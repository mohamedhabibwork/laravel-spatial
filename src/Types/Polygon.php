<?php

declare(strict_types=1);

namespace Habib\LaravelSpatial\Types;

use GeoJson\GeoJson;
use GeoJson\Geometry\Geometry as GeoJsonGeometry;
use GeoJson\Geometry\LinearRing;
use GeoJson\Geometry\Polygon as GeoJsonPolygon;
use Habib\LaravelSpatial\Exceptions\InvalidGeoJsonException;

class Polygon extends MultiLineString
{
    public function toWKT(): string
    {
        return sprintf('POLYGON(%s)', (string) $this);
    }

    public static function fromJson(string|GeoJsonGeometry $geoJson): static
    {
        if (is_string($geoJson)) {
            $geoJson = GeoJson::jsonUnserialize(json_decode($geoJson, true));
        }

        if (! $geoJson instanceof GeoJsonPolygon) {
            throw new InvalidGeoJsonException('Expected '.GeoJsonPolygon::class.', got '.get_class($geoJson));
        }

        $set = [];
        foreach ($geoJson->getCoordinates() as $coordinates) {
            $points = [];
            foreach ($coordinates as $coordinate) {
                $points[] = new Point($coordinate[1], $coordinate[0]);
            }
            $set[] = new LineString($points);
        }

        return new self($set);
    }

    /**
     * Convert to GeoJson Polygon that is jsonable to GeoJSON.
     */
    public function jsonSerialize(): GeoJsonPolygon
    {
        $linearRings = [];
        foreach ($this->items as $lineString) {
            $linearRings[] = new LinearRing($lineString->jsonSerialize()->getCoordinates());
        }

        return new GeoJsonPolygon($linearRings);
    }
}
