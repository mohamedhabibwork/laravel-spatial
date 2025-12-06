<?php

declare(strict_types=1);

namespace Habib\LaravelSpatial;

use Doctrine\DBAL\Types\Type as DoctrineType;
use Habib\LaravelSpatial\Connectors\ConnectionFactory;
use Habib\LaravelSpatial\Doctrine\Geometry;
use Habib\LaravelSpatial\Doctrine\GeometryCollection;
use Habib\LaravelSpatial\Doctrine\LineString;
use Habib\LaravelSpatial\Doctrine\MultiLineString;
use Habib\LaravelSpatial\Doctrine\MultiPoint;
use Habib\LaravelSpatial\Doctrine\MultiPolygon;
use Habib\LaravelSpatial\Doctrine\Point;
use Habib\LaravelSpatial\Doctrine\Polygon;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\DatabaseServiceProvider;

/**
 * Class SpatialServiceProvider.
 */
class SpatialServiceProvider extends DatabaseServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register(): void
    {
        // The connection factory is used to create the actual connection instances on
        // the database. We will inject the factory into the manager so that it may
        // make the connections while they are actually needed and not of before.
        $this->app->singleton('db.factory', function ($app): ConnectionFactory {
            return new ConnectionFactory($app);
        });

        // The database manager is used to resolve various connections, since multiple
        // connections might be managed. It also implements the connection resolver
        // interface which may be used by other components requiring connections.
        $this->app->singleton('db', function ($app): DatabaseManager {
            return new DatabaseManager($app, $app['db.factory']);
        });

        if (class_exists(DoctrineType::class)) {
            // Prevent geometry type fields from throwing a 'type not found' error when changing them
            $geometries = [
                'geometry' => Geometry::class,
                'point' => Point::class,
                'linestring' => LineString::class,
                'polygon' => Polygon::class,
                'multipoint' => MultiPoint::class,
                'multilinestring' => MultiLineString::class,
                'multipolygon' => MultiPolygon::class,
                'geometrycollection' => GeometryCollection::class,
            ];
            $typeNames = array_keys(DoctrineType::getTypesMap());
            foreach ($geometries as $type => $class) {
                if (! in_array($type, $typeNames, true)) {
                    DoctrineType::addType($type, $class);
                }
            }
        }
    }
}
