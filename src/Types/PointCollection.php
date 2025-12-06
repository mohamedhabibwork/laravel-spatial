<?php

declare(strict_types=1);

namespace Habib\LaravelSpatial\Types;

use ArrayAccess;
use InvalidArgumentException;

abstract class PointCollection extends GeometryCollection
{
    /**
     * The class of the items in the collection.
     */
    protected string $collectionItemType = Point::class;

    public function toPairList(): string
    {
        return implode(',', array_map(function (Point $point): string {
            return $point->toPair();
        }, $this->items));
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->validateItemType($value);

        parent::offsetSet($offset, $value);
    }

    /**
     * @return Point[]
     */
    public function getPoints(): array
    {
        return $this->items;
    }

    /**
     * @deprecated 2.1.0 Use array_unshift($multipoint, $point); instead
     * @see array_unshift
     * @see ArrayAccess
     */
    public function prependPoint(Point $point): void
    {
        array_unshift($this->items, $point);
    }

    /**
     * @deprecated 2.1.0 Use $multipoint[] = $point; instead
     * @see ArrayAccess
     */
    public function appendPoint(Point $point): void
    {
        $this->items[] = $point;
    }

    /**
     * @deprecated 2.1.0 Use array_splice($multipoint, $index, 0, [$point]); instead
     * @see array_splice
     * @see ArrayAccess
     */
    public function insertPoint(int $index, Point $point): void
    {
        if (count($this->items) - 1 < $index) {
            throw new InvalidArgumentException('$index is greater than the size of the array');
        }

        array_splice($this->items, $index, 0, [$point]);
    }
}
