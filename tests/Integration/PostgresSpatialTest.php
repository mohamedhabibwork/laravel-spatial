<?php

use Habib\LaravelSpatial\Types\GeometryCollection;
use Habib\LaravelSpatial\Types\LineString;
use Habib\LaravelSpatial\Types\MultiPoint;
use Habib\LaravelSpatial\Types\MultiPolygon;
use Habib\LaravelSpatial\Types\Point;
use Habib\LaravelSpatial\Types\Polygon;

/**
 * PostgreSQL Spatial Test
 *
 * This test suite verifies that spatial operations work correctly with PostgreSQL/PostGIS.
 * The tests mirror the MySQL tests but use PostgreSQL-specific connection.
 */
final class PostgresSpatialTest extends PostgresIntegrationBaseTestCase
{
    protected array $migrations = [
        CreateLocationTable::class,
        UpdateLocationTable::class,
    ];

    public function test_spatial_fields_not_defined_exception(): void
    {
        $geo = new NoSpatialFieldsModel;
        $geo->geometry = new Point(1, 2);
        $geo->save();

        $this->assertException(\Habib\LaravelSpatial\Exceptions\SpatialFieldsNotDefinedException::class);
        NoSpatialFieldsModel::all();
    }

    public function test_insert_point(): void
    {
        $geo = new GeometryModel;
        $geo->location = new Point(1, 2);
        $geo->save();
        $this->assertDatabaseHas('geometry', ['id' => $geo->id]);
    }

    public function test_insert_line_string(): void
    {
        $geo = new GeometryModel;

        $geo->location = new Point(1, 2);
        $geo->line = new LineString([new Point(1, 1), new Point(2, 2)]);
        $geo->save();
        $this->assertDatabaseHas('geometry', ['id' => $geo->id]);
    }

    public function test_insert_polygon(): void
    {
        $geo = new GeometryModel;

        $geo->location = new Point(1, 2);
        $geo->shape = Polygon::fromWKT('POLYGON((0 10,10 10,10 0,0 0,0 10))');
        $geo->save();
        $this->assertDatabaseHas('geometry', ['id' => $geo->id]);
    }

    public function test_insert_multi_point(): void
    {
        $geo = new GeometryModel;

        $geo->location = new Point(1, 2);
        $geo->multi_locations = new MultiPoint([new Point(1, 1), new Point(2, 2)]);
        $geo->save();
        $this->assertDatabaseHas('geometry', ['id' => $geo->id]);
    }

    public function test_insert_multi_polygon(): void
    {
        $geo = new GeometryModel;

        $geo->location = new Point(1, 2);

        $geo->multi_shapes = new MultiPolygon([
            Polygon::fromWKT('POLYGON((0 10,10 10,10 0,0 0,0 10))'),
            Polygon::fromWKT('POLYGON((0 0,0 5,5 5,5 0,0 0))'),
        ]);
        $geo->save();
        $this->assertDatabaseHas('geometry', ['id' => $geo->id]);
    }

    public function test_insert_geometry_collection(): void
    {
        $geo = new GeometryModel;

        $geo->location = new Point(1, 2);

        $geo->multi_geometries = new GeometryCollection([
            Polygon::fromWKT('POLYGON((0 10,10 10,10 0,0 0,0 10))'),
            Polygon::fromWKT('POLYGON((0 0,0 5,5 5,5 0,0 0))'),
            new Point(0, 0),
        ]);
        $geo->save();
        $this->assertDatabaseHas('geometry', ['id' => $geo->id]);
    }

    public function test_update(): void
    {
        $geo = new GeometryModel;
        $geo->location = new Point(1, 2);
        $geo->save();

        $to_update = GeometryModel::all()->first();
        $to_update->location = new Point(2, 3);
        $to_update->save();

        $updated = GeometryModel::all()->first();
        $this->assertEquals(2, $updated->location->getLat());
        $this->assertEquals(3, $updated->location->getLng());
    }

    public function test_distance_in_kilometers(): void
    {
        $loc1 = new GeometryModel;
        $loc1->location = new Point(40.767864, -73.971732);
        $loc1->save();

        $loc2 = new GeometryModel;
        $loc2->location = new Point(40.767664, -73.971271);
        $loc2->save();

        $a = GeometryModel::distance('location', $loc1->location, 1)->get();

        $this->assertCount(2, $a);
        $this->assertTrue($a->contains('id', $loc1->id));
        $this->assertTrue($a->contains('id', $loc2->id));
    }

    public function test_distance_sphere(): void
    {
        $loc1 = new GeometryModel;
        $loc1->location = new Point(40.767864, -73.971732);
        $loc1->save();

        $loc2 = new GeometryModel;
        $loc2->location = new Point(40.767664, -73.971271);
        $loc2->save();

        // Distance sphere in meters
        $a = GeometryModel::distanceSphere('location', $loc1->location, 100)->get();

        $this->assertCount(2, $a);
    }

    public function test_scope_within(): void
    {
        $point = new GeometryModel;
        $point->location = new Point(1, 1);
        $point->save();

        $polygon = Polygon::fromWKT('POLYGON((0 0,0 5,5 5,5 0,0 0))');

        $results = GeometryModel::within('location', $polygon)->get();

        $this->assertCount(1, $results);
        $this->assertEquals($point->id, $results->first()->id);
    }

    public function test_scope_contains(): void
    {
        $geo = new GeometryModel;
        $geo->location = new Point(1, 1);
        $geo->shape = Polygon::fromWKT('POLYGON((0 0,0 5,5 5,5 0,0 0))');
        $geo->save();

        $point = new Point(2, 2);

        $results = GeometryModel::contains('shape', $point)->get();

        $this->assertCount(1, $results);
        $this->assertEquals($geo->id, $results->first()->id);
    }

    public function test_scope_intersects(): void
    {
        $geo = new GeometryModel;
        $geo->location = new Point(1, 1);
        $geo->shape = Polygon::fromWKT('POLYGON((0 0,0 5,5 5,5 0,0 0))');
        $geo->save();

        $line = LineString::fromWKT('LINESTRING(-1 -1, 6 6)');

        $results = GeometryModel::intersects('shape', $line)->get();

        $this->assertCount(1, $results);
        $this->assertEquals($geo->id, $results->first()->id);
    }

    public function test_order_by_distance(): void
    {
        $loc1 = new GeometryModel;
        $loc1->location = new Point(1, 1);
        $loc1->save();

        $loc2 = new GeometryModel;
        $loc2->location = new Point(2, 2);
        $loc2->save();

        $point = new Point(1.5, 1.5);

        $results = GeometryModel::orderByDistance('location', $point)->get();

        $this->assertCount(2, $results);
        $this->assertEquals($loc2->id, $results->first()->id);
    }
}

