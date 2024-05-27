<?php

/**
 * This class is generated using the package carono/codegen.
 */

namespace app\models\query\base;

use yii\data\ActiveDataProvider;
use yii\data\Sort;
use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for \tests\models\Employee.
 *
 * @see \tests\models\Employee
 */
class EmployeeQuery extends ActiveQuery
{
    /**
     * @inheritdoc
     *
     * @return \tests\models\Employee[]
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     *
     * @return \tests\models\Employee
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    /**
     * @var mixed
     * @var array Options for ActiveDataProvider
     *
     * @return ActiveDataProvider
     */
    public function search($filter = null, $options = [])
    {
        $query = clone $this;
        $query->filter($filter);
        $sort = new Sort();

        return new ActiveDataProvider(
            array_merge([
                'query' => $query,
                'sort'  => $sort,
            ], $options)
        );
    }

    /**
     * @var array|\yii\db\ActiveRecord
     *
     * @return $this
     */
    public function filter($model = null)
    {
        return $this;
    }
}
