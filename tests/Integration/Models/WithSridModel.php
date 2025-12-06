<?php

use Habib\LaravelSpatial\Eloquent\SpatialTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * Class WithSridModel.
 *
 * @property int                                          id
 * @property \Habib\LaravelSpatial\Types\Point      location
 * @property \Habib\LaravelSpatial\Types\LineString line
 * @property \Habib\LaravelSpatial\Types\LineString shape
 */
class WithSridModel extends Model
{
    use SpatialTrait;

    protected $table = 'with_srid';

    protected $spatialFields = ['location', 'line'];

    public $timestamps = false;
}
