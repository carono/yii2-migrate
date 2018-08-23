<?php

namespace src;

use carono\yii2migrate\Migration;
use carono\yii2migrate\PivotColumn;

class PivotColumnTest extends \Codeception\Test\Unit
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
     * @return Migration
     */
    protected function getMigrate()
    {
        if ($this->_migrate) {
            return $this->_migrate;
        }
        return $this->_migrate = new \carono\yii2migrate\Migration(['db' => \Yii::$app->db]);
    }

    public function testMigrate()
    {
        $migrate = $this->getMigrate();
        $pivot = new PivotColumn();
        $pivot->setMigrate($migrate);
        $this->assertSame($migrate, $pivot->migrate);
    }

    public function testToString()
    {
        $pivot = new PivotColumn();
        $this->assertSame($pivot->getPrefix(), (string)$pivot);
    }

    public function testSetSuffix()
    {
        $pivot = new PivotColumn();
        $pivot->setSuffix('my-name');
        $this->assertSame('my-name', $pivot->getSuffix());
    }

    public function testTableName()
    {
        $pivot = new PivotColumn();
        $pivot->tableName('my-table-name');
        $this->assertSame('{{%my-table-name}}', $pivot->getTableName());
    }

    public function testColumns()
    {
        $pivot = new PivotColumn();
        $columns = ['id' => 'int'];
        $pivot->columns($columns);
        $this->assertSame($columns, $pivot->getColumns());
    }

    public function testGetTableName()
    {
        $pivot = new PivotColumn();
        $pivot->tableName('pivot_table');
        $this->assertSame('{{%pivot_table}}', $pivot->getTableName());
    }

    public function testGetNameAuto()
    {
        $pivot = new PivotColumn();
        $pivot->sourceTable('{{%user}}');
        $pivot->refTable('{{%photo}}');
        $pivot->setSuffix('photos');

        $this->assertSame('{{%pv_user_photos}}', $pivot->getTableName());
    }

    public function testSetPrefix()
    {
        $pivot = new PivotColumn();
        $pivot->setPrefix('prefix');
        $this->assertSame('prefix', $pivot->getPrefix());
    }

    public function testGetRefTable()
    {
        $pivot = new PivotColumn();
        $pivot->refTable('{{%photo}}');
        $this->assertSame('{{%photo}}', $pivot->getRefTable());
    }

    public function testGetRefColumn()
    {
        $pivot = new PivotColumn();
        $pivot->refColumn('id');
        $this->assertSame('id', $pivot->getRefColumn());
    }

    public function testGetRefColumnAuto()
    {
        $pivot = new PivotColumn();
        $pivot->refTable('{{%photo}}');
        $this->assertSame('photo_id', $pivot->getRefColumn());
    }

    public function testGetSourceTable()
    {
        $pivot = new PivotColumn();
        $pivot->sourceTable('{{%user}}');
        $this->assertSame('{{%user}}', $pivot->getSourceTable());
    }

    public function testGetSourceColumn()
    {
        $pivot = new PivotColumn();
        $pivot->sourceColumn('id');
        $this->assertSame('id', $pivot->getSourceColumn());
    }

    public function testGetSourceColumnAuto()
    {
        $pivot = new PivotColumn();
        $pivot->sourceTable('photo');
        $this->assertSame('photo_id', $pivot->getSourceColumn());
    }
}