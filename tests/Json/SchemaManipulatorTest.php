<?php

namespace AiBundle\Tests\Json;

use AiBundle\Json\SchemaManipulator;
use PHPUnit\Framework\TestCase;

class SchemaManipulatorTest extends TestCase {

  public function testRemoveProperty() {
    $schema = array(
      'name' => 'getResourceReference',
      'description' => 'Returns a resource reference that can be used by MCP clients',
      'parameters' =>
        array (
          'testnoMatchSubstring' => 'keep',
          'noMatchSubstring' => 'remove',
          'type' => 'object',
          'properties' =>
            array (
              'resourceId' =>
                array (
                  'type' => 'number',
                  'minimum' => 1,
                  'maximum' => 100,
                  'description' => 'ID of the resource to reference (1-100)',
                ),
            ),
          'required' =>
            array (
              0 => 'resourceId',
            ),
          'additionalProperties' => false,
          '$schema' => 'http://json-schema.org/draft-07/schema#',
        ),
    );

    $result = SchemaManipulator::removeProperties($schema, [
      'noMatchSubstring',
      '$schema',
      'additionalProperties',
      'resourceId/description'
    ]);

    $this->assertEquals(array(
      'name' => 'getResourceReference',
      'description' => 'Returns a resource reference that can be used by MCP clients',
      'parameters' =>
        array (
          'testnoMatchSubstring' => 'keep',
          'type' => 'object',
          'properties' =>
            array (
              'resourceId' =>
                array (
                  'type' => 'number',
                  'minimum' => 1,
                  'maximum' => 100
                ),
            ),
          'required' =>
            array (
              0 => 'resourceId',
            ),
        ),
    ), $result);
  }

}
