<?php

declare(strict_types=1);

namespace Habib\LaravelSpatial\Types;

use GeoJson\GeoJson;
use GeoJson\Geometry\Geometry as GeoJsonGeometry;
use GeoJson\Geometry\MultiPoint as GeoJsonMultiPoint;
use Habib\LaravelSpatial\Exceptions\InvalidGeoJsonException;

class MultiPoint extends PointCollection
{
    /**
     * The minimum number of items required to create this collection.
     */
    protected int $minimumCollectionItems = 1;

    public function toWKT(): string
    {
        return sprintf('MULTIPOINT(%s)', (string) $this);
    }

    public static function fromWkt(string $wkt, ?int $srid = null): static
    {
        $wktArgument = Geometry::getWKTArgument($wkt);

        return static::fromString($wktArgument, $srid);
    }

    public static function fromString(string $wktArgument, int $srid): static
    {
        $matches = [];
        preg_match_all('/\(\s*(\d+\s+\d+)\s*\)/', trim($wktArgument), $matches);

        $points = array_map(function (string $pair): Point {
            return Point::fromPair($pair);
        }, $matches[1]);

        return new static($points, $srid);
    }

    public function __toString(): string
    {
        return implode(',', array_map(function (Point $point): string {
            return sprintf('(%s)', $point->toPair());
        }, $this->items));
    }

    public static function fromJson(string|GeoJsonGeometry $geoJson): static
    {
        if (is_string($geoJson)) {
            $geoJson = GeoJson::jsonUnserialize(json_decode($geoJson, true));
        }

        if (! $geoJson instanceof GeoJsonMultiPoint) {
            throw new InvalidGeoJsonException('Expected '.GeoJsonMultiPoint::class.', got '.get_class($geoJson));
        }

        $set = [];
        foreach ($geoJson->getCoordinates() as $coordinate) {
            $set[] = new Point($coordinate[1], $coordinate[0]);
        }

        return new self($set);
    }

    /**
     * Convert to GeoJson MultiPoint that is jsonable to GeoJSON.
     */
    public function jsonSerialize(): GeoJsonMultiPoint
    {
        $points = [];
        foreach ($this->items as $point) {
            $points[] = $point->jsonSerialize();
        }

        return new GeoJsonMultiPoint($points);
    }
}
