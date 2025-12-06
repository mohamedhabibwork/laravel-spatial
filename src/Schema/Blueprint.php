<?php

declare(strict_types=1);

namespace Habib\LaravelSpatial\Schema;

use Illuminate\Database\Schema\Blueprint as IlluminateBlueprint;
use Illuminate\Support\Fluent;

final class Blueprint extends IlluminateBlueprint
{
    /**
     * Add a geometry column on the table.
     */
    public function geometry(string $column, ?string $subtype = null, ?int $srid = null): Fluent
    {
        $srid ??= (int) (getenv('SPATIAL_REF_SYS') ?: 0);

        return $this->addColumn('geometry', $column, compact('srid'));
    }

    /**
     * Add a point column on the table.
     */
    public function point(string $column, ?int $srid = null): Fluent
    {
        $srid ??= (int) (getenv('SPATIAL_REF_SYS') ?: 0);

        return $this->addColumn('point', $column, compact('srid'));
    }

    /**
     * Add a linestring column on the table.
     */
    public function lineString(string $column, ?int $srid = null): Fluent
    {
        $srid ??= (int) (getenv('SPATIAL_REF_SYS') ?: 0);

        return $this->addColumn('linestring', $column, compact('srid'));
    }

    /**
     * Add a polygon column on the table.
     */
    public function polygon(string $column, ?int $srid = null): Fluent
    {
        $srid ??= (int) (getenv('SPATIAL_REF_SYS') ?: 0);

        return $this->addColumn('polygon', $column, compact('srid'));
    }

    /**
     * Add a multipoint column on the table.
     */
    public function multiPoint(string $column, ?int $srid = null): Fluent
    {
        $srid ??= (int) (getenv('SPATIAL_REF_SYS') ?: 0);

        return $this->addColumn('multipoint', $column, compact('srid'));
    }

    /**
     * Add a multilinestring column on the table.
     */
    public function multiLineString(string $column, ?int $srid = null): Fluent
    {
        $srid ??= (int) (getenv('SPATIAL_REF_SYS') ?: 0);

        return $this->addColumn('multilinestring', $column, compact('srid'));
    }

    /**
     * Add a multipolygon column on the table.
     */
    public function multiPolygon(string $column, ?int $srid = null): Fluent
    {
        $srid ??= (int) (getenv('SPATIAL_REF_SYS') ?: 0);

        return $this->addColumn('multipolygon', $column, compact('srid'));
    }

    /**
     * Add a geometrycollection column on the table.
     */
    public function geometryCollection(string $column, ?int $srid = null): Fluent
    {
        $srid ??= (int) (getenv('SPATIAL_REF_SYS') ?: 0);

        return $this->addColumn('geometrycollection', $column, compact('srid'));
    }

    /**
     * Specify a spatial index for the table.
     */
    public function spatialIndex(string|array $columns, ?string $name = null): Fluent
    {
        return $this->indexCommand('spatial', $columns, $name);
    }

    /**
     * Indicate that the given index should be dropped.
     */
    public function dropSpatialIndex(string|array $index): Fluent
    {
        return $this->dropIndexCommand('dropIndex', 'spatial', $index);
    }
}
