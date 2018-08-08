<?php

namespace src;

use carono\yii2migrate\ForeignKeyColumn;
use carono\yii2migrate\helpers\SchemaHelper;
use yii\db\Migration;

class ForeignKeyColumnBaseTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;
    protected $_migrate;

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

    protected function _before()
    {
        $migrate = $this->getMigrate();
        $migrate->createTable('{{%test_source}}', [
            'id' => $migrate->primaryKey(),
            'ref_id' => $migrate->integer()->defaultValue(0)
        ]);
        $migrate->createTable('{{%test_ref}}', ['id' => $migrate->primaryKey()]);
    }

    protected function _after()
    {
        $migrate = $this->getMigrate();
        $migrate->dropTable('{{%test_source}}');
        $migrate->dropTable('{{%test_ref}}');
    }

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

    public function testApply()
    {
        $this->getFk()->apply();
        $schema = $this->getMigrate()->db->getTableSchema('{{%test_source}}');
        $this->assertArrayHasKey('pfx_test_source[ref_id]_pfx_test_ref[id]_fk', $schema->foreignKeys);
    }

    public function testRemove()
    {
        $this->testApply();
        $this->getFk()->remove();
        $schema = $this->getMigrate()->db->getTableSchema('{{%test_source}}');
        $this->assertArrayNotHasKey('pfx_test_source[ref_id]_pfx_test_ref[id]_fk', $schema->foreignKeys);
    }

    public function testOnDeleteNull()
    {
        $fk = $this->getFk()->onDeleteNull();
        $fk->apply();
        $key = current(SchemaHelper::findTableForeignKeys($fk->migrate->db, 'pfx_test_source'));
        $this->assertSame(ForeignKeyColumn::FK_NULL, $key->onDelete);
    }

    public function testOnDeleteCascade()
    {
        $fk = $this->getFk()->onDeleteCascade();
        $fk->apply();
        $key = current(SchemaHelper::findTableForeignKeys($fk->migrate->db, 'pfx_test_source'));
        $this->assertSame(ForeignKeyColumn::FK_CASCADE, $key->onDelete);
    }

    public function testOnDeleteRestrict()
    {
        $fk = $this->getFk()->onDeleteRestrict();
        $fk->apply();
        $key = current(SchemaHelper::findTableForeignKeys($fk->migrate->db, 'pfx_test_source'));
        $this->assertSame(ForeignKeyColumn::FK_RESTRICT, $key->onDelete);
    }

    public function testOnDeleteNoAction()
    {
        $fk = $this->getFk()->onDeleteNoAction();
        $fk->apply();
        $key = current(SchemaHelper::findTableForeignKeys($fk->migrate->db, 'pfx_test_source'));
        $this->assertSame(ForeignKeyColumn::FK_NO_ACTION, $key->onDelete);
    }

    public function testOnDeleteDefault()
    {
        if ($this->getMigrate()->db->driverName === 'mysql') {
            return;
        }
        $fk = $this->getFk()->onDeleteDefault();
        $fk->apply();
        $key = current(SchemaHelper::findTableForeignKeys($fk->migrate->db, 'pfx_test_source'));
        $this->assertSame(ForeignKeyColumn::FK_DEFAULT, $key->onDelete);
    }

    public function testOnUpdateNull()
    {
        $fk = $this->getFk()->onUpdateNull();
        $fk->apply();
        $key = current(SchemaHelper::findTableForeignKeys($fk->migrate->db, 'pfx_test_source'));
        $this->assertSame(ForeignKeyColumn::FK_NULL, $key->onUpdate);
    }

    public function testOnUpdateCascade()
    {
        $fk = $this->getFk()->onUpdateCascade();
        $fk->apply();
        $key = current(SchemaHelper::findTableForeignKeys($fk->migrate->db, 'pfx_test_source'));
        $this->assertSame(ForeignKeyColumn::FK_CASCADE, $key->onUpdate);
    }

    public function testOnUpdateRestrict()
    {
        $fk = $this->getFk()->onUpdateRestrict();
        $fk->apply();
        $key = current(SchemaHelper::findTableForeignKeys($fk->migrate->db, 'pfx_test_source'));
        $this->assertSame(ForeignKeyColumn::FK_RESTRICT, $key->onUpdate);
    }

    public function testOnUpdateNoAction()
    {
        $fk = $this->getFk()->onUpdateNoAction();
        $fk->apply();
        $key = current(SchemaHelper::findTableForeignKeys($fk->migrate->db, 'pfx_test_source'));
        $this->assertSame(ForeignKeyColumn::FK_NO_ACTION, $key->onUpdate);
    }

    public function testOnUpdateDefault()
    {
        if ($this->getMigrate()->db->driverName === 'mysql') {
            return;
        }
        $fk = $this->getFk()->onUpdateDefault();
        $fk->apply();
        $key = current(SchemaHelper::findTableForeignKeys($fk->migrate->db, 'pfx_test_source'));
        $this->assertSame(ForeignKeyColumn::FK_DEFAULT, $key->onUpdate);
    }

    public function testGetRefColumn()
    {
        $fk = $this->getFk();
        $fk->refColumn('my_column');
        $this->assertSame('my_column', $fk->getRefColumn());
    }

    public function testGetRefColumnByTable()
    {
        $migrate = $this->getMigrate();
        $fk = (new ForeignKeyColumn($migrate->integer()))
            ->setMigrate($migrate)
            ->sourceTable('{{%test_source}}')
            ->sourceColumn('ref_id')
            ->refTable('{{%test_ref}}');
        $this->assertSame('id', $fk->getRefColumn());
    }
}