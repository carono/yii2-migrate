<?php

/**
 * This class is generated using the package carono/codegen
 */

namespace tests\yii2migrate\models\base;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the base-model class for table "employees".
 *
 * @property integer $company_id
 * @property integer $user_id
 *
 * @property \tests\yii2migrate\models\Company $company
 * @property \tests\yii2migrate\models\User $user
 */
class Employee extends ActiveRecord
{
	protected $_relationClasses = ['company_id' => 'tests\yii2migrate\models\Company', 'user_id' => 'tests\yii2migrate\models\User'];


	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%employees}}';
	}


	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
		            [['company_id', 'user_id'], 'required'],
		            [['company_id', 'user_id'], 'integer'],
		            [['company_id', 'user_id'], 'unique', 'targetAttribute' => ['company_id', 'user_id']],
		            [['company_id'], 'exist', 'skipOnError' => true, 'targetClass' => \tests\yii2migrate\models\Company::className(), 'targetAttribute' => ['company_id' => 'id']],
		            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => \tests\yii2migrate\models\User::className(), 'targetAttribute' => ['user_id' => 'id']],
		        ];
	}


	/**
	 * @inheritdoc
	 * @return \tests\yii2migrate\models\Employee|\yii\db\ActiveRecord
	 */
	public static function findOne($condition, $raise = false)
	{
		$model = parent::findOne($condition);
		if (!$model && $raise){
		    throw new \yii\web\HttpException(404, Yii::t('errors', "Model tests\\yii2migrate\\models\\Employee not found"));
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
		    'company_id' => Yii::t('models', 'Company ID'),
		    'user_id' => Yii::t('models', 'User ID')
		];
	}


	/**
	 * @inheritdoc
	 * @return \tests\yii2migrate\models\query\EmployeeQuery the active query used by this AR class.
	 */
	public static function find()
	{
		return new \tests\yii2migrate\models\query\EmployeeQuery(get_called_class());
	}


	/**
	 * @return \tests\yii2migrate\models\query\CompanyQuery|\yii\db\ActiveQuery
	 */
	public function getCompany()
	{
		return $this->hasOne(\tests\yii2migrate\models\Company::className(), ['id' => 'company_id']);
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
