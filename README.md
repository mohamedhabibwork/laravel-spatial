# Laravel Spatial Extension

[![Build Status](https://img.shields.io/travis/mohamedhabibwork/laravel-spatial.svg?style=flat-square)](https://travis-ci.org/mohamedhabibwork/laravel-spatial)
[![Code Climate](https://img.shields.io/codeclimate/maintainability/mohamedhabibwork/laravel-spatial.svg?style=flat-square)](https://codeclimate.com/github/mohamedhabibwork/laravel-spatial/maintainability)
[![Code Climate](https://img.shields.io/codeclimate/c/mohamedhabibwork/laravel-spatial.svg?style=flat-square&colorB=4BCA2A)](https://codeclimate.com/github/mohamedhabibwork/laravel-spatial/test_coverage)
[![Packagist](https://img.shields.io/packagist/v/mohamedhabibwork/laravel-spatial.svg?style=flat-square)](https://packagist.org/packages/mohamedhabibwork/laravel-spatial)
[![Packagist](https://img.shields.io/packagist/dt/mohamedhabibwork/laravel-spatial.svg?style=flat-square)](https://packagist.org/packages/mohamedhabibwork/laravel-spatial)
[![StyleCI](https://github.styleci.io/repos/83766141/shield?branch=master)](https://github.styleci.io/repos/83766141)
[![license](https://img.shields.io/github/license/mashape/apistatus.svg?style=flat-square)](LICENSE)

Modern Laravel package for working with spatial data types and functions. Supports both **MySQL 8+** and **PostgreSQL 16+** (with PostGIS) using a transparent, unified API.

## Features

- 🌐 **Multi-Database Support**: Works seamlessly with MySQL 8+ and PostgreSQL 16+ (PostGIS)
- 🔄 **Transparent API**: Same code works for both databases
- 🚀 **Modern PHP**: Built with PHP 8.2+ features (typed properties, match expressions, readonly, etc.)
- 📦 **Laravel 11+**: Full integration with Laravel's query builder and Eloquent ORM
- 🎯 **Type Safety**: Comprehensive type hints and strict types throughout
- 🗺️ **SRID Support**: Full support for Spatial Reference System Identifiers
- 🔍 **Spatial Functions**: Distance calculations, geometric comparisons, and more

## Database Support

### MySQL 8+
- Uses MySQL's native [Spatial Data Types](https://dev.mysql.com/doc/refman/8.0/en/spatial-type-overview.html)
- Leverages [MySQL Spatial Functions](https://dev.mysql.com/doc/refman/8.0/en/spatial-function-reference.html)
- SRID support with `POINT SRID 4326` syntax
- Spatial indexes with `SPATIAL INDEX`

### PostgreSQL 16+ (PostGIS)
- Requires [PostGIS extension](https://postgis.net/) 
- Uses `geometry(Point, 4326)` type syntax
- Spatial indexes with `GIST INDEX`
- Full PostGIS function support

## Requirements

- PHP 8.2 or higher
- Laravel 11.0 or higher
- MySQL 8.0+ **OR** PostgreSQL 16+ with PostGIS extension

## Installation

Install via Composer:

```bash
composer require mohamedhabibwork/laravel-spatial
```

### PostgreSQL Setup

For PostgreSQL, you must enable the PostGIS extension:

```sql
CREATE EXTENSION IF NOT EXISTS postgis;
```

The package service provider is auto-discovered by Laravel. For manual registration, add to `config/app.php`:

```php
'providers' => [
    Habib\LaravelSpatial\SpatialServiceProvider::class,
],
```

## Version History

- **`6.x.x`**: MySQL 8+ and PostgreSQL 16+ support with PHP 8.2+ (Current)
- `5.x.x`: MySQL 5.7 and 8.0 (Laravel 8+)
- `4.x.x`: MySQL 8.0 with SRID support (Laravel 8+)
- `3.x.x`: MySQL 8.0 with SRID support (Laravel < 8.0)
- `2.x.x`: MySQL 5.7 and 8.0 (Laravel < 8.0)
- `1.x.x`: MySQL 5.6 and 5.5

## Quickstart

### Create a Migration

```bash
php artisan make:migration create_places_table
```

Define your spatial columns:

```php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('places', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            // Add spatial data fields
            $table->point('location')->nullable();
            $table->polygon('area')->nullable();
            $table->timestamps();
        });
        
        // With SRID (WGS84 spheroid)
        // Schema::create('places', function (Blueprint $table) {
        //     $table->id();
        //     $table->string('name')->unique();
        //     $table->point('location', 4326)->nullable();
        //     $table->polygon('area', 4326)->nullable();
        //     $table->timestamps();
        // });
    }

    public function down(): void
    {
        Schema::dropIfExists('places');
    }
};
```

Run the migration:

```bash
php artisan migrate
```

### Create a Model

```bash
php artisan make:model Place
```

Use the `SpatialTrait` and define spatial fields:

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Habib\LaravelSpatial\Eloquent\SpatialTrait;
use Habib\LaravelSpatial\Types\Point;
use Habib\LaravelSpatial\Types\Polygon;

class Place extends Model
{
    use SpatialTrait;

    protected $fillable = ['name'];

    protected $spatialFields = [
        'location',
        'area',
    ];
}
```

### Save a Model

```php
use App\Models\Place;
use Habib\LaravelSpatial\Types\Point;
use Habib\LaravelSpatial\Types\Polygon;
use Habib\LaravelSpatial\Types\LineString;

// Create a place with a point
$place = new Place();
$place->name = 'Empire State Building';
$place->location = new Point(40.7484404, -73.9878441);  // lat, lng
$place->save();

// With SRID
$place->location = new Point(40.7484404, -73.9878441, 4326);
$place->save();

// Create a polygon
$place->area = new Polygon([new LineString([
    new Point(40.74894149554006, -73.98615270853043),
    new Point(40.74848633046773, -73.98648262023926),
    new Point(40.747925497790725, -73.9851602911949),
    new Point(40.74837050671544, -73.98482501506805),
    new Point(40.74894149554006, -73.98615270853043)
])], 4326);
$place->save();
```

### Retrieve a Model

```php
$place = Place::first();
$lat = $place->location->getLat();  // 40.7484404
$lng = $place->location->getLng();  // -73.9878441
```

## Geometry Classes

All geometry types implement a common interface and work identically on both MySQL and PostgreSQL.

| Class | OpenGIS Type |
|-------|--------------|
| `Point($lat, $lng, $srid = 0)` | [Point](https://dev.mysql.com/doc/refman/8.0/en/gis-class-point.html) |
| `MultiPoint(Point[], $srid = 0)` | [MultiPoint](https://dev.mysql.com/doc/refman/8.0/en/gis-class-multipoint.html) |
| `LineString(Point[], $srid = 0)` | [LineString](https://dev.mysql.com/doc/refman/8.0/en/gis-class-linestring.html) |
| `MultiLineString(LineString[], $srid = 0)` | [MultiLineString](https://dev.mysql.com/doc/refman/8.0/en/gis-class-multilinestring.html) |
| `Polygon(LineString[], $srid = 0)` | [Polygon](https://dev.mysql.com/doc/refman/8.0/en/gis-class-polygon.html) |
| `MultiPolygon(Polygon[], $srid = 0)` | [MultiPolygon](https://dev.mysql.com/doc/refman/8.0/en/gis-class-multipolygon.html) |
| `GeometryCollection(Geometry[], $srid = 0)` | [GeometryCollection](https://dev.mysql.com/doc/refman/8.0/en/gis-class-geometrycollection.html) |

### Working with Geometries

#### From/To WKT (Well Known Text)

```php
use Habib\LaravelSpatial\Types\Point;
use Habib\LaravelSpatial\Types\Polygon;

$point = Point::fromWKT('POINT(2 1)');
echo $point->toWKT();  // POINT(2 1)

$polygon = Polygon::fromWKT('POLYGON((0 0,4 0,4 4,0 4,0 0),(1 1, 2 1, 2 2, 1 2,1 1))');
echo $polygon->toWKT();
```

#### From/To GeoJSON

```php
use Habib\LaravelSpatial\Types\Point;
use Habib\LaravelSpatial\Types\Geometry;

$point = new Point(40.7484404, -73.9878441);
$json = json_encode($point);
// {
//   "type": "Feature",
//   "properties": {},
//   "geometry": {
//     "type": "Point",
//     "coordinates": [-73.9878441, 40.7484404]
//   }
// }

// Deserialize
$location = Geometry::fromJson('{"type":"Point","coordinates":[3.4,1.2]}');
```

## Spatial Scopes

Query with spatial functions using Eloquent scopes. The same API works for both MySQL and PostgreSQL:

```php
use App\Models\Place;
use Habib\LaravelSpatial\Types\Point;
use Habib\LaravelSpatial\Types\Polygon;

// Distance queries
$center = new Point(40.7484, -73.9878);
$nearby = Place::distance('location', $center, 1000)->get();

// Distance excluding the point itself
$others = Place::distanceExcludingSelf('location', $center, 5000)->get();

// Spherical distance (uses Earth's curvature)
$nearby = Place::distanceSphere('location', $center, 5000)->get();

// Geometric comparisons
$polygon = Polygon::fromWKT('POLYGON((0 0,0 5,5 5,5 0,0 0))');

Place::within('location', $polygon)->get();
Place::contains('area', $point)->get();
Place::intersects('area', $line)->get();
Place::crosses('line', $geometry)->get();
Place::disjoint('area', $geometry)->get();
Place::overlaps('area', $polygon)->get();
Place::equals('location', $point)->get();
Place::touches('area', $polygon)->get();

// Ordering by distance
$ordered = Place::orderByDistance('location', $center)->get();
$ordered = Place::orderByDistanceSphere('location', $center, 'desc')->get();
```

### Available Scopes

- `distance($column, $geometry, $distance)`
- `distanceExcludingSelf($column, $geometry, $distance)`
- `distanceSphere($column, $geometry, $distance)`
- `distanceSphereExcludingSelf($column, $geometry, $distance)`
- `within($column, $polygon)`
- `contains($column, $geometry)`
- `crosses($column, $geometry)`
- `disjoint($column, $geometry)`
- `equals($column, $geometry)`
- `intersects($column, $geometry)`
- `overlaps($column, $geometry)`
- `doesTouch($column, $geometry)`
- `orderByDistance($column, $geometry, $direction = 'asc')`
- `orderByDistanceSphere($column, $geometry, $direction = 'asc')`

## Migrations

### Available Column Types

```php
$table->geometry('column_name', $srid = 0);
$table->point('column_name', $srid = 0);
$table->lineString('column_name', $srid = 0);
$table->polygon('column_name', $srid = 0);
$table->multiPoint('column_name', $srid = 0);
$table->multiLineString('column_name', $srid = 0);
$table->multiPolygon('column_name', $srid = 0);
$table->geometryCollection('column_name', $srid = 0);
```

### Spatial Indexes

```php
Schema::table('places', function (Blueprint $table) {
    // Make column NOT NULL (required for spatial indexes)
    $table->point('location')->nullable(false)->change();
    
    // Add spatial index
    $table->spatialIndex('location');
});

// Drop spatial index
Schema::table('places', function (Blueprint $table) {
    $table->dropSpatialIndex(['location']);
    // or by index name
    $table->dropSpatialIndex('places_location_spatial');
});
```

**Note**: Spatial indexes require columns to be `NOT NULL`. For MySQL, InnoDB tables support spatial indexes (MySQL 5.7.5+). For PostgreSQL, GIST indexes are used automatically.

## Database-Specific Considerations

While the API is identical, there are some internal differences:

### MySQL 8+
- Column definition: `POINT SRID 4326`
- Function calls: `ST_GeomFromText(?, ?, 'axis-order=long-lat')`
- Index type: `SPATIAL INDEX`

### PostgreSQL + PostGIS
- Column definition: `geometry(Point, 4326)`
- Function calls: `ST_GeomFromText(?, ?)`
- Index type: `GIST INDEX`
- Requires PostGIS extension

The package handles these differences automatically, providing a unified API.

## Testing

```bash
# Run all tests
composer test

# Unit tests only
composer test:unit

# Integration tests (requires database)
composer test:integration
```

### Integration Test Setup

For MySQL:
```bash
docker run -d -p 3306:3306 -e MYSQL_ROOT_PASSWORD=root -e MYSQL_DATABASE=spatial_test mysql:8.0
```

For PostgreSQL:
```bash
docker run -d -p 5432:5432 -e POSTGRES_PASSWORD=postgres -e POSTGRES_DB=spatial_test postgis/postgis:16-3.4
```

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request. For major changes, please open an issue first to discuss what you would like to change.

## Credits

- Originally inspired by [njbarrett's Laravel PostGIS package](https://github.com/njbarrett/laravel-postgis)
- Original MySQL implementation by Joseph Estefane
- Multi-database support and PHP 8.2+ modernization

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).
