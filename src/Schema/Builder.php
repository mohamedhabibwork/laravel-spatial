<?php

declare(strict_types=1);

namespace Habib\LaravelSpatial\Schema;

use Closure;
use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Blueprint as BaseBlueprint;
use Illuminate\Database\Schema\Builder as BaseBuilder;
use Illuminate\Database\Schema\MySqlBuilder;
use Illuminate\Database\Schema\PostgresBuilder;

final class Builder extends BaseBuilder
{
    /**
     * Create a new command set with a Closure.
     */
    protected function createBlueprint($table, ?Closure $callback = null): BaseBlueprint
    {
        return new Blueprint(
            $this->connection,
            $table,
            $callback,
        );
    }

    /**
     * Get the database-specific builder instance for delegation.
     * This allows us to inherit database-specific behavior while using our custom blueprint.
     */
    protected function getDelegateBuilder(): MySqlBuilder|PostgresBuilder
    {
        return match ($this->connection->getDriverName()) {
            'mysql' => new MySqlBuilder($this->connection),
            'pgsql' => new PostgresBuilder($this->connection),
            default => new MySqlBuilder($this->connection),
        };
    }
}
