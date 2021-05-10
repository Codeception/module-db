<?php

declare(strict_types=1);

use Codeception\Lib\DbPopulator;
use Codeception\Test\Unit;

/**
 * @group db
 * Class DbPopulatorTest
 */
final class DbPopulatorTest extends Unit
{
    public function testCommandBuilderInterpolatesVariables()
    {
        $dbPopulator = new DbPopulator(
            [
                'populate'  => true,
                'dsn'       => 'mysql:host=127.0.0.1;dbname=my_db',
                'dump'      => 'tests/data/dumps/sqlite.sql',
                'user'      => 'root',
                'populator' => 'mysql -u $user -h $host -D $dbname < $dump',
                'databases' => []
            ]
        );

        $this->assertEquals(
            ['mysql -u root -h 127.0.0.1 -D my_db < tests/data/dumps/sqlite.sql'],
            $dbPopulator->buildCommands()
        );
    }

    public function testCommandBuilderInterpolatesVariablesMultiDump()
    {
        $dbPopulator = new DbPopulator(
            [
                'populate'  => true,
                'dsn'       => 'mysql:host=127.0.0.1;dbname=my_db',
                'dump'      => [
                    'tests/data/dumps/sqlite.sql',
                    'tests/data/dumps/sqlite2.sql',
                ],
                'user'      => 'root',
                'populator' => 'mysql -u $user -h $host -D $dbname < $dump'

            ]
        );

        $this->assertEquals(
            [
                'mysql -u root -h 127.0.0.1 -D my_db < tests/data/dumps/sqlite.sql',
                'mysql -u root -h 127.0.0.1 -D my_db < tests/data/dumps/sqlite2.sql'
            ],
            $dbPopulator->buildCommands()
        );
    }

    public function testCommandBuilderWontTouchVariablesNotFound()
    {
        $dbPopulator = new DbPopulator([
            'populator' => 'noop_tool -u $user -h $host -D $dbname < $dump',
            'user' => 'root',
        ]);
        $this->assertEquals(
            ['noop_tool -u root -h $host -D $dbname < $dump'],
            $dbPopulator->buildCommands()
        );
    }
}
