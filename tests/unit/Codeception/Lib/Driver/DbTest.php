<?php

declare(strict_types=1);

use Codeception\Lib\Driver\Db;
use Codeception\Test\Unit;
use Codeception\Util\ReflectionHelper;

/**
 * @group appveyor
 * @group db
 */
final class DbTest extends Unit
{
    /**
     * @dataProvider getWhereCriteria
     */
    public function testGenerateWhereClause(array $criteria, string $expectedResult)
    {
        $db = new Db('sqlite:tests/data/sqlite.db','root','');
        $result = ReflectionHelper::invokePrivateMethod($db, 'generateWhereClause', [&$criteria]);
        $this->assertSame($expectedResult, $result);
    }

    public function getWhereCriteria(): array
    {
        return [
            'like'        => [['email like' => 'mail.ua'], 'WHERE "email" LIKE ? '],
            '<='          => [['id <=' => '5'],            'WHERE "id" <= ? '],
            '<'           => [['id <' => '5'],             'WHERE "id" < ? '],
            '>='          => [['id >=' => '5'],            'WHERE "id" >= ? '],
            '>'           => [['id >' => '5'],             'WHERE "id" > ? '],
            '!='          => [['id !=' => '5'],            'WHERE "id" != ? '],
            'is null'     => [['id' => null],              'WHERE "id" IS NULL '],
            'is not null' => [['id !=' => null],           'WHERE "id" IS NOT NULL '],
        ];
    }
}
