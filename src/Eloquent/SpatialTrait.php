<?php

declare(strict_types=1);

namespace Habib\LaravelSpatial\Eloquent;

use Habib\LaravelSpatial\Exceptions\SpatialFieldsNotDefinedException;
use Habib\LaravelSpatial\Exceptions\UnknownSpatialFunctionException;
use Habib\LaravelSpatial\Exceptions\UnknownSpatialRelationFunction;
use Habib\LaravelSpatial\Support\DatabaseHelper;
use Habib\LaravelSpatial\Types\Geometry;
use Habib\LaravelSpatial\Types\GeometryInterface;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

/**
 * Trait SpatialTrait.
 *
 * @method static distance($geometryColumn, $geometry, $distance)
 * @method static distanceExcludingSelf($geometryColumn, $geometry, $distance)
 * @method static distanceSphere($geometryColumn, $geometry, $distance)
 * @method static distanceSphereExcludingSelf($geometryColumn, $geometry, $distance)
 * @method static comparison($geometryColumn, $geometry, $relationship)
 * @method static within($geometryColumn, $polygon)
 * @method static crosses($geometryColumn, $geometry)
 * @method static contains($geometryColumn, $geometry)
 * @method static disjoint($geometryColumn, $geometry)
 * @method static equals($geometryColumn, $geometry)
 * @method static intersects($geometryColumn, $geometry)
 * @method static overlaps($geometryColumn, $geometry)
 * @method static doesTouch($geometryColumn, $geometry)
 * @method static orderBySpatial($geometryColumn, $geometry, $orderFunction, $direction = 'asc')
 * @method static orderByDistance($geometryColumn, $geometry, $direction = 'asc')
 * @method static orderByDistanceSphere($geometryColumn, $geometry, $direction = 'asc')
 */
trait SpatialTrait
{
    /**
     * @var array<string, GeometryInterface>
     */
    public array $geometries = [];

    /**
     * @var array<int, string>
     */
    protected array $stRelations = [
        'within',
        'crosses',
        'contains',
        'disjoint',
        'equals',
        'intersects',
        'overlaps',
        'touches',
    ];

    /**
     * @var array<int, string>
     */
    protected array $stOrderFunctions = [
        'distance',
        'distance_sphere',
    ];

    /**
     * Create a new Eloquent query builder for the model.
     */
    public function newEloquentBuilder(\Illuminate\Database\Query\Builder $query): Builder
    {
        return new Builder($query);
    }

    protected function newBaseQueryBuilder(): BaseBuilder
    {
        $connection = $this->getConnection();

        return new BaseBuilder(
            $connection,
            $connection->getQueryGrammar(),
            $connection->getPostProcessor()
        );
    }

    protected function performInsert(EloquentBuilder $query, array $options = []): bool
    {
        foreach ($this->attributes as $key => $value) {
            if ($value instanceof GeometryInterface) {
                $this->geometries[$key] = $value;
                $this->attributes[$key] = new SpatialExpression($value);
            }
        }

        $insert = parent::performInsert($query, $options);

        foreach ($this->geometries as $key => $value) {
            $this->attributes[$key] = $value;
        }

        return $insert;
    }

    public function setRawAttributes(array $attributes, bool $sync = false): static
    {
        $spatial_fields = $this->getSpatialFields();

        foreach ($attributes as $attribute => &$value) {
            if (in_array($attribute, $spatial_fields, true) && is_string($value) && strlen($value) >= 13) {
                $value = Geometry::fromWKB($value);
            }
        }

        return parent::setRawAttributes($attributes, $sync);
    }

    /**
     * @return array<int, string>
     */
    public function getSpatialFields(): array
    {
        if (property_exists($this, 'spatialFields')) {
            return $this->spatialFields;
        }

        throw new SpatialFieldsNotDefinedException(__CLASS__.' has to define $spatialFields');
    }

    public function isColumnAllowed(string $geometryColumn): bool
    {
        if (! in_array($geometryColumn, $this->getSpatialFields(), true)) {
            throw new SpatialFieldsNotDefinedException;
        }

        return true;
    }

