<?php

namespace carono\yii2migrate;

/**
 * Class PivotColumn
 *
 * @package carono\yii2installer
 */
class PivotColumn
{
    protected $_refTable = null;
    protected $_refColumn = null;
    protected $_sourceTable = null;
    protected $_sourceColumn = null;
    protected $_name = null;
    protected $_tableName = null;
    protected $_columns = [];
    /**
     * @var Migration
     */
    public $migrate;

    /**
     * @return string
     */
    public function __toString()
    {
        return 'pv';
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
     * @param $name
     * @return $this
     */
    public function setName($name)
    {
        $this->_name = $name;
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
     * @return mixed
     */
    public function getName()
    {
        $name = $this->_tableName ? $this->_tableName : join('_', ["pv", $this->_sourceTable, $this->_name]);
        return "{{%" . Migration::setTablePrefix($name, '') . "}}";
    }

    public function remove()
    {
        if ($this->_columns) {
            $this->migrate->downNewColumns([$this->getName() => $this->_columns]);
        }
        $this->migrate->dropTable($this->getName());
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
        if ($this->migrate->db->driverName == "mysql") {
            $this->migrate->createTable($this->getName(), $columnsInt);
            $this->migrate->addPrimaryKey(null, $this->getName(), array_keys($columns));
            foreach ($columns as $name => $type) {
                $type->sourceTable($this->getName())->sourceColumn($name);
                $type->apply();
            }
        } else {
            $this->migrate->createTable($this->getName(), $columns);
            $this->migrate->addPrimaryKey(null, $this->getName(), array_keys($columns));
        }
        if ($this->_columns) {
            $this->migrate->upNewColumns([$this->getName() => $this->_columns]);
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
            $name = join("_", [$this->getRefTable(), "id"]);
        } else {
            $name = $this->_refColumn;
        }
        return Migration::setTablePrefix($name, '');
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
            $name = join("_", [$this->getSourceTable(), "id"]);
        } else {
            $name = $this->_sourceColumn;
        }
        return Migration::setTablePrefix($name, '');
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