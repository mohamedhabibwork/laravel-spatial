<?php

declare(strict_types=1);

namespace Habib\LaravelSpatial\Schema\Grammars;

use Habib\LaravelSpatial\Schema\Blueprint;
use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Grammars\PostgresGrammar as IlluminatePostgresGrammar;
use Illuminate\Support\Fluent;

final class PostgresGrammar extends IlluminatePostgresGrammar
{
    public const COLUMN_MODIFIER_SRID = 'Srid';

    /**
     * Create a new grammar instance.
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        // Enable SRID as a column modifier
        if (! in_array(self::COLUMN_MODIFIER_SRID, $this->modifiers, true)) {
            $this->modifiers[] = self::COLUMN_MODIFIER_SRID;
        }
    }

    /**
     * Adds a statement to add a geometry column.
     * PostgreSQL format: geometry or geometry(GeometryType, SRID)
     */
    public function typeGeometry(Fluent $column): string
    {
        if (! is_null($column->srid) && is_int($column->srid) && $column->srid > 0) {
            return "geometry(Geometry, {$column->srid})";
        }

        return 'geometry';
    }

    /**
     * Adds a statement to add a point column.
     * PostgreSQL format: geometry(Point, SRID)
     */
    public function typePoint(Fluent $column): string
    {
        if (! is_null($column->srid) && is_int($column->srid) && $column->srid > 0) {
            return "geometry(Point, {$column->srid})";
        }

        return 'geometry(Point)';
    }

    /**
     * Adds a statement to add a linestring column.
     * PostgreSQL format: geometry(LineString, SRID)
     */
    public function typeLinestring(Fluent $column): string
    {
        if (! is_null($column->srid) && is_int($column->srid) && $column->srid > 0) {
            return "geometry(LineString, {$column->srid})";
        }

        return 'geometry(LineString)';
    }

    /**
     * Adds a statement to add a polygon column.
     * PostgreSQL format: geometry(Polygon, SRID)
     */
    public function typePolygon(Fluent $column): string
    {
        if (! is_null($column->srid) && is_int($column->srid) && $column->srid > 0) {
            return "geometry(Polygon, {$column->srid})";
        }

        return 'geometry(Polygon)';
    }

    /**
     * Adds a statement to add a multipoint column.
     * PostgreSQL format: geometry(MultiPoint, SRID)
     */
    public function typeMultipoint(Fluent $column): string
    {
        if (! is_null($column->srid) && is_int($column->srid) && $column->srid > 0) {
            return "geometry(MultiPoint, {$column->srid})";
        }

        return 'geometry(MultiPoint)';
    }

    /**
     * Adds a statement to add a multilinestring column.
     * PostgreSQL format: geometry(MultiLineString, SRID)
     */
    public function typeMultilinestring(Fluent $column): string
    {
        if (! is_null($column->srid) && is_int($column->srid) && $column->srid > 0) {
            return "geometry(MultiLineString, {$column->srid})";
        }

        return 'geometry(MultiLineString)';
    }

    /**
     * Adds a statement to add a multipolygon column.
     * PostgreSQL format: geometry(MultiPolygon, SRID)
     */
    public function typeMultipolygon(Fluent $column): string
    {
        if (! is_null($column->srid) && is_int($column->srid) && $column->srid > 0) {
            return "geometry(MultiPolygon, {$column->srid})";
        }

        return 'geometry(MultiPolygon)';
    }

    /**
     * Adds a statement to add a geometrycollection column.
     * PostgreSQL format: geometry(GeometryCollection, SRID)
     */
    public function typeGeometrycollection(Fluent $column): string
    {
        if (! is_null($column->srid) && is_int($column->srid) && $column->srid > 0) {
            return "geometry(GeometryCollection, {$column->srid})";
        }

        return 'geometry(GeometryCollection)';
    }

    /**
     * Compile a spatial index key command.
     * PostgreSQL uses GIST index instead of SPATIAL
     */
    public function compileSpatial(Blueprint $blueprint, Fluent $command): string
    {
        $columns = $this->columnize($command->columns);

        return sprintf(
            'create index %s on %s using gist (%s)',
            $this->wrap($command->index),
            $this->wrapTable($blueprint),
            $columns
        );
    }

    /**
     * Get the SQL for a SRID column modifier.
     * Note: For PostgreSQL, SRID is included in the type definition, not as a modifier
     */
    protected function modifySrid(\Illuminate\Database\Schema\Blueprint $blueprint, Fluent $column): ?string
    {
        // PostgreSQL includes SRID in type definition, so we return null here
        return null;
    }
}

