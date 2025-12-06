<?php

declare(strict_types=1);

namespace Habib\LaravelSpatial\Types;

use GeoJson\GeoJson;
use GeoJson\Geometry\Geometry as GeoJsonGeometry;
use GeoJson\Geometry\LineString as GeoJsonLineString;
use Habib\LaravelSpatial\Exceptions\InvalidGeoJsonException;

class LineString extends PointCollection
{
    /**
     * The minimum number of items required to create this collection.
     */
    protected int $minimumCollectionItems = 2;

    public static function fromWkt(string $wkt, ?int $srid = null): static
    {
        $srid ??= (int) (getenv('SPATIAL_REF_SYS') ?: 0);
        $wktArgument = Geometry::getWKTArgument($wkt);

        return static::fromString($wktArgument, $srid);
    }

    public static function fromString(string $wktArgument, int $srid): static
    {
        $pairs = explode(',', trim($wktArgument));
        $points = array_map(function (string $pair): Point {
            return Point::fromPair($pair);
        }, $pairs);

        return new static($points, $srid);
    }

    public static function fromJson(string|GeoJsonGeometry $geoJson): static
    {
        if (is_string($geoJson)) {
            $geoJson = GeoJson::jsonUnserialize(json_decode($geoJson, true));
        }

        if (! $geoJson instanceof GeoJsonLineString) {
            throw new InvalidGeoJsonException('Expected '.GeoJsonLineString::class.', got '.get_class($geoJson));
        }

        $set = [];
        foreach ($geoJson->getCoordinates() as $coordinate) {
            $set[] = new Point($coordinate[1], $coordinate[0]);
        }

        return new self($set);
    }

    public function toWKT(): string
    {
        return sprintf('LINESTRING(%s)', $this->toPairList());
    }

    public function __toString(): string
    {
        return $this->toPairList();
    }

    /**
     * Convert to GeoJson LineString that is jsonable to GeoJSON.
     */
    public function jsonSerialize(): GeoJsonLineString
    {
        $points = [];
        foreach ($this->items as $point) {
            $points[] = $point->jsonSerialize();
        }

        return new GeoJsonLineString($points);
    }
}
