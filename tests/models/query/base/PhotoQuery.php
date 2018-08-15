<?php

/**
 * This class is generated using the package carono/codegen
 */

namespace tests\yii2migrate\models\query\base;

use yii\data\ActiveDataProvider;
use yii\data\Sort;
use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for \tests\yii2migrate\models\Photo
 * @see \tests\yii2migrate\models\Photo
 */
class PhotoQuery extends ActiveQuery
{
	/**
	 * @inheritdoc
	 * @return \tests\yii2migrate\models\Photo[]
	 */
	public function all($db = null)
	{
		return parent::all($db);
	}


	/**
	 * @inheritdoc
	 * @return \tests\yii2migrate\models\Photo
	 */
	public function one($db = null)
	{
		return parent::one($db);
	}


	/**
	 * @var mixed $filter
	 * @var array $options Options for ActiveDataProvider
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
		        'sort'  => $sort
		    ], $options)
		);
	}


	/**
	 * @var array|\yii\db\ActiveRecord $model
	 * @return $this
	 */
	public function filter($model = null)
	{
		return $this;
	}
}
