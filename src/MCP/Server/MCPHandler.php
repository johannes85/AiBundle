<?php

namespace AiBundle\MCP\Server;

use AiBundle\MCP\Dto\Capabilities;
use AiBundle\MCP\Dto\Content;
use AiBundle\MCP\Dto\InitializeResponse;
use AiBundle\MCP\Dto\JsonRpcRequest;
use AiBundle\MCP\Dto\JsonRpcResponse;
use AiBundle\MCP\Dto\ServerInfo;
use AiBundle\MCP\Dto\TextContent;
use AiBundle\MCP\Dto\ToolDefinition;
use AiBundle\MCP\Dto\ToolResponse;
use AiBundle\MCP\Dto\ToolsCapabilities;
use AiBundle\MCP\Dto\ToolsList;
use ReflectionException;
use ReflectionMethod;
use ReflectionNamedType;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Exception\MissingConstructorArgumentsException;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Exception\ExceptionInterface as SerializerExceptionInterface;

class MCPHandler {

  private const JSON_RPC_VERSION = '2.0';
  private const JSON_RPC_RESPONSE_CONTENT_TYPE = 'application/json';

  private const MCP_METHOD_TOOLS_LIST = 'tools/list';
  private const MCP_METHOD_TOOLS_CALL = 'tools/call';
  private const MCP_METHOD_PROMPTS_LIST = 'prompts/list';
  private const MCP_METHOD_RESOURCES_LIST = 'resources/list';
  private const MCP_METHOD_INITIALIZE = 'initialize';
  private const MCP_METHOD_INITIALIZED = 'notifications/initialized';

  private const DEFAULT_PROTOCOL_VERSION = '2025-06-18';
  private const SUPPORTED_PROTOCOL_VERSIONS = [
    '2025-06-18',
    '2025-03-26'
  ];

  /**
   * @param Serializer $serializer
   * @param ToolRegistry $toolRegistry
   * @param ContainerInterface $container
   * @param array<string, mixed> $serverConfig
   */
  public function __construct(
    #[Autowire('@ai_bundle.serializer')] private Serializer $serializer,
    private ToolRegistry $toolRegistry,
    #[Autowire('@service_container')] private ContainerInterface $container,
    #[Autowire('%ai_bundle.mcp_server%')] private array $serverConfig
  ) {}

  /**
   * Handles a JSON-RPC request
   *
   * @param Request $request
   * @return Response
   * @throws MCPHandlerException
   */
  public function handleRequest(Request $request): Response {
    $id = null;
    try {
      try {
        $jsonRpcRequest = $this->serializer->deserialize(
          $request->getContent(),
          JsonRpcRequest::class,
          'json'
        );
        $id = $jsonRpcRequest->id;
      } catch (MissingConstructorArgumentsException) {
        throw new JsonRpcException(JsonRpcException::CODE_INVALID_REQUEST);
      } catch (SerializerExceptionInterface) {
        throw new JsonRpcException(JsonRpcException::CODE_PARSE_ERROR);
      }

      try {
        $res = $this->handleJsonRpcRequest($jsonRpcRequest);
        if ($res === null) {
          return new Response(status:202, headers: ['Content-Type' => self::JSON_RPC_RESPONSE_CONTENT_TYPE]);
        }
        return new Response(
          $this->serializer->serialize(
            $this->handleJsonRpcRequest($jsonRpcRequest),
            'json',
            [
              AbstractObjectNormalizer::PRESERVE_EMPTY_OBJECTS => true
            ]
          ),
          200,
          ['Content-Type' => self::JSON_RPC_RESPONSE_CONTENT_TYPE]
        );
      } catch (SerializerExceptionInterface $ex) {
        throw new MCPHandlerException('Error serializing JSON-RPC response', previous: $ex);
      }
    } catch (JsonRpcException $ex) {
      try {
        return new Response(
          $this->serializer->serialize($ex->toJsonRpcResponse($id), 'json'),
          200,
          ['Content-Type' => self::JSON_RPC_RESPONSE_CONTENT_TYPE]
        );
      } catch (SerializerExceptionInterface $ex) {
        throw new MCPHandlerException('Error serializing JSON-RPC error response', previous: $ex);
      }
    }
  }

  /**
   * Handles JSON-RPC request object
   *
   * @param JsonRpcRequest $jsonRpcRequest
   * @return ?JsonRpcResponse
   * @throws JsonRpcException
   * @throws MCPHandlerException
   */
  private function handleJsonRpcRequest(JsonRpcRequest $jsonRpcRequest): ?JsonRpcResponse {
    $res = match($jsonRpcRequest->method) {
      self::MCP_METHOD_TOOLS_LIST => $this->handleToolsList(),
      self::MCP_METHOD_TOOLS_CALL => $this->handleToolCall($jsonRpcRequest->params),
      self::MCP_METHOD_INITIALIZE => $this->handleInitialize($jsonRpcRequest->params),
      self::MCP_METHOD_INITIALIZED  => null,
      self::MCP_METHOD_PROMPTS_LIST => ["prompts" => []], // Dummy entry for dummy clients ignoring capabilities
      self::MCP_METHOD_RESOURCES_LIST => ["resources" => []], // Dummy entry for dummy clients ignoring capabilities
      default => throw new JsonRpcException(JsonRpcException::CODE_METHOD_NOT_FOUND)
    };

    return $res !== null ? new JsonRpcResponse(
      self::JSON_RPC_VERSION,
      $res,
      null,
      $jsonRpcRequest->id
    ) : null;
  }

