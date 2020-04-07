<?php

require_once \Codeception\Configuration::testsDir().'unit/Codeception/Module/Db/TestsForDb.php';

/**
 * @group appveyor
 * @group db
 */
class PostgreSqlDbTest extends TestsForDb
{
    public function getPopulator()
    {
        if (getenv('DB_POSTGRESQL_PASSWORD')) {
            $this->markTestSkipped('Disabled on Appveyor');
        }
        return "psql -d codeception_test -U postgres  < tests/data/dumps/postgres.sql";
    }

    public function getConfig()
    {
        if (!function_exists('pg_connect')) {
            $this->markTestSkipped();
        }
        return [
            'dsn' => 'pgsql:host=localhost;dbname=codeception_test',
            'user' => 'postgres',
            'password' => 'postgres',
            'dump' => 'tests/data/dumps/postgres.sql',
            'reconnect' => true,
            'cleanup' => true,
            'populate' => true
        ];
    }

}
