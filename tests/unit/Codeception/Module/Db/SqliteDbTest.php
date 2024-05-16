<?php

declare(strict_types=1);

use Codeception\Stub;
use Codeception\TestInterface;
use Codeception\Util\ActionSequence;

final class SqliteDbTest extends AbstractDbTest
{
    public function getPopulator()
    {
        $config = $this->getConfig();
        @chmod('tests/data/sqlite.db', 0777);
        return 'cat ' . $config['dump'] . ' | sqlite3 tests/data/sqlite.db';
    }

    public function getConfig(): array
    {
        return [
            'dsn' => 'sqlite:tests/data/sqlite.db',
            'user' => 'root',
            'password' => '',
            'dump' => 'tests/data/dumps/sqlite.sql',
            'reconnect' => true,
            'cleanup' => true,
            'populate' => true
        ];
    }

    public function testConnectionIsResetOnEveryTestWhenReconnectIsTrue()
    {
        $testCase1 = Stub::makeEmpty(TestInterface::class);
        $testCase2 = Stub::makeEmpty(TestInterface::class);
        $testCase3 = Stub::makeEmpty(TestInterface::class);

        $this->module->_setConfig(['reconnect' => false]);
        $this->module->_beforeSuite();

        // Simulate a test that runs
        $this->module->_before($testCase1);

        $connection1 = spl_object_hash($this->module->dbh);
        $this->module->_after($testCase1);

        // Simulate a second test that runs
        $this->module->_before($testCase2);

        $connection2 = spl_object_hash($this->module->dbh);
        $this->module->_after($testCase2);
        $this->module->_afterSuite();

        $this->module->_setConfig(['reconnect' => true]);

        $this->module->_before($testCase3);

        $connection3 = spl_object_hash($this->module->dbh);
        $this->module->_after($testCase3);

        $this->assertSame($connection1, $connection2);
        $this->assertNotSame($connection3, $connection2);
    }

    public function testMultiDatabase()
    {
        $config = array_merge($this->getConfig(), [
            'dsn' => 'sqlite:tests/data/sqlite1.db',
            'cleanup' => false
        ]);
        $this->module->_reconfigure(
            [
                'databases' => ['db2' => $config],
            ]
        );
        $this->module->_beforeSuite();

        $testDataInDb1 = ['name' => 'userdb1', 'email' => 'userdb1@example.org'];
        $testDataInDb2 = ['name' => 'userdb2', 'email' => 'userdb2@example.org'];

        $this->module->_insertInDatabase('users', $testDataInDb1);
        $this->module->seeInDatabase('users', $testDataInDb1);

        $this->module->amConnectedToDatabase('db2');

        $this->module->_insertInDatabase('users', $testDataInDb2);
        $this->module->seeInDatabase('users', $testDataInDb2);
        $this->module->dontSeeInDatabase('users', $testDataInDb1);
    }

    public function testDatabaseIsAlwaysDefaultBeforeTest()
    {
        $config = array_merge($this->getConfig(), ['dsn' => 'sqlite:tests/data/sqlite1.db']);
        $this->module->_reconfigure(
            [
                'cleanup' => false,
                'databases' => ['db2' => $config],
            ]
        );
        $this->module->_beforeSuite();

        $testCase1 = Stub::makeEmpty(TestInterface::class);
        $testCase2 = Stub::makeEmpty(TestInterface::class);
        $testDataInDb1 = ['name' => 'userdb1', 'email' => 'userdb1@example.org'];
        $testDataInDb2 = ['name' => 'userdb2', 'email' => 'userdb2@example.org'];

        // Simulate a test that runs
        $this->module->_before($testCase1);
        $this->module->_insertInDatabase('users', $testDataInDb1);
        $this->module->seeInDatabase('users', $testDataInDb1);
        $this->module->amConnectedToDatabase('db2');
        $this->module->_insertInDatabase('users', $testDataInDb2);
        $this->module->seeInDatabase('users', $testDataInDb2);
        $this->module->_after($testCase1);

        // Simulate a second test that runs
        $this->module->_before($testCase2);
        $this->module->dontSeeInDatabase('users', $testDataInDb2);
        $this->module->seeInDatabase('users', $testDataInDb1);
        $this->module->_after($testCase2);
        $this->module->_afterSuite();
    }

    public function testMultiDatabaseWithArray()
    {
        $config = array_merge($this->getConfig(), [
            'dsn' => 'sqlite:tests/data/sqlite1.db',
            'cleanup' => false
        ]);
        $this->module->_reconfigure(
            [
                'databases' => ['db2' => $config],
            ]
        );
        $this->module->_beforeSuite();

        $testDataInDb1 = ['name' => 'userdb1', 'email' => 'userdb1@example.org'];
        $testDataInDb2 = ['name' => 'userdb2', 'email' => 'userdb2@example.org'];

        $this->module->_insertInDatabase('users', $testDataInDb1);
        $this->module->performInDatabase('db2', [
            'haveInDatabase' => ['users', $testDataInDb2],
            'seeInDatabase' => ['users', $testDataInDb2],
        ]);
        $this->module->seeInDatabase('users', $testDataInDb1);
        $this->module->dontSeeInDatabase('users', $testDataInDb2);
    }

    public function testMultiDatabaseWithActionSequence()
    {
        $config = array_merge($this->getConfig(), [
            'dsn' => 'sqlite:tests/data/sqlite1.db',
            'cleanup' => false
        ]);
        $this->module->_reconfigure(
            [
                'databases' => ['db2' => $config],
            ]
        );
        $this->module->_beforeSuite();

        $testDataInDb1 = ['name' => 'userdb1', 'email' => 'userdb1@example.org'];
        $testDataInDb2 = ['name' => 'userdb2', 'email' => 'userdb2@example.org'];

        $this->module->_insertInDatabase('users', $testDataInDb1);
        $this->module->performInDatabase(
            'db2',
            ActionSequence::build()
                ->haveInDatabase('users', $testDataInDb2)
                ->seeInDatabase('users', $testDataInDb2)
        );
        $this->module->seeInDatabase('users', $testDataInDb1);
        $this->module->dontSeeInDatabase('users', $testDataInDb2);
    }

    public function testMultiDatabaseWithAnonymousFunction()
    {
        $config = array_merge($this->getConfig(), [
            'dsn' => 'sqlite:tests/data/sqlite1.db',
            'cleanup' => false
        ]);
        $this->module->_reconfigure(
            [
                'databases' => ['db2' => $config],
            ]
        );
        $this->module->_beforeSuite();

        $testDataInDb1 = ['name' => 'userdb1', 'email' => 'userdb1@example.org'];
        $testDataInDb2 = ['name' => 'userdb2', 'email' => 'userdb2@example.org'];

        $this->module->_insertInDatabase('users', $testDataInDb1);
        $this->module->performInDatabase('db2', function ($module) use ($testDataInDb2) {
            $module->_insertInDatabase('users', $testDataInDb2);
            $module->seeInDatabase('users', $testDataInDb2);
        });
        $this->module->seeInDatabase('users', $testDataInDb1);
        $this->module->dontSeeInDatabase('users', $testDataInDb2);
    }

    public function testMultiDatabaseWithUnknowDatabase()
    {
        $this->expectException(Codeception\Exception\ModuleConfigException::class);
        $this->module->amConnectedToDatabase('db2');
    }

    public function testMultiDatabaseWithRemoveInserted()
    {
        $testCase1 = Stub::makeEmpty(TestInterface::class);
        $testCase2 = Stub::makeEmpty(TestInterface::class);
        $config = array_merge($this->getConfig(), [
            'dsn' => 'sqlite:tests/data/sqlite1.db',
            'cleanup' => false
        ]);
        $this->module->_reconfigure(
            [
                'databases' => ['db2' => $config],
            ]
        );
        $this->module->_beforeSuite();
        $this->module->_before($testCase1);

        $testDataInDb1 = ['name' => 'userdb1', 'email' => 'userdb1@example.org'];
        $testDataInDb2 = ['name' => 'userdb2', 'email' => 'userdb2@example.org'];

        $this->module->haveInDatabase('users', $testDataInDb1);
        $this->module->seeInDatabase('users', $testDataInDb1);
        $this->module->amConnectedToDatabase('db2');
        $this->module->_after($testCase1);

        $this->module->_before($testCase2);
        $this->module->dontSeeInDatabase('users', $testDataInDb1);
        $this->module->amConnectedToDatabase('db2');
        $this->module->_after($testCase2);
    }
}
