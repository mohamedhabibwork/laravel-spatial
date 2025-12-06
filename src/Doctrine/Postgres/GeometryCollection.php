<?php

declare(strict_types=1);

namespace Habib\LaravelSpatial\Doctrine\Postgres;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

final class GeometryCollection extends Type
{
    public const GEOMETRYCOLLECTION = 'geometrycollection';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return 'geometry(GeometryCollection)';
    }

    public function getName(): string
    {
        return self::GEOMETRYCOLLECTION;
    }
}

