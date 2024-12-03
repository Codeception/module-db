<?php

declare(strict_types=1);

final class MssqlDblibDbTest extends AbstractDbTest
{
    public function getPopulator(): string
    {
        $config = $this->getConfig();

        return sprintf('/opt/mssql-tools/bin/sqlcmd -S $host -U $user -P $password -d $dbname -i %s', $config['dump']);
    }

    public function getConfig(): array
    {
        $host = getenv('MSSQL_HOST') ?: 'localhost';
        $user = getenv('MSSQL_USER') ?: 'sa';
        $password = getenv('MSSQL_PASSWORD') ?: '';
        $database = getenv('MSSQL_DB') ?: 'codeception_test';
        $dsn = getenv('MSSQL_DSN') ?: 'dblib:host=' . $host . ';dbname=' . $database;

        return [
            'dsn' => $dsn,
            'user' => $user,
            'password' => $password,
            'dump' => 'tests/data/dumps/mssql.sql',
            'reconnect' => true,
            'repopulate' => true,
            'populate' => true,
        ];
    }
}
