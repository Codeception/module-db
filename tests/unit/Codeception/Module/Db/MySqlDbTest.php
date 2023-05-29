<?php

declare(strict_types=1);

use Codeception\Configuration;
use Codeception\Stub;
use Codeception\TestInterface;

require_once Configuration::testsDir().'unit/Codeception/Module/Db/AbstractDbTest.php';

/**
 * @group db
 */
final class MySqlDbTest extends AbstractDbTest
{
    public function getPopulator(): string
    {
        $config = $this->getConfig();
        $password = $config['password'] ? '-p'.$config['password'] : '';
        return sprintf('mysql -u $user %s $dbname < %s', $password, $config['dump']);
    }

    public function getConfig(): array
    {
        $host = getenv('MYSQL_HOST') ?: 'localhost';
        $user = getenv('MYSQL_USER') ?: 'root';
        $password = getenv('MYSQL_PASSWORD') ?: '';
        $database = getenv('MYSQL_DB') ?: 'codeception_test';
        $dsn = getenv('MYSQL_DSN') ?: 'mysql:host=' . $host . ';dbname=' . $database;

        return [
            'dsn' => $dsn,
            'user' => $user,
            'password' => $password,
            'dump' => 'tests/data/dumps/mysql.sql',
            'reconnect' => true,
            'cleanup' => true,
            'populate' => true
        ];
    }

    /**
     * Overridden, Using MYSQL CONNECTION_ID to get current connection
     */
    public function testConnectionIsResetOnEveryTestWhenReconnectIsTrue()
    {
        $testCase1 = Stub::makeEmpty(TestInterface::class);
        $testCase2 = Stub::makeEmpty(TestInterface::class);
        $testCase3 = Stub::makeEmpty(TestInterface::class);


        $this->module->_setConfig(['reconnect' => false]);
        $this->module->_beforeSuite();

        // Simulate a test that runs
        $this->module->_before($testCase1);

        $connection1 = $this->module->dbh->query('SELECT CONNECTION_ID()')->fetch(PDO::FETCH_COLUMN);
        $this->module->_after($testCase1);

        // Simulate a second test that runs
        $this->module->_before($testCase2);

        $connection2 = $this->module->dbh->query('SELECT CONNECTION_ID()')->fetch(PDO::FETCH_COLUMN);
        $this->module->_after($testCase2);
        $this->module->_afterSuite();

        $this->module->_setConfig(['reconnect' => true]);

        $this->module->_before($testCase3);

        $connection3 = $this->module->dbh->query('SELECT CONNECTION_ID()')->fetch(PDO::FETCH_COLUMN);
        $this->module->_after($testCase3);

        $this->assertSame($connection1, $connection2);
        $this->assertNotSame($connection3, $connection2);
    }

    public function testInitialQueriesAreExecuted()
    {
        $dbName = 'test_db';
        $config = $this->module->_getConfig();
        $config['initial_queries'] = [
            'CREATE DATABASE IF NOT EXISTS ' . $dbName . ';',
            'USE ' . $dbName . ';',
        ];
        $this->module->_reconfigure($config);
        $this->module->_before(Stub::makeEmpty(TestInterface::class));

        $usedDatabaseName = $this->module->dbh->query('SELECT DATABASE();')->fetch(PDO::FETCH_COLUMN);

        $this->assertSame($dbName, $usedDatabaseName);
    }

    public function testGrabColumnFromDatabase()
    {
        $this->module->_beforeSuite();
        $emails = $this->module->grabColumnFromDatabase('users', 'email');
        $this->assertSame(
            [
                'davert@mail.ua',
                'nick@mail.ua',
                'miles@davis.com',
                'charlie@parker.com',
            ],
            $emails);
    }

    public function testGrabEntryFromDatabaseShouldFailIfNotFound()
    {
        try {
            $this->module->grabEntryFromDatabase('users', ['email' => 'doesnot@exist.info']);
            $this->fail("should have thrown an exception");
        } catch (\Throwable $t) {
            $this->assertInstanceOf(AssertionError::class, $t);
        }
    }

    public function testGrabEntryFromDatabaseShouldReturnASingleEntry()
    {
        $this->module->_beforeSuite();
        $result = $this->module->grabEntryFromDatabase('users', ['is_active' => true]);

        $this->assertArrayNotHasKey(0, $result);
    }

    public function testGrabEntryFromDatabaseShouldReturnAnAssocArray()
    {
        $this->module->_beforeSuite();
        $result = $this->module->grabEntryFromDatabase('users', ['is_active' => true]);

        $this->assertArrayHasKey('is_active', $result);
    }

    public function testGrabEntriesFromDatabaseShouldReturnAnEmptyArrayIfNoRowMatches()
    {
        $this->module->_beforeSuite();
        $result = $this->module->grabEntriesFromDatabase('users', ['email' => 'doesnot@exist.info']);
        $this->assertEquals([], $result);
    }

    public function testGrabEntriesFromDatabaseShouldReturnAllMatchedRows()
    {
        $this->module->_beforeSuite();
        $result = $this->module->grabEntriesFromDatabase('users', ['is_active' => true]);

        $this->assertCount(3, $result);
    }

    public function testGrabEntriesFromDatabaseShouldReturnASetOfAssocArray()
    {
        $this->module->_beforeSuite();
        $result = $this->module->grabEntriesFromDatabase('users', ['is_active' => true]);

        $this->assertEquals(true, array_key_exists('is_active', $result[0]));
    }

    public function testHaveInDatabaseAutoIncrementOnANonPrimaryKey()
    {
        $testData = [
            'id' => 777,
        ];
        $this->module->haveInDatabase('auto_increment_not_on_pk', $testData);
        $this->module->seeInDatabase('auto_increment_not_on_pk', $testData);
        $this->module->_after(Stub::makeEmpty(TestInterface::class));

        $this->module->dontSeeInDatabase('auto_increment_not_on_pk', $testData);
    }

    public function testHaveInDatabaseAutoIncrementOnCompositePrimaryKey()
    {
        $testData = [
            'id' => 777,
        ];
        $this->module->haveInDatabase('auto_increment_on_composite_pk', $testData);
        $this->module->seeInDatabase('auto_increment_on_composite_pk', $testData);
        $this->module->_after(Stub::makeEmpty(TestInterface::class));

        $this->module->dontSeeInDatabase('auto_increment_on_composite_pk', $testData);
    }
}
