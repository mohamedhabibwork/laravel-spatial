<?php

declare(strict_types=1);

namespace Habib\LaravelSpatial\Doctrine;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class MultiLineString extends Type
{
    public const MULTILINESTRING = 'multilinestring';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return 'multilinestring';
    }

    public function getName(): string
    {
        return self::MULTILINESTRING;
    }
}
