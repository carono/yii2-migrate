<?php

/**
 * This class is generated using the package carono/codegen
 */

namespace tests\yii2migrate\models\base;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the base-model class for table "pv_user_parents".
 *
 * @property integer $user_id
 * @property integer $parent_id
 *
 * @property \tests\yii2migrate\models\User $parent
 * @property \tests\yii2migrate\models\User $user
 */
class PvUserParent extends ActiveRecord
{
	protected $_relationClasses = ['parent_id' => 'tests\yii2migrate\models\User', 'user_id' => 'tests\yii2migrate\models\User'];


	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%pv_user_parents}}';
	}


	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
		            [['user_id', 'parent_id'], 'required'],
		            [['user_id', 'parent_id'], 'integer'],
		            [['user_id', 'parent_id'], 'unique', 'targetAttribute' => ['user_id', 'parent_id']],
		            [['parent_id'], 'exist', 'skipOnError' => true, 'targetClass' => \tests\yii2migrate\models\User::className(), 'targetAttribute' => ['parent_id' => 'id']],
		            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => \tests\yii2migrate\models\User::className(), 'targetAttribute' => ['user_id' => 'id']],
		        ];
	}


	/**
	 * @inheritdoc
	 * @return \tests\yii2migrate\models\PvUserParent|\yii\db\ActiveRecord
	 */
	public static function findOne($condition, $raise = false)
	{
		$model = parent::findOne($condition);
		if (!$model && $raise){
		    throw new \yii\web\HttpException(404, Yii::t('errors', "Model tests\\yii2migrate\\models\\PvUserParent not found"));
		}else{
		    return $model;
		}
	}


	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
		    'user_id' => Yii::t('models', 'User ID'),
		    'parent_id' => Yii::t('models', 'Parent ID')
		];
	}


	/**
	 * @inheritdoc
	 * @return \tests\yii2migrate\models\query\PvUserParentQuery the active query used by this AR class.
	 */
	public static function find()
	{
		return new \tests\yii2migrate\models\query\PvUserParentQuery(get_called_class());
	}


	/**
	 * @return \tests\yii2migrate\models\query\UserQuery|\yii\db\ActiveQuery
	 */
	public function getParent()
	{
		return $this->hasOne(\tests\yii2migrate\models\User::className(), ['id' => 'parent_id']);
	}


	/**
	 * @return \tests\yii2migrate\models\query\UserQuery|\yii\db\ActiveQuery
	 */
	public function getUser()
	{
		return $this->hasOne(\tests\yii2migrate\models\User::className(), ['id' => 'user_id']);
	}


	/**
	 * @param string $attribute
	 * @return string|null
	 */
	public function getRelationClass($attribute)
	{
		return ArrayHelper::getValue($this->_relationClasses, $attribute);
	}
}
