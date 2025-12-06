<?php

declare(strict_types=1);

namespace Habib\LaravelSpatial\Types;

use GeoJson\Geometry\Geometry as GeoJsonGeometry;

interface GeometryInterface
{
    public function toWKT(): string;

    public static function fromWKT(string $wkt, ?int $srid = null): static;

    public function __toString(): string;

    public static function fromString(string $wktArgument, int $srid): static;

    public static function fromJson(string|GeoJsonGeometry $geoJson): static;
}
