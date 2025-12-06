<?php

declare(strict_types=1);

namespace Habib\LaravelSpatial\Doctrine\Postgres;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

final class LineString extends Type
{
    public const LINESTRING = 'linestring';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return 'geometry(LineString)';
    }

    public function getName(): string
    {
        return self::LINESTRING;
    }
}

