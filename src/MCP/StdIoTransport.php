<?php

namespace AiBundle\MCP;

use AiBundle\MCP\Dto\JsonRpcRequest;
use AiBundle\MCP\Dto\JsonRpcResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Process\Exception\ExceptionInterface as ProcessExceptionInterface;
use Symfony\Component\Process\Exception\ProcessStartFailedException;
use Symfony\Component\Process\Exception\RuntimeException;
use Symfony\Component\Process\InputStream;
use Symfony\Component\Process\Process;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Serializer\Exception\ExceptionInterface as SerializerExceptionInterface;

class StdIoTransport implements TransportInterface {

  private const PROTOCOL_VERSION = '2024-11-05';
  private const SUPPORTED_PROTOCOL_VERSIONS = [
    '2024-10-07',
    '2024-11-05'
  ];

  private const DEFAULT_STOP_SIGNAL = SIGINT;

  /** @var ?Process */
  private ?Process $process = null;

  private ?InputStream $input = null;

  /**
   * @param array<string> $command
   * @param Serializer $serializer
   * @param LoggerInterface $log
   * @param int $responseTimeout
   * @param int $initTimeout
   * @param int $initTrys
   * @param int $stopSignal
   */
  public function __construct(
    private readonly array $command,
    #[Autowire('@ai_bundle.serializer')] private readonly Serializer $serializer,
    private LoggerInterface $log,
    private readonly int $responseTimeout = 20,
    private readonly int $initTimeout = 10,
    private readonly int $initTrys = 3,
    private readonly int $stopSignal = self::DEFAULT_STOP_SIGNAL
  ) {}

  public function __destruct() {
    $this->disconnect();
  }

  /**
   * Returns whether the process is currently running
   *
   * @return bool
   */
  private function isConnected(): bool {
    return $this->process && $this->process->isRunning();
  }

  /**
   * Opens the process and sends an initialize request.
   *
   * @return void
   * @throws MCPException
   * @throws MCPTransportException
   * @throws ProcessExceptionInterface
   * @throws ProcessStartFailedException
   * @throws RuntimeException
   */
  public function connect(): void {
    if (!$this->isConnected()) {
      $this->process = new Process($this->command);
      $this->process->setInput($this->input = new InputStream());
      $this->process->start();

      $res = null;
      $initCount = 0;
      do {
        $initCount++;
        try {
          $this->log->debug('Sending initialize request');
          $res = $this->executeRequest(new JsonRpcRequest(
            'initialize',
            [
              'protocolVersion' => self::PROTOCOL_VERSION,
              'capabilities' => [],
              'clientInfo' => [
                'name' => 'AiBundle',
                'version' => '1.0.0'
              ]
            ]
          ), $this->initTimeout);
          $this->log->debug('Got response', [
            'response' => $res
          ]);
        } catch (MCPTransportTimeoutException) { }
      } while ($res === null && $initCount < $this->initTrys);
      if ($res === null) {
        throw new MCPTransportException('Timeout while waiting for initialization response');
      }
      $serverProtocolVersion = $res->result['protocolVersion'] ?? 'undefined';
      if (!in_array($serverProtocolVersion, self::SUPPORTED_PROTOCOL_VERSIONS)) {
        throw new MCPTransportException(sprintf(
          'Unsupported protocol version: %s. Supported versions: %s',
          $serverProtocolVersion,
          implode(', ', self::SUPPORTED_PROTOCOL_VERSIONS)
        ));
      }
    }
  }

  /**
   * Execute a JSON-RPC request and return the response.
   *
   * @param JsonRpcRequest $request
   * @param int|null $timeout
   * @return JsonRpcResponse
   * @throws MCPException
   * @throws MCPTransportException
   * @throws MCPTransportTimeoutException
   * @throws ProcessExceptionInterface
   */
  public function executeRequest(JsonRpcRequest $request, ?int $timeout = null): JsonRpcResponse {
    if (!$this->isConnected()) {
      $this->connect();
    }
    if (!$this->process || !$this->process->isRunning()) {
      throw new MCPTransportException('Process is not running.');
    }
    if ($timeout === null) {
      $timeout = $this->responseTimeout;
    }
    $request = clone $request;
    if ($request->id === null) {
      $request = new JsonRpcRequest(
        $request->method,
        $request->params,
        Uuid::v4()->toRfc4122()
      );
    }
    try {
      $message = $this->serializer->serialize($request, 'json', [
        Serializer::EMPTY_ARRAY_AS_OBJECT => true
      ])."\n";
    } catch (SerializerExceptionInterface $ex) {
      throw new MCPException('Failed to serialize json rpc request', previous: $ex);
    }
    $this->input->write($message);

    $buffer = "";
    $time = time();
    do {
      if ($time + $timeout < time()) {
        throw new MCPTransportTimeoutException('Timeout while waiting for response');
      }
      if (($b = $this->process->getOutput()) !== '') {
        $this->process->clearOutput();
        $buffer .= $b;
        while (($nlPos = strpos($buffer, "\n")) !== false) {
          $line = substr($buffer, 0, $nlPos);
          $buffer = substr($buffer, $nlPos + 1);
          if (str_starts_with($line, '{') && str_ends_with($line, '}')) {
            try {
              /** @var JsonRpcResponse $rpcMessage */
              $rpcMessage = $this->serializer->deserialize($line, JsonRpcResponse::class, 'json');
            } catch (SerializerExceptionInterface $ex) {
              throw new MCPException('Failed to deserialize json rpc response', previous: $ex);
            }
            if ($rpcMessage->id === $request->id) {
              if ($rpcMessage->error !== null) {
                throw new MCPTransportException(sprintf(
                  'Error in response: [%d] %s',
                  $rpcMessage->error->code,
                  $rpcMessage->error->message
                ));
              }
              return $rpcMessage;
            }
          } else {
            $this->log->debug(sprintf('Ignoring line: %s', $line));
          }
        }
      }
    } while(true);
  }

  /**
   * Ends process and closes input stream.
   *
   * @return void
   */
  private function disconnect(): void {
    if ($this->input !== null) {
      $this->input->close();
      $this->input = null;
    }
    if ($this->process !== null) {
      $this->process->stop(signal: $this->stopSignal);
      $this->process = null;
    }
  }

}
