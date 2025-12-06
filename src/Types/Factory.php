<?php

declare(strict_types=1);

namespace Habib\LaravelSpatial\Types;

final class Factory implements \GeoIO\Factory
{
    public function createPoint($dimension, array $coordinates, int $srid = null): Point
    {
        $srid ??= (int) (getenv('SPATIAL_REF_SYS') ?: 0);

        return new Point($coordinates['y'], $coordinates['x'], $srid);
    }

    public function createLineString($dimension, array $points, int $srid = null): LineString
    {
        $srid ??= (int) (getenv('SPATIAL_REF_SYS') ?: 0);

        return new LineString($points, $srid);
    }

    public function createLinearRing($dimension, array $points, int $srid = null): LineString
    {
        $srid ??= (int) (getenv('SPATIAL_REF_SYS') ?: 0);

        return new LineString($points, $srid);
    }

    public function createPolygon($dimension, array $lineStrings, int $srid = null): Polygon
    {
        $srid ??= (int) (getenv('SPATIAL_REF_SYS') ?: 0);

        return new Polygon($lineStrings, $srid);
    }

    public function createMultiPoint($dimension, array $points, int $srid = null): MultiPoint
    {
        $srid ??= (int) (getenv('SPATIAL_REF_SYS') ?: 0);

        return new MultiPoint($points, $srid);
    }

    public function createMultiLineString($dimension, array $lineStrings, int $srid = null): MultiLineString
    {
        $srid ??= (int) (getenv('SPATIAL_REF_SYS') ?: 0);

        return new MultiLineString($lineStrings, $srid);
    }

    public function createMultiPolygon($dimension, array $polygons, int $srid = null): MultiPolygon
    {
        $srid ??= (int) (getenv('SPATIAL_REF_SYS') ?: 0);

        return new MultiPolygon($polygons, $srid);
    }

    public function createGeometryCollection($dimension, array $geometries, int $srid = null): GeometryCollection
    {
        $srid ??= (int) (getenv('SPATIAL_REF_SYS') ?: 0);

        return new GeometryCollection($geometries, $srid);
    }
}
