<?php

use Habib\LaravelSpatial\Connectors\ConnectionFactory;
use Habib\LaravelSpatial\MysqlConnection;
use Illuminate\Container\Container;
use Stubs\PDOStub;

class ConnectionFactoryBaseTest extends BaseTestCase
{
    public function test_make_calls_create_connection()
    {
        $pdo = new PDOStub;

        $factory = Mockery::mock(ConnectionFactory::class, [new Container])->makePartial();
        $factory->shouldAllowMockingProtectedMethods();
        $conn = $factory->createConnection('mysql', $pdo, 'database');

        $this->assertInstanceOf(MysqlConnection::class, $conn);
    }

    public function test_create_connection_different_driver()
    {
        $pdo = new PDOStub;

        $factory = Mockery::mock(ConnectionFactory::class, [new Container])->makePartial();
        $factory->shouldAllowMockingProtectedMethods();
        $conn = $factory->createConnection('pgsql', $pdo, 'database');

        $this->assertInstanceOf(\Illuminate\Database\PostgresConnection::class, $conn);
    }
}
