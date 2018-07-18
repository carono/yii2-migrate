<?php


namespace carono\yii2migrate;


class IndexColumn
{
    /**
     * @var Migration
     */
    public $migrate;
    protected $_unique;
    protected $_name;
    protected $_columns;
    protected $_table;

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
     * @param bool $value
     * @return $this
     */
    public function unique($value = true)
    {
        $this->_unique = $value;
        return $this;
    }

    /**
     * @param $name
     * @return $this
     */
    public function name($name)
    {
        $this->_name = $name;
        return $this;
    }

    /**
     * @param array $columns
     * @return IndexColumn
     */
    public function columns($columns)
    {
        $this->_columns = $columns;
        return $this;
    }

    /**
     * @param $table
     * @return $this
     */
    public function table($table)
    {
        $this->_table = $table;
        return $this;
    }

    public function formIndexName()
    {
        $unique = $this->_unique;
        $columns = $this->_columns;
        if (!$this->_name) {
            $indexName = Migration::formIndexName($this->_table, $columns, $unique ? 'unq' : 'idx');
            $name = $this->migrate->expandTablePrefix($indexName);
        } else {
            $name = $this->_name;
        }
        return $name;
    }

    public function apply()
    {
        $this->migrate->createIndex($this->formIndexName(), $this->_table, $this->_columns, $this->_unique);
    }

    public function remove()
    {
        $this->migrate->dropIndex($this->formIndexName(), $this->_table);
    }
}