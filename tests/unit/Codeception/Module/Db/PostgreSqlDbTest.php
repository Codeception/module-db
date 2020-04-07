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
        if (getenv('APPVEYOR')) {
            $this->markTestSkipped('Disabled on Appveyor');
        }
        return "psql -h localhost -d codeception_test -U postgres  < tests/data/dumps/postgres.sql";
    }

    public function getConfig()
    {
        if (!function_exists('pg_connect')) {
            $this->markTestSkipped();
        }
        
        if (getenv('APPVEYOR')) {
            $password = 'Password12!';
        }
        elseif (getenv('PGPASSWORD')) {
            $password = getenv('PGPASSWORD');
        }
        
        return [
            'dsn' => 'pgsql:host=localhost;dbname=codeception_test',
            'user' => 'postgres',
            'password' => $password ? $password : null,
            'dump' => 'tests/data/dumps/postgres.sql',
            'reconnect' => true,
            'cleanup' => true,
            'populate' => true
        ];
    }

}
