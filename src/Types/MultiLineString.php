<?php

declare(strict_types=1);

namespace Habib\LaravelSpatial\Types;

use GeoJson\GeoJson;
use GeoJson\Geometry\Geometry as GeoJsonGeometry;
use GeoJson\Geometry\MultiLineString as GeoJsonMultiLineString;
use Habib\LaravelSpatial\Exceptions\InvalidGeoJsonException;

class MultiLineString extends GeometryCollection
{
    /**
     * The minimum number of items required to create this collection.
     */
    protected int $minimumCollectionItems = 1;

    /**
     * The class of the items in the collection.
     */
    protected string $collectionItemType = LineString::class;

    /**
     * @return LineString[]
     */
    public function getLineStrings(): array
    {
        return $this->items;
    }

    public function toWKT(): string
    {
        return sprintf('MULTILINESTRING(%s)', (string) $this);
    }

    public static function fromString(string $wktArgument, int $srid): static
    {
        $str = preg_split('/\)\s*,\s*\(/', substr(trim($wktArgument), 1, -1));
        $lineStrings = array_map(function (string $data) use ($srid): LineString {
            return LineString::fromString($data, $srid);
        }, $str);

        return new static($lineStrings, $srid);
    }

    public function __toString(): string
    {
        return implode(',', array_map(function (LineString $lineString): string {
            return sprintf('(%s)', (string) $lineString);
        }, $this->getLineStrings()));
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->validateItemType($value);

        parent::offsetSet($offset, $value);
    }

    public static function fromJson(string|GeoJsonGeometry $geoJson): static
    {
        if (is_string($geoJson)) {
            $geoJson = GeoJson::jsonUnserialize(json_decode($geoJson, true));
        }

        if (! $geoJson instanceof GeoJsonMultiLineString) {
            throw new InvalidGeoJsonException('Expected '.GeoJsonMultiLineString::class.', got '.get_class($geoJson));
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
     * Convert to GeoJson MultiLineString that is jsonable to GeoJSON.
     */
    public function jsonSerialize(): mixed
    {
        $lineStrings = [];

        foreach ($this->items as $lineString) {
            $lineStrings[] = $lineString->jsonSerialize();
        }

        return new GeoJsonMultiLineString($lineStrings);
    }
}
