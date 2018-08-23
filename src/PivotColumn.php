<?php

namespace carono\yii2migrate;

use carono\yii2migrate\helpers\SchemaHelper;
use yii\helpers\Inflector;

/**
 * Class PivotColumn
 *
 * @package carono\yii2installer
 */
class PivotColumn
{
    protected $_refTable;
    protected $_refColumn;
    protected $_sourceTable;
    protected $_sourceColumn;
    protected $_suffix;
    protected $_tableName;
    protected $_columns = [];
    protected $_prefix = 'pv';
    /**
     * @var Migration
     */
    public $migrate;

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getPrefix();
    }

    public function setPrefix($value)
    {
        $this->_prefix = $value;
    }

    public function getPrefix()
    {
        return $this->_prefix;
    }

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
    public function setSuffix($value)
    {
        $this->_suffix = $value;
        return $this;
    }

    /**
     * @param $name
     * @return $this
     */
    public function tableName($name)
    {
        $this->_tableName = $name;
        return $this;
    }

    /**
     * @param array $columns
     * @return $this
     */
    public function columns($columns)
    {
        $this->_columns = $columns;
        return $this;
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        return $this->_columns;
    }

    /**
     * @return mixed
     */
    public function getTableName()
    {
        $name = $this->_tableName ?: implode('_', [$this->getPrefix(), $this->_sourceTable, $this->_suffix]);
        return '{{%' . SchemaHelper::expandTablePrefix($name, '') . '}}';
    }

    /**
     * @return mixed
     */
    public function getSuffix()
    {
        return $this->_suffix;
    }

    public function remove()
    {
        if ($this->_columns) {
            $this->migrate->downNewColumns([$this->getTableName() => $this->_columns]);
        }
        $this->migrate->dropTable($this->getTableName());
    }

    public function apply()
    {
        /**
         * @var ForeignKeyColumn $type
         */
        $columns = [
            $this->getSourceColumn() => $this->migrate->foreignKey($this->getSourceTable()),
            $this->getRefColumn() => $this->migrate->foreignKey($this->getRefTable()),
        ];
        $columnsInt = array_combine(array_keys($columns), [$this->migrate->integer(), $this->migrate->integer()]);

        $this->migrate->createTable($this->getTableName(), $columnsInt);
        $this->migrate->addPrimaryKey(null, $this->getTableName(), array_keys($columns));

        foreach ($columns as $name => $type) {
            $type->sourceTable($this->getTableName())->sourceColumn($name);
            $type->apply();
        }
        if ($advancedColumns = $this->getColumns()) {
            $this->migrate->upNewColumns([$this->getTableName() => $this->getColumns()]);
        }
    }

    /**
     * @return null
     */
    public function getRefTable()
    {
        return $this->_refTable;
    }

    /**
     * @return mixed
     */
    public function getRefColumn()
    {
        if (!$this->_refColumn) {
            $name = implode('_', [$this->getRefTable(), 'id']);
        } else {
            $name = $this->_refColumn;
        }
        $refColumn = SchemaHelper::expandTablePrefix($name, '');
        if (strtolower($refColumn) === strtolower($this->getSourceColumn())) {
            $refColumn = Inflector::singularize($this->_suffix) . '_id';
        }
        return $refColumn;
    }

    /**
     * @return null
     */
    public function getSourceTable()
    {
        return $this->_sourceTable;
    }

    /**
     * @return mixed
     */
    public function getSourceColumn()
    {
        if (!$this->_sourceColumn) {
            $name = implode('_', [$this->getSourceTable(), 'id']);
        } else {
            $name = $this->_sourceColumn;
        }
        return SchemaHelper::expandTablePrefix($name, '');
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
     * @param $name
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