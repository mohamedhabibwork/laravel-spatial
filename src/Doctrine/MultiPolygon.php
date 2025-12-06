<?php

declare(strict_types=1);

namespace Habib\LaravelSpatial\Doctrine;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class MultiPolygon extends Type
{
    public const MULTIPOLYGON = 'multipolygon';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return 'multipolygon';
    }

    public function getName(): string
    {
        return self::MULTIPOLYGON;
    }
}
