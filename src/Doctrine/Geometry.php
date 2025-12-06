<?php

declare(strict_types=1);

namespace Habib\LaravelSpatial\Doctrine;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class Geometry extends Type
{
    public const GEOMETRY = 'geometry';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return 'geometry';
    }

    public function getName(): string
    {
        return self::GEOMETRY;
    }
}
