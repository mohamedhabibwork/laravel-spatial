<?php

declare(strict_types=1);

namespace Habib\LaravelSpatial\Support;

use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

final class DatabaseHelper
{
    /**
     * Get the ST_GeomFromText function call appropriate for the database.
     */
    public static function getGeomFromTextSql(Connection $connection): string
    {
        return match ($connection->getDriverName()) {
            'mysql' => "ST_GeomFromText(?, ?, 'axis-order=long-lat')",
            'pgsql' => 'ST_GeomFromText(?, ?)',
            default => 'ST_GeomFromText(?, ?)',
        };
    }

    /**
     * Get the appropriate column wrapper for the database.
     */
    public static function wrapColumn(Connection $connection, string $column): string
    {
        return match ($connection->getDriverName()) {
            'mysql' => "`{$column}`",
            'pgsql' => "\"{$column}\"",
            default => $column,
        };
    }

    /**
     * Check if the connection is MySQL.
     */
    public static function isMySql(Connection $connection): bool
    {
        return $connection->getDriverName() === 'mysql';
    }

    /**
     * Check if the connection is PostgreSQL.
     */
    public static function isPostgres(Connection $connection): bool
    {
        return $connection->getDriverName() === 'pgsql';
    }

    /**
     * Get spatial function name with database-specific handling if needed.
     * Most spatial functions work the same across MySQL and PostgreSQL.
     */
    public static function getSpatialFunction(Connection $connection, string $function): string
    {
        // Both MySQL and PostgreSQL use lowercase function names
        // and SQL is case-insensitive for function names
        return strtolower($function);
    }
}

