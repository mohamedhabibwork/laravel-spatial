<?php

declare(strict_types=1);

namespace Habib\LaravelSpatial\Types;

use GeoJson\GeoJson;
use GeoJson\Geometry\Geometry as GeoJsonGeometry;
use GeoJson\Geometry\Point as GeoJsonPoint;
use Habib\LaravelSpatial\Exceptions\InvalidGeoJsonException;

class Point extends Geometry
{
    protected float $lat;

    protected float $lng;

    public function __construct(float|int|string $lat, float|int|string $lng, ?int $srid = null)
    {
        $srid ??= (int) (getenv('SPATIAL_REF_SYS') ?: 0);
        parent::__construct($srid);

        $this->lat = (float) $lat;
        $this->lng = (float) $lng;
    }

    public function getLat(): float
    {
        return $this->lat;
    }

    public function setLat(float|int|string $lat): void
    {
        $this->lat = (float) $lat;
    }

    public function getLng(): float
    {
        return $this->lng;
    }

    public function setLng(float|int|string $lng): void
    {
        $this->lng = (float) $lng;
    }

    public function toPair(): string
    {
        return $this->getLng().' '.$this->getLat();
    }

    public static function fromPair(string $pair, ?int $srid = null): static
    {
        [$lng, $lat] = explode(' ', trim($pair, "\t\n\r \x0B()"));

        return new static((float) $lat, (float) $lng, $srid);
    }

    public function toWKT(): string
    {
        return sprintf('POINT(%s)', (string) $this);
    }

    public static function fromString(string $wktArgument, int $srid): static
    {
        return static::fromPair($wktArgument, $srid);
    }

    public function __toString(): string
    {
        return $this->getLng().' '.$this->getLat();
    }

    /**
     * @throws InvalidGeoJsonException
     */
    public static function fromJson(string|GeoJsonGeometry $geoJson): static
    {
        if (is_string($geoJson)) {
            $geoJson = GeoJson::jsonUnserialize(json_decode($geoJson, true));
        }

        if (! $geoJson instanceof GeoJsonPoint) {
            throw new InvalidGeoJsonException('Expected '.GeoJsonPoint::class.', got '.get_class($geoJson));
        }

        $coordinates = $geoJson->getCoordinates();

        return new self($coordinates[1], $coordinates[0]);
    }

    /**
     * Convert to GeoJson Point that is jsonable to GeoJSON.
     */
    public function jsonSerialize(): GeoJsonPoint
    {
        return new GeoJsonPoint([$this->getLng(), $this->getLat()]);
    }
}
