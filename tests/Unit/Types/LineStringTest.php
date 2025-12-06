<?php

use Habib\LaravelSpatial\Types\LineString;
use Habib\LaravelSpatial\Types\Point;

class LineStringTest extends BaseTestCase
{
    private $points;

    protected function setUp()
    {
        $this->points = [new Point(0, 0), new Point(1, 1), new Point(2, 2)];
    }

    public function test_to_wkt()
    {
        $linestring = new LineString($this->points);

        $this->assertEquals('LINESTRING(0 0,1 1,2 2)', $linestring->toWKT());
    }

    public function test_from_wkt()
    {
        $linestring = LineString::fromWKT('LINESTRING(0 0, 1 1, 2 2)');
        $this->assertInstanceOf(LineString::class, $linestring);

        $this->assertEquals(3, $linestring->count());
    }

    public function test_to_string()
    {
        $linestring = new LineString($this->points);

        $this->assertEquals('0 0,1 1,2 2', (string) $linestring);
    }

    public function test_from_json()
    {
        $lineString = LineString::fromJson('{"type": "LineString","coordinates":[[1,1],[2,2]]}');
        $this->assertInstanceOf(LineString::class, $lineString);
        $lineStringPoints = $lineString->getGeometries();
        $this->assertEquals(new Point(1, 1), $lineStringPoints[0]);
        $this->assertEquals(new Point(2, 2), $lineStringPoints[1]);
    }

    public function test_invalid_geo_json_exception()
    {
        $this->assertException(
            \Habib\LaravelSpatial\Exceptions\InvalidGeoJsonException::class,
            sprintf('Expected %s, got %s', \GeoJson\Geometry\LineString::class, GeoJson\Geometry\Point::class)
        );
        LineString::fromJson('{"type":"Point","coordinates":[3.4,1.2]}');
    }

    public function test_json_serialize()
    {
        $lineString = new LineString($this->points);

        $this->assertInstanceOf(\GeoJson\Geometry\LineString::class, $lineString->jsonSerialize());
        $this->assertSame('{"type":"LineString","coordinates":[[0,0],[1,1],[2,2]]}', json_encode($lineString));
    }
}
