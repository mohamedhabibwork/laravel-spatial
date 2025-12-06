<?php

use Habib\LaravelSpatial\SpatialServiceProvider;
use Illuminate\Support\Facades\DB;
use Laravel\BrowserKitTesting\TestCase as BaseTestCase;

abstract class PostgresIntegrationBaseTestCase extends BaseTestCase
{
    protected array $migrations = [];

    /**
     * Boots the application.
     */
    public function createApplication(): \Illuminate\Foundation\Application
    {
        $app = require __DIR__.'/../../vendor/laravel/laravel/bootstrap/app.php';
        $app->register(SpatialServiceProvider::class);

        $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

        // Configure PostgreSQL connection
        $app['config']->set('database.default', 'pgsql');
        $app['config']->set('database.connections.pgsql.driver', 'pgsql');
        $app['config']->set('database.connections.pgsql.host', env('PGSQL_HOST', '127.0.0.1'));
        $app['config']->set('database.connections.pgsql.port', env('PGSQL_PORT', '5432'));
        $app['config']->set('database.connections.pgsql.database', env('PGSQL_DATABASE', 'spatial_test'));
        $app['config']->set('database.connections.pgsql.username', env('PGSQL_USERNAME', 'postgres'));
        $app['config']->set('database.connections.pgsql.password', env('PGSQL_PASSWORD', ''));

        return $app;
    }

    /**
     * Setup DB before each test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Ensure PostGIS extension is enabled
        try {
            DB::statement('CREATE EXTENSION IF NOT EXISTS postgis');
        } catch (\Exception $e) {
            // Extension might already exist or user doesn't have permission
        }

        $this->onMigrations(function ($migrationClass): void {
            (new $migrationClass)->up();
        });
    }

    protected function tearDown(): void
    {
        $this->onMigrations(function ($migrationClass): void {
            (new $migrationClass)->down();
        }, true);

        parent::tearDown();
    }

    protected function assertDatabaseHas($table, array $data, $connection = null): void
    {
        if (method_exists($this, 'seeInDatabase')) {
            $this->seeInDatabase($table, $data, $connection);
        } else {
            parent::assertDatabaseHas($table, $data, $connection);
        }
    }

    protected function assertException(string $exceptionName, ?string $exceptionMessage = null): void
    {
        if (method_exists(parent::class, 'expectException')) {
            parent::expectException($exceptionName);
            if ($exceptionMessage !== null) {
                $this->expectExceptionMessage($exceptionMessage);
            }
        } else {
            $this->setExpectedException($exceptionName, $exceptionMessage);
        }
    }

    private function onMigrations(\Closure $closure, bool $reverse_sort = false): void
    {
        $migrations = $this->migrations;
        $reverse_sort ? rsort($migrations, SORT_STRING) : sort($migrations, SORT_STRING);

        foreach ($migrations as $migrationClass) {
            $closure($migrationClass);
        }
    }
}

