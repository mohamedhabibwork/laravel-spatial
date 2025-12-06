<?php

declare(strict_types=1);

namespace Habib\LaravelSpatial;

use Doctrine\DBAL\Types\Type as DoctrineType;
use Habib\LaravelSpatial\Schema\Builder;
use Habib\LaravelSpatial\Schema\Grammars\MySqlGrammar;
use Illuminate\Database\Grammar;
use Illuminate\Database\MySqlConnection as IlluminateMySqlConnection;
use PDO;

final class MysqlConnection extends IlluminateMySqlConnection
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
        return $this->withTablePrefix(new MySqlGrammar($this));
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