    /**
     * Get the ST_GeomFromText function call appropriate for the database.
     */
    protected function getSTGeomFromTextSQL(Builder $query): string
    {
        return DatabaseHelper::getGeomFromTextSql($query->getConnection());
    }

    /**
     * Get the appropriate column wrapper for the database.
     */
    protected function wrapColumn(Builder $query, string $column): string
    {
        return DatabaseHelper::wrapColumn($query->getConnection(), $column);
    }

    public function scopeDistance(Builder $query, string $geometryColumn, GeometryInterface $geometry, float $distance): Builder
    {
        $this->isColumnAllowed($geometryColumn);
        
        $wrappedColumn = $this->wrapColumn($query, $geometryColumn);
        $stGeomFromText = $this->getSTGeomFromTextSQL($query);

        $query->whereRaw("st_distance({$wrappedColumn}, {$stGeomFromText}) <= ?", [
            $geometry->toWkt(),
            $geometry->getSrid(),
            $distance,
        ]);

        return $query;
    }

    public function scopeDistanceExcludingSelf(Builder $query, string $geometryColumn, GeometryInterface $geometry, float $distance): Builder
    {
        $this->isColumnAllowed($geometryColumn);

        $query = $this->scopeDistance($query, $geometryColumn, $geometry, $distance);
        
        $wrappedColumn = $this->wrapColumn($query, $geometryColumn);
        $stGeomFromText = $this->getSTGeomFromTextSQL($query);

        $query->whereRaw("st_distance({$wrappedColumn}, {$stGeomFromText}) != 0", [
            $geometry->toWkt(),
            $geometry->getSrid(),
        ]);

        return $query;
    }

    public function scopeDistanceValue(Builder $query, string $geometryColumn, GeometryInterface $geometry, string $alias = 'distance'): void
    {
        $this->isColumnAllowed($geometryColumn);

        $columns = $query->getQuery()->columns;

        if (! $columns) {
            $query->select('*');
        }

        $wrappedColumn = $this->wrapColumn($query, $geometryColumn);
        $stGeomFromText = $this->getSTGeomFromTextSQL($query);

        $query->selectRaw("st_distance({$wrappedColumn}, {$stGeomFromText}) as {$alias}", [
            $geometry->toWkt(),
            $geometry->getSrid(),
        ]);
    }

    public function scopeDistanceSphere(Builder $query, string $geometryColumn, GeometryInterface $geometry, float $distance): Builder
    {
        $this->isColumnAllowed($geometryColumn);

        $wrappedColumn = $this->wrapColumn($query, $geometryColumn);
        $stGeomFromText = $this->getSTGeomFromTextSQL($query);

        $query->whereRaw("st_distance_sphere({$wrappedColumn}, {$stGeomFromText}) <= ?", [
            $geometry->toWkt(),
            $geometry->getSrid(),
            $distance,
        ]);

        return $query;
    }

    public function scopeDistanceSphereExcludingSelf(Builder $query, string $geometryColumn, GeometryInterface $geometry, float $distance): Builder
    {
        $this->isColumnAllowed($geometryColumn);

        $query = $this->scopeDistanceSphere($query, $geometryColumn, $geometry, $distance);

        $wrappedColumn = $this->wrapColumn($query, $geometryColumn);
        $stGeomFromText = $this->getSTGeomFromTextSQL($query);

        $query->whereRaw("st_distance_sphere({$wrappedColumn}, {$stGeomFromText}) != 0", [
            $geometry->toWkt(),
            $geometry->getSrid(),
        ]);

        return $query;
    }

