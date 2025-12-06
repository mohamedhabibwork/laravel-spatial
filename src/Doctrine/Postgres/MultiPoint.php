<?php

declare(strict_types=1);

namespace Habib\LaravelSpatial\Doctrine\Postgres;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

final class MultiPoint extends Type
{
    public const MULTIPOINT = 'multipoint';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return 'geometry(MultiPoint)';
    }

    public function getName(): string
    {
        return self::MULTIPOINT;
    }
}

