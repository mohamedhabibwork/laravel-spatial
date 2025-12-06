<?php

declare(strict_types=1);

namespace Habib\LaravelSpatial\Eloquent;

use Habib\LaravelSpatial\Types\GeometryInterface;
use Illuminate\Database\Grammar;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Query\Grammars\MySqlGrammar;
use Illuminate\Database\Query\Grammars\PostgresGrammar;

final class SpatialExpression extends Expression
{
    public function __construct(
        protected readonly GeometryInterface $value
    ) {}

    public function getValue(Grammar $grammar): string
    {
        return match (true) {
            $grammar instanceof PostgresGrammar => "ST_GeomFromText(?, ?)",
            $grammar instanceof MySqlGrammar => "ST_GeomFromText(?, ?, 'axis-order=long-lat')",
            default => "ST_GeomFromText(?, ?)",
        };
    }

    public function getSpatialValue(): string
    {
        return $this->value->toWkt();
    }

    public function getSrid(): int
    {
        return $this->value->getSrid();
    }
}
