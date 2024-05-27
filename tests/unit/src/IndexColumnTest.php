<?php

namespace src;

use carono\yii2migrate\IndexColumn;
use carono\yii2migrate\Migration;

class IndexColumnTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;
    protected $_migrate;

    protected function _before()
    {
    }

    protected function _after()
    {
    }

    /**
     * @param string $db
     *
     * @return Migration
     */
    protected function getMigrate()
    {
        if ($this->_migrate) {
            return $this->_migrate;
        }

        return $this->_migrate = new \carono\yii2migrate\Migration(['db' => \Yii::$app->db]);
    }

    // tests
    public function testSetMigrate()
    {
        $migrate = $this->getMigrate();
        $index = new IndexColumn();
        $index->setMigrate($migrate);
        $this->assertSame($migrate, $index->migrate);
    }

    public function testUnique()
    {
        $index = new IndexColumn();

        $index->unique(true);
        $this->assertTrue($index->getUnique());

        $index->unique(false);
        $this->assertFalse($index->getUnique());
    }

    public function testGetName()
    {
        $index = new IndexColumn();
        $index->name('index-name');
        $this->assertSame('index-name', $index->getName());
    }

    public function testFormIndexNameNonUnique()
    {
        $index = new IndexColumn();
        $index->setMigrate($this->getMigrate());

        $index->table('{{%user}}');
        $index->columns(['id', 'name']);
        $index->unique(false);
        $this->assertSame('pfx_user:id:name_idx', $index->formIndexName());
    }

    public function testFormIndexNameUnique()
    {
        $index = new IndexColumn();
        $index->setMigrate($this->getMigrate());

        $index->table('{{%user}}');
        $index->columns(['id', 'name']);
        $index->unique(true);
        $this->assertSame('pfx_user:id:name_unq', $index->formIndexName());
    }

    public function testGetIndexName()
    {
        $index = new IndexColumn();
        $index->setMigrate($this->getMigrate());
        $index->table('{{%user}}');
        $index->columns(['id', 'name']);
        $index->unique(true);
        $index->name('my-index');
        $this->assertSame('my-index', $index->formIndexName());
    }

    public function testGetTable()
    {
        $index = new IndexColumn();
        $index->table('{{%user}}');
        $this->assertSame('{{%user}}', $index->getTable());
    }

    public function testGetColumns()
    {
        $index = new IndexColumn();
        $columns = [
            'value' => $this->getMigrate()->string(),
            'data'  => $this->getMigrate()->integer(),
        ];
        $index->columns($columns);
        $this->assertSame($columns, $index->getColumns());
    }
}
