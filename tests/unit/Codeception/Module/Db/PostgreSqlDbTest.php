<?php

declare(strict_types=1);

use Codeception\Configuration;

require_once Configuration::testsDir().'unit/Codeception/Module/Db/AbstractDbTest.php';

/**
 * @group db
 */
final class PostgreSqlDbTest extends AbstractDbTest
{
    public function getPopulator(): string
    {
        return "psql -h localhost -d codeception_test -U postgres  < tests/data/dumps/postgres.sql";
    }

    public function getConfig(): array
    {
        if (!function_exists('pg_connect')) {
            $this->markTestSkipped();
        }

        $password = getenv('PGPASSWORD') ? getenv('PGPASSWORD') : null;

        return [
            'dsn' => 'pgsql:host=localhost;dbname=codeception_test',
            'user' => 'postgres',
            'password' => $password,
            'dump' => 'tests/data/dumps/postgres.sql',
            'reconnect' => true,
            'cleanup' => true,
            'populate' => true
        ];
    }
}
