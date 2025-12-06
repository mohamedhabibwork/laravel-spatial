<?php

declare(strict_types=1);

namespace Habib\LaravelSpatial\Eloquent;

use Habib\LaravelSpatial\Types\GeometryInterface;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

final class Builder extends EloquentBuilder
{
    public function update(array $values): int
    {
        foreach ($values as $key => &$value) {
            if ($value instanceof GeometryInterface) {
                $value = $this->asWKT($value);
            }
        }

        return parent::update($values);
    }

    protected function asWKT(GeometryInterface $geometry): SpatialExpression
    {
        return new SpatialExpression($geometry);
    }
}
