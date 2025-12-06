<?php

declare(strict_types=1);

namespace Habib\LaravelSpatial\Doctrine\Postgres;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

final class Point extends Type
{
    public const POINT = 'point';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return 'geometry(Point)';
    }

    public function getName(): string
    {
        return self::POINT;
    }
}

