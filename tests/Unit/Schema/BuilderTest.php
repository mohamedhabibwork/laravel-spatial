<?php

namespace Schema;

use BaseTestCase;
use Habib\LaravelSpatial\MysqlConnection;
use Habib\LaravelSpatial\Schema\Blueprint;
use Habib\LaravelSpatial\Schema\Builder;
use Mockery;

class BuilderTest extends BaseTestCase
{
    public function test_returns_correct_blueprint()
    {
        $connection = Mockery::mock(MysqlConnection::class);
        $connection->shouldReceive('getSchemaGrammar')->once()->andReturn(null);

        $mock = Mockery::mock(Builder::class, [$connection]);
        $mock->makePartial()->shouldAllowMockingProtectedMethods();
        $blueprint = $mock->createBlueprint('test', function () {});

        $this->assertInstanceOf(Blueprint::class, $blueprint);
    }
}
