<?php

namespace src;

use carono\yii2migrate\ForeignKeyColumn;
use yii\db\Migration;

class ForeignKeyColumnTest extends \Codeception\Test\Unit
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
     * @return ForeignKeyColumn
     */
    protected function getFk()
    {
        $migrate = $this->getMigrate();

        return (new ForeignKeyColumn($migrate->integer()))
            ->setMigrate($migrate)
            ->sourceTable('{{%test_source}}')
            ->sourceColumn('ref_id')
            ->refTable('{{%test_ref}}')
            ->refColumn('id');
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

    public function testSetMigrate()
    {
        $fk = new ForeignKeyColumn($this->getMigrate()->integer());
        $fk->setMigrate($this->getMigrate());
        $this->assertSame($this->getMigrate(), $fk->migrate);
    }

    public function testSetName()
    {
        $fk = $this->getFk();
        $fk->setName('my-name');
        $this->assertSame('my-name', $fk->getName());
    }

    public function testGetName()
    {
        $fk = $this->getFk()->setName('my-index');
        $this->assertSame('my-index', $fk->getName());
    }

    public function testFormIndexName()
    {
        $fk = $this->getFk();
        $this->assertSame('pfx_test_source[ref_id]_pfx_test_ref[id]_fk', $fk->formIndexName());
    }

    public function testRefTable()
    {
        $fk = new ForeignKeyColumn($this->getMigrate()->integer());
        $fk->refTable('{{%ref_table}}');
        $this->assertSame('{{%ref_table}}', $fk->getRefTable());
    }

    public function testRefColumn()
    {
        $fk = new ForeignKeyColumn($this->getMigrate()->integer());
        $fk->refColumn('user_id');
        $this->assertSame('user_id', $fk->getRefColumn());
    }

    public function testSourceTable()
    {
        $fk = new ForeignKeyColumn($this->getMigrate()->integer());
        $fk->sourceTable('{{%source_table}}');
        $this->assertSame('{{%source_table}}', $fk->getSourceTable());
    }

    public function testSourceColumn()
    {
        $fk = new ForeignKeyColumn($this->getMigrate()->integer());
        $fk->sourceColumn('user_id');
        $this->assertSame('user_id', $fk->getSourceColumn());
    }

    public function testOnDelete()
    {
        $fk = new ForeignKeyColumn($this->getMigrate()->integer());

        $fk->onDeleteCascade();
        $this->assertSame(ForeignKeyColumn::FK_CASCADE, $fk->getOnDelete());

        $fk->onDeleteNull();
        $this->assertSame(ForeignKeyColumn::FK_NULL, $fk->getOnDelete());

        $fk->onDeleteDefault();
        $this->assertSame(ForeignKeyColumn::FK_DEFAULT, $fk->getOnDelete());
    }

    public function testFormName()
    {
        $sourceTable = 'source_table';
        $sourceColumn = 'source_id';
        $refTable = 'ref_table';
        $refColumn = 'id';

        $fk = (new ForeignKeyColumn($this->getMigrate()->integer()))->setMigrate($this->getMigrate());

        $key = 'source_table[source_id]_ref_table[id]_fk';
        $this->assertSame($key, $fk->formName($sourceTable, $sourceColumn, $refTable, $refColumn));
    }
}
