<?php

declare(strict_types=1);

namespace Habib\LaravelSpatial\Types;

use GeoIO\WKB\Parser\Parser;
use GeoJson\GeoJson;
use GeoJson\Geometry\Geometry as GeoJsonGeometry;
use Habib\LaravelSpatial\Exceptions\UnknownWKTTypeException;
use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;

abstract class Geometry implements GeometryInterface, Jsonable, JsonSerializable
{
    /**
     * @var array<int, class-string<Geometry>>
     */
    protected static array $wkb_types = [
        1 => Point::class,
        2 => LineString::class,
        3 => Polygon::class,
        4 => MultiPoint::class,
        5 => MultiLineString::class,
        6 => MultiPolygon::class,
        7 => GeometryCollection::class,
    ];

    protected int $srid;

    public function __construct(?int $srid = null)
    {
        $srid ??= (int) (getenv('SPATIAL_REF_SYS') ?: 0);
        $this->srid = $srid;
    }

    /**
     * @return class-string<Geometry>
     *
     * @throws UnknownWKTTypeException
     */
    public static function getWKTClass(string $value): string
    {
        $left = strpos($value, '(');
        if ($left === false) {
            throw new UnknownWKTTypeException('Invalid WKT format');
        }

        $type = trim(substr($value, 0, $left));

        return match (strtoupper($type)) {
            'POINT' => Point::class,
            'LINESTRING' => LineString::class,
            'POLYGON' => Polygon::class,
            'MULTIPOINT' => MultiPoint::class,
            'MULTILINESTRING' => MultiLineString::class,
            'MULTIPOLYGON' => MultiPolygon::class,
            'GEOMETRYCOLLECTION' => GeometryCollection::class,
            default => throw new UnknownWKTTypeException('Type was '.$type),
        };
    }

    public static function fromWKB(string $wkb): static
    {
        $srid = substr($wkb, 0, 4);
        $srid = unpack('L', $srid)[1];

        $wkb = substr($wkb, 4);
        $parser = new Parser(new Factory);

        /** @var Geometry $parsed */
        $parsed = $parser->parse($wkb);

        if ($srid > 0) {
            $parsed->setSrid($srid);
        }

        return $parsed;
    }

    public static function fromWKT(string $wkt, ?int $srid = null): static
    {
        $wktArgument = static::getWKTArgument($wkt);
        $srid ??= (int) (getenv('SPATIAL_REF_SYS') ?: 0);

        return static::fromString($wktArgument, $srid);
    }

    public static function getWKTArgument(string $value): string
    {
        $left = strpos($value, '(');
        $right = strrpos($value, ')');

        if ($left === false || $right === false) {
            return '';
        }

        return substr($value, $left + 1, $right - $left - 1);
    }

    public static function fromJson(string|GeoJsonGeometry $geoJson): static
    {
        if (is_string($geoJson)) {
            $geoJson = GeoJson::jsonUnserialize(json_decode($geoJson, true));
        }

        if ($geoJson->getType() === 'FeatureCollection') {
            return GeometryCollection::fromJson($geoJson);
        }

        if ($geoJson->getType() === 'Feature') {
            $geoJson = $geoJson->getGeometry();
        }

        $type = '\Habib\LaravelSpatial\Types\\'.$geoJson->getType();

        return $type::fromJson($geoJson);
    }

    public function getSrid(): int
    {
        return $this->srid;
    }

    public function setSrid(?int $srid = null): static
    {
        $srid ??= (int) (getenv('SPATIAL_REF_SYS') ?: 0);
        $this->srid = $srid;

        return $this;
    }

    public function toJson($options = 0): string
    {
        return json_encode($this, $options);
    }

    abstract public static function fromString(string $wktArgument, int $srid): static;

    abstract public function toWKT(): string;
}