    public function scopeDistanceSphereValue(Builder $query, string $geometryColumn, GeometryInterface $geometry, string $alias = 'distance'): void
    {
        $this->isColumnAllowed($geometryColumn);

        $columns = $query->getQuery()->columns;

        if (! $columns) {
            $query->select('*');
        }

        $wrappedColumn = $this->wrapColumn($query, $geometryColumn);
        $stGeomFromText = $this->getSTGeomFromTextSQL($query);

        $query->selectRaw("st_distance_sphere({$wrappedColumn}, {$stGeomFromText}) as {$alias}", [
            $geometry->toWkt(),
            $geometry->getSrid(),
        ]);
    }

    public function scopeComparison(Builder $query, string $geometryColumn, GeometryInterface $geometry, string $relationship): Builder
    {
        $this->isColumnAllowed($geometryColumn);

        if (! in_array(strtolower($relationship), $this->stRelations, true)) {
            throw new UnknownSpatialRelationFunction($relationship);
        }

        $wrappedColumn = $this->wrapColumn($query, $geometryColumn);
        $stGeomFromText = $this->getSTGeomFromTextSQL($query);

        $query->whereRaw("st_{$relationship}({$wrappedColumn}, {$stGeomFromText})", [
            $geometry->toWkt(),
            $geometry->getSrid(),
        ]);

        return $query;
    }

    public function scopeWithin(Builder $query, string $geometryColumn, GeometryInterface $polygon): Builder
    {
        return $this->scopeComparison($query, $geometryColumn, $polygon, 'within');
    }

    public function scopeCrosses(Builder $query, string $geometryColumn, GeometryInterface $geometry): Builder
    {
        return $this->scopeComparison($query, $geometryColumn, $geometry, 'crosses');
    }

    public function scopeContains(Builder $query, string $geometryColumn, GeometryInterface $geometry): Builder
    {
        return $this->scopeComparison($query, $geometryColumn, $geometry, 'contains');
    }

    public function scopeDisjoint(Builder $query, string $geometryColumn, GeometryInterface $geometry): Builder
    {
        return $this->scopeComparison($query, $geometryColumn, $geometry, 'disjoint');
    }

    public function scopeEquals(Builder $query, string $geometryColumn, GeometryInterface $geometry): Builder
    {
        return $this->scopeComparison($query, $geometryColumn, $geometry, 'equals');
    }

    public function scopeIntersects(Builder $query, string $geometryColumn, GeometryInterface $geometry): Builder
    {
        return $this->scopeComparison($query, $geometryColumn, $geometry, 'intersects');
    }

    public function scopeOverlaps(Builder $query, string $geometryColumn, GeometryInterface $geometry): Builder
    {
        return $this->scopeComparison($query, $geometryColumn, $geometry, 'overlaps');
    }

    public function scopeDoesTouch(Builder $query, string $geometryColumn, GeometryInterface $geometry): Builder
    {
        return $this->scopeComparison($query, $geometryColumn, $geometry, 'touches');
    }

    public function scopeOrderBySpatial(Builder $query, string $geometryColumn, GeometryInterface $geometry, string $orderFunction, string $direction = 'asc'): Builder
    {
        $this->isColumnAllowed($geometryColumn);

        if (! in_array(strtolower($orderFunction), $this->stOrderFunctions, true)) {
            throw new UnknownSpatialFunctionException($orderFunction);
        }

        $wrappedColumn = $this->wrapColumn($query, $geometryColumn);
        $stGeomFromText = $this->getSTGeomFromTextSQL($query);

        $query->orderByRaw("st_{$orderFunction}({$wrappedColumn}, {$stGeomFromText}) {$direction}", [
            $geometry->toWkt(),
            $geometry->getSrid(),
        ]);

        return $query;
    }

    public function scopeOrderByDistance(Builder $query, string $geometryColumn, GeometryInterface $geometry, string $direction = 'asc'): Builder
    {
        return $this->scopeOrderBySpatial($query, $geometryColumn, $geometry, 'distance', $direction);
    }

    public function scopeOrderByDistanceSphere(Builder $query, string $geometryColumn, GeometryInterface $geometry, string $direction = 'asc'): Builder
    {
        return $this->scopeOrderBySpatial($query, $geometryColumn, $geometry, 'distance_sphere', $direction);
    }
}