  /**
   * Handles the initialize method
   *
   *
   * @param array<mixed> $params
   * @return InitializeResponse
   * @throws JsonRpcException
   */
  private function handleInitialize(array $params): InitializeResponse {
    if (!isset($params['protocolVersion'])) {
      throw new JsonRpcException(data: 'Missing protocol version in initialize request');
    }
    return new InitializeResponse(
      in_array($params['protocolVersion'], self::SUPPORTED_PROTOCOL_VERSIONS)
        ? $params['protocolVersion']
        : self::DEFAULT_PROTOCOL_VERSION,
      new Capabilities(
        new ToolsCapabilities(false)
      ),
      new ServerInfo(
        $this->serverConfig['name'],
        $this->serverConfig['title'],
        $this->serverConfig['version']
      ),
      $this->serverConfig['instructions']
    );
  }

  /**
   * Handles the tools/list method
   *
   * @return ToolsList
   */
  private function handleToolsList(): ToolsList {
    return new ToolsList(
      array_map(fn (RegisteredTool $tool) => new ToolDefinition(
        $tool->name,
        $tool->description,
        $tool->schema
      ), array_values($this->toolRegistry->getTools()))
    );
  }

  /**
   * Handles the tools/call method
   *
   *
   * @param array<string, mixed> $params
   * @return mixed
   * @throws JsonRpcException
   * @throws MCPHandlerException
   */
  private function handleToolCall(array $params): mixed {
    if (!isset($params['name'])) {
      throw new JsonRpcException(data: 'Invalid parameters for tool call');
    }
    $arguments = $params['arguments'] ?? [];

    $tool = $this->toolRegistry->getTool($params['name']);
    if (!$tool) {
      throw new JsonRpcException(data: 'Tool not found');
    }

    try {
      $toolService = $this->container->get($tool->serviceId);
    } catch (ServiceNotFoundException $ex) {
      throw new JsonRpcException(data: 'Tool service not found');
    } catch (ServiceCircularReferenceException $ex) {
      throw new MCPHandlerException('Error while loading tool service', previous: $ex);
    }

    try {
      $toolMethod = new ReflectionMethod($toolService, $tool->methodName);
    } catch (ReflectionException) {
      throw new JsonRpcException(data: 'Tool method not found in service');
    }

    $toolMethodParams = $toolMethod->getParameters();
    $paramCallValues = [];
    $errors = [];
    foreach ($toolMethodParams as $param) {
      $paramType = $param->getType();
      if ($paramType instanceof ReflectionNamedType) {
        if (isset($arguments[$param->getName()])) {
          if ($paramType->isBuiltin()) {
            $paramCallValues[$param->getName()] = $arguments[$param->getName()];
          } else {
            try {
              $paramCallValues[$param->getName()] = $this->serializer->denormalize(
                $arguments[$param->getName()],
                $paramType->getName()
              );
            } catch (SerializerExceptionInterface $ex) {
              $errors[] = 'Error denormalizing argument: '.$param->getName();
            }
          }
        } elseif (!$param->isDefaultValueAvailable()) {
          $errors[] = 'Missing argument: '.$param->getName();
        }
      }
    }
    if (count($errors) > 0) {
      throw new JsonRpcException(data: $errors);
    }
    try {
      $ret = call_user_func_array([$toolService, $tool->methodName], $paramCallValues);
    } catch (MCPToolError $ex) {
      return new ToolResponse(
        [
          [
            'type' => 'text',
            'text' => $ex->content
          ]
        ],
        true
      );
    }
    return new ToolResponse(
      $this->createContentArrayForToolResponse($ret),
      false
    );
  }

  /**
   * Creates array of content objects from the tool response.
   *
   * @param mixed $result
   * @return array<Content>
   * @throws MCPHandlerException
   */
  private function createContentArrayForToolResponse(
    mixed $result,
  ): array {
    try {
      if (is_array($result)) {
        if (count($result) === 0) {
          return [new TextContent('[]')];
        }
        $contentArray = true;
        foreach ($result as $item) {
          if (!$item instanceof Content) {
            $contentArray = false;
            break;
          }
        }
        if ($contentArray) {
          return $result;
        }
        return [new TextContent($this->serializer->serialize($result, 'json'))];
      } else if (is_object($result)) {
        return [new TextContent($this->serializer->serialize($result, 'json'))];
      } else if ($result === null) {
        return [new TextContent('null')];
      } else if (is_bool($result)) {
        return [new TextContent($result ? 'true' : 'false')];
      } else if ($result instanceof Content) {
        return [$result];
      }
      return [new TextContent((string) $result)];
    } catch (SerializerExceptionInterface $ex) {
      throw new MCPHandlerException('Error serializing tool response', previous: $ex);
    }
  }

}
