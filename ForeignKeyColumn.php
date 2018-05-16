<?php

namespace carono\yii2migrate;


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
    protected $_onDelete = self::FK_CASCADE;
    protected $_onUpdate = null;
    protected $_refTable = null;
    protected $_refColumn = null;
    protected $_sourceTable = null;
    protected $_sourceColumn = null;
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
        if ($this->_name) {
            $name = $this->_name;
        } else {
            $name = self::formName($this->getSourceTable(), $this->getSourceColumn(), $this->getRefTable(), $this->getRefColumn());
        }
        $name = $this->migrate->expandTablePrefix($name);
        return $name;
    }

    public function apply()
    {
        return $this->migrate->addForeignKey(
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

    /**
     * @return null|string
     */
    public function getRefColumn()
    {
        if (!$this->_refColumn && $this->migrate) {
            $pk = $this->migrate->db->getTableSchema($this->getRefTable())->primaryKey;
            $this->refColumn(current($pk));
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
     * @param $str
     * @return mixed
     */
    private static function removeSchema($str)
    {
        if (strpos($str, '.') !== false) {
            $arr = explode('.', $str);
            return $arr[1];
        }

        return $str;
    }

    /**
     * @param $table
     * @param $column
     * @param $refTable
     * @param $refColumn
     * @return string
     */
    public static function formName($table, $column, $refTable, $refColumn)
    {
        $table = self::removeSchema($table);
        $refTable = self::removeSchema($refTable);
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