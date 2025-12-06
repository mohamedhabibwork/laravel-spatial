<?php

declare(strict_types=1);

namespace Habib\LaravelSpatial\Connectors;

use Habib\LaravelSpatial\MysqlConnection;
use Habib\LaravelSpatial\PostgresConnection;
use Illuminate\Database\Connectors\ConnectionFactory as IlluminateConnectionFactory;

final class ConnectionFactory extends IlluminateConnectionFactory
{
    /**
     * Create a new connection instance.
     */
    protected function createConnection($driver, $connection, $database, $prefix = '', array $config = [])
    {
        if ($this->container->bound($key = "db.connection.{$driver}")) {
            return $this->container->make($key, [$connection, $database, $prefix, $config]);    // @codeCoverageIgnore
        }

        return match ($driver) {
            'mysql' => new MysqlConnection($connection, $database, $prefix, $config),
            'pgsql' => new PostgresConnection($connection, $database, $prefix, $config),
            default => parent::createConnection($driver, $connection, $database, $prefix, $config),
        };
    }
}
