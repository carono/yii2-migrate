<?php

namespace carono\yii2migrate;


use carono\yii2migrate\helpers\SchemaHelper;
use yii\db\ColumnSchemaBuilder;

/**
 * Class ForeignKeyColumn
 *
 * @package carono\yii2installer
 */
class ForeignKeyColumn extends ColumnSchemaBuilder
{
    const FK_CASCADE = 'CASCADE';
    const FK_DEFAULT = 'SET DEFAULT';
    const FK_NULL = 'SET NULL';
    const FK_RESTRICT = 'RESTRICT';
    const FK_NO_ACTION = 'NO ACTION';

    protected $_onDelete = self::FK_CASCADE;
    protected $_onUpdate;
    protected $_refTable;
    protected $_refColumn;
    protected $_sourceTable;
    protected $_sourceColumn;
    protected $_name;
    /**
     * @var Migration
     */
    public $migrate;

    /**
     * @param $migrate
     * @return $this
     */
    public function setMigrate($migrate)
    {
        $this->migrate = $migrate;
        $this->db = $migrate->db;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setName($value)
    {
        $this->_name = $value;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->_name;
    }

    public function formIndexName()
    {
        if ($this->_name) {
            $name = $this->_name;
        } else {
            $name = $this->formName($this->getSourceTable(), $this->getSourceColumn(), $this->getRefTable(), $this->getRefColumn());
        }
        $name = $this->migrate->expandTablePrefix($name);
        return $name;
    }

    public function apply()
    {
        $this->migrate->addForeignKey(
            $this->getName(), $this->getSourceTable(), $this->getSourceColumn(), $this->getRefTable(),
            $this->getRefColumn(), $this->getOnDelete(), $this->getOnUpdate()
        );
    }

    public function remove()
    {
        $this->migrate->dropForeignKeyByColumn($this->getSourceTable(), $this->getSourceColumn());
    }

    /**
     * @return null|string
     */
    public function getRefTable()
    {
        return $this->_refTable;
    }

    protected function getRefColumnByTable($table)
    {
        return current($this->migrate->db->getTableSchema($table)->primaryKey);
    }

    /**
     * @return null|string
     */
    public function getRefColumn()
    {
        if (!$this->_refColumn && $this->migrate) {
            $this->_refColumn = $this->getRefColumnByTable($this->getRefTable());
        }
        return $this->_refColumn;
    }

    /**
     * @return null|string
     */
    public function getSourceTable()
    {
        return $this->_sourceTable;
    }

    /**
     * @return null|string
     */
    public function getSourceColumn()
    {
        return $this->_sourceColumn;
    }

    /**
     * @return string
     */
    public function getOnDelete()
    {
        return $this->_onDelete;
    }

    /**
     * @return null|string
     */
    public function getOnUpdate()
    {
        return $this->_onUpdate;
    }

    /**
     * @param $string
     * @return $this
     */
    public function onDelete($string)
    {
        $this->_onDelete = $string;
        return $this;
    }

    /**
     * @param $string
     * @return $this
     */
    public function onUpdate($string)
    {
        $this->_onUpdate = $string;
        return $this;
    }

    /**
     * @return ForeignKeyColumn
     */
    public function onDeleteCascade()
    {
        return $this->onDelete(self::FK_CASCADE);
    }

    /**
     * @return ForeignKeyColumn
     */
    public function onDeleteNull()
    {
        return $this->onDelete(self::FK_NULL);
    }

    /**
     * @return ForeignKeyColumn
     */
    public function onDeleteDefault()
    {
        return $this->onDelete(self::FK_DEFAULT);
    }

    /**
     * @return ForeignKeyColumn
     */
    public function onDeleteRestrict()
    {
        return $this->onDelete(self::FK_RESTRICT);
    }

    /**
     * @return ForeignKeyColumn
     */
    public function onDeleteNoAction()
    {
        return $this->onDelete(self::FK_NO_ACTION);
    }

    /**
     * @return ForeignKeyColumn
     */
    public function onUpdateCascade()
    {
        return $this->onUpdate(self::FK_CASCADE);
    }

    /**
     * @return ForeignKeyColumn
     */
    public function onUpdateNull()
    {
        return $this->onUpdate(self::FK_NULL);
    }

    /**
     * @return ForeignKeyColumn
     */
    public function onUpdateDefault()
    {
        return $this->onUpdate(self::FK_DEFAULT);
    }

    /**
     * @return ForeignKeyColumn
     */
    public function onUpdateRestrict()
    {
        return $this->onUpdate(self::FK_RESTRICT);
    }

    /**
     * @return ForeignKeyColumn
     */
    public function onUpdateNoAction()
    {
        return $this->onUpdate(self::FK_NO_ACTION);
    }

    /**
     * @param $name
     * @return $this
     */
    public function refTable($name)
    {
        $this->_refTable = $name;
        return $this;
    }

    /**
     * @param $name
     * @return $this
     */
    public function sourceColumn($name)
    {
        $this->_sourceColumn = $name;
        return $this;
    }

    /**
     * @param $table
     * @param $column
     * @param $refTable
     * @param $refColumn
     * @return string
     */
    public function formName($table, $column, $refTable, $refColumn)
    {
        $table = SchemaHelper::removeSchema($this->migrate->expandTablePrefix($table));
        $refTable = SchemaHelper::removeSchema($this->migrate->expandTablePrefix($refTable));
        return "{$table}[{$column}]_{$refTable}[{$refColumn}]_fk";
    }

    /**
     * @param $name
     *
     * @return $this
     */
    public function sourceTable($name)
    {
        $this->_sourceTable = $name;
        return $this;
    }

    /**
     * @param $name
     * @return $this
     */
    public function refColumn($name)
    {
        $this->_refColumn = $name;
        return $this;
    }
}