<?php

declare(strict_types=1);

namespace Habib\LaravelSpatial\Doctrine\Postgres;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

final class MultiLineString extends Type
{
    public const MULTILINESTRING = 'multilinestring';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return 'geometry(MultiLineString)';
    }

    public function getName(): string
    {
        return self::MULTILINESTRING;
    }
}

