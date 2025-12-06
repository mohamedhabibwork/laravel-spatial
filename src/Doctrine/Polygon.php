<?php

declare(strict_types=1);

namespace Habib\LaravelSpatial\Doctrine;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class Polygon extends Type
{
    public const POLYGON = 'polygon';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return 'polygon';
    }

    public function getName(): string
    {
        return self::POLYGON;
    }
}
