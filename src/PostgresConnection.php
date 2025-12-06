<?php

declare(strict_types=1);

namespace Habib\LaravelSpatial;

use Doctrine\DBAL\Types\Type as DoctrineType;
use Habib\LaravelSpatial\Schema\Builder;
use Habib\LaravelSpatial\Schema\Grammars\PostgresGrammar;
use Illuminate\Database\Grammar;
use Illuminate\Database\PostgresConnection as IlluminatePostgresConnection;
use PDO;

final class PostgresConnection extends IlluminatePostgresConnection
{
    public function __construct(PDO $pdo, string $database = '', string $tablePrefix = '', array $config = [])
    {
        parent::__construct($pdo, $database, $tablePrefix, $config);

        if (class_exists(DoctrineType::class)) {
            // Prevent geometry type fields from throwing a 'type not found' error when changing them
            $geometries = [
                'geometry',
                'point',
                'linestring',
                'polygon',
                'multipoint',
                'multilinestring',
                'multipolygon',
                'geometrycollection',
                'geomcollection',
            ];
            $dbPlatform = $this->getDoctrineSchemaManager()->getDatabasePlatform();
            foreach ($geometries as $type) {
                $dbPlatform->registerDoctrineTypeMapping($type, 'string');
            }
        }
    }

    /**
     * Get the default schema grammar instance.
     */
    protected function getDefaultSchemaGrammar(): Grammar
    {
        return $this->withTablePrefix(new PostgresGrammar($this));
    }

    /**
     * Get a schema builder instance for the connection.
     */
    public function getSchemaBuilder(): Builder
    {
        if (is_null($this->schemaGrammar)) {
            $this->useDefaultSchemaGrammar();
        }

        return new Builder($this);
    }
}

