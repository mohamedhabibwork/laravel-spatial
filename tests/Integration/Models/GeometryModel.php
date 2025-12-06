<?php

use Habib\LaravelSpatial\Eloquent\SpatialTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * Class GeometryModel.
 *
 * @property int                                          id
 * @property \Habib\LaravelSpatial\Types\Point      location
 * @property \Habib\LaravelSpatial\Types\LineString line
 * @property \Habib\LaravelSpatial\Types\LineString shape
 */
class GeometryModel extends Model
{
    use SpatialTrait;

    protected $table = 'geometry';

    protected $spatialFields = ['location', 'line', 'multi_geometries'];
}
