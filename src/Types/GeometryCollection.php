<?php

declare(strict_types=1);

namespace Habib\LaravelSpatial\Types;

use ArrayAccess;
use ArrayIterator;
use Countable;
use GeoJson\Feature\FeatureCollection;
use GeoJson\GeoJson;
use GeoJson\Geometry\Geometry as GeoJsonGeometry;
use GeoJson\Geometry\GeometryCollection as GeoJsonGeometryCollection;
use Habib\LaravelSpatial\Exceptions\InvalidGeoJsonException;
use Illuminate\Contracts\Support\Arrayable;
use InvalidArgumentException;
use IteratorAggregate;

class GeometryCollection extends Geometry implements Arrayable, ArrayAccess, Countable, IteratorAggregate
{
    /**
     * The minimum number of items required to create this collection.
     */
    protected int $minimumCollectionItems = 0;

    /**
     * The class of the items in the collection.
     */
    protected string $collectionItemType = GeometryInterface::class;

    /**
     * The items contained in the spatial collection.
     *
     * @var GeometryInterface[]
     */
    protected array $items = [];

    /**
     * @param  GeometryInterface[]  $geometries
     *
     * @throws InvalidArgumentException
     */
    public function __construct(array $geometries, ?int $srid = null)
    {
        $srid ??= (int) (getenv('SPATIAL_REF_SYS') ?: 0);
        parent::__construct($srid);

        $this->validateItems($geometries);

        $this->items = $geometries;
    }

    /**
     * Checks whether the items are valid to create this collection.
     */
    protected function validateItems(array $items): void
    {
        $this->validateItemCount($items);

        foreach ($items as $item) {
            $this->validateItemType($item);
        }
    }

    /**
     * Checks whether the array has enough items to generate a valid WKT.
     *
     * @see $minimumCollectionItems
     */
    protected function validateItemCount(array $items): void
    {
        if (count($items) < $this->minimumCollectionItems) {
            $entries = $this->minimumCollectionItems === 1 ? 'entry' : 'entries';

            throw new InvalidArgumentException(sprintf(
                '%s must contain at least %d %s',
                get_class($this),
                $this->minimumCollectionItems,
                $entries
            ));
        }
    }

    /**
     * Checks the type of the items in the array.
     *
     * @see $collectionItemType
     */
    protected function validateItemType(mixed $item): void
    {
        if (! $item instanceof $this->collectionItemType) {
            throw new InvalidArgumentException(sprintf(
                '%s must be a collection of %s',
                get_class($this),
                $this->collectionItemType
            ));
        }
    }

    public static function fromString(string $wktArgument, int $srid): static
    {
        if (empty($wktArgument)) {
            return new static([]);
        }

        $geometry_strings = preg_split('/,\s*(?=[A-Za-z])/', $wktArgument);

        return new static(array_map(function (string $geometry_string): GeometryInterface {
            $klass = Geometry::getWKTClass($geometry_string);

            return call_user_func($klass.'::fromWKT', $geometry_string);
        }, $geometry_strings), $srid);
    }

    public static function fromJson(string|GeoJsonGeometry $geoJson): static
    {
        if (is_string($geoJson)) {
            $geoJson = GeoJson::jsonUnserialize(json_decode($geoJson, true));
        }

        if (! $geoJson instanceof FeatureCollection) {
            throw new InvalidGeoJsonException('Expected '.FeatureCollection::class.', got '.get_class($geoJson));
        }

        $set = [];
        foreach ($geoJson->getFeatures() as $feature) {
            $set[] = parent::fromJson($feature);
        }

        return new self($set);
    }

    /**
     * @return GeometryInterface[]
     */
    public function getGeometries(): array
    {
        return $this->items;
    }

    public function __toString(): string
    {
        return implode(',', array_map(function (GeometryInterface $geometry): string {
            return $geometry->toWKT();
        }, $this->items));
    }

    public function toWKT(): string
    {
        return sprintf('GEOMETRYCOLLECTION(%s)', (string) $this);
    }

    public function toArray(): array
    {
        return $this->items;
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->items);
    }

    public function offsetGet(mixed $offset): ?GeometryInterface
    {
        return $this->offsetExists($offset) ? $this->items[$offset] : null;
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->items[$offset]);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->validateItemType($value);

        if (is_null($offset)) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->items[$offset]);
    }

    public function count(): int
    {
        return count($this->items);
    }

    /**
     * Convert to GeoJson GeometryCollection that is jsonable to GeoJSON.
     */
    public function jsonSerialize(): mixed
    {
        $geometries = [];
        foreach ($this->items as $geometry) {
            $geometries[] = $geometry->jsonSerialize();
        }

        return new GeoJsonGeometryCollection($geometries);
    }
}
