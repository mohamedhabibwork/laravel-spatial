<?php

declare(strict_types=1);

namespace Habib\LaravelSpatial\Doctrine;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class GeometryCollection extends Type
{
    public const GEOMETRYCOLLECTION = 'geometrycollection';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return 'geometrycollection';
    }

    public function getName(): string
    {
        return self::GEOMETRYCOLLECTION;
    }
}
