<?php

use Habib\LaravelSpatial\MysqlConnection;
use Habib\LaravelSpatial\Schema\Builder;
use PHPUnit\Framework\TestCase;
use Stubs\PDOStub;

class MysqlConnectionTest extends TestCase
{
    private $mysqlConnection;

    protected function setUp()
    {
        $mysqlConfig = ['driver' => 'mysql', 'prefix' => 'prefix', 'database' => 'database', 'name' => 'foo'];
        $this->mysqlConnection = new MysqlConnection(new PDOStub, 'database', 'prefix', $mysqlConfig);
    }

    public function test_get_schema_builder()
    {
        $builder = $this->mysqlConnection->getSchemaBuilder();

        $this->assertInstanceOf(Builder::class, $builder);
    }
}
