<?php

/**
 * This class is generated using the package carono/codegen
 */

namespace tests\yii2migrate\models\base;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the base-model class for table "pv_company_directors".
 *
 * @property integer $company_id
 * @property integer $director_id
 * @property string $hire_at
 * @property integer $hired_id
 *
 * @property \tests\yii2migrate\models\Company $company
 * @property \tests\yii2migrate\models\User $director
 * @property \tests\yii2migrate\models\User $hired
 */
class PvCompanyDirector extends ActiveRecord
{
	protected $_relationClasses = [
		'company_id' => 'tests\yii2migrate\models\Company',
		'director_id' => 'tests\yii2migrate\models\User',
		'hired_id' => 'tests\yii2migrate\models\User',
	];


	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%pv_company_directors}}';
	}


	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
		            [['company_id', 'director_id'], 'required'],
		            [['company_id', 'director_id', 'hired_id'], 'integer'],
		            [['hire_at'], 'safe'],
		            [['company_id', 'director_id'], 'unique', 'targetAttribute' => ['company_id', 'director_id']],
		            [['company_id'], 'exist', 'skipOnError' => true, 'targetClass' => \tests\yii2migrate\models\Company::className(), 'targetAttribute' => ['company_id' => 'id']],
		            [['director_id'], 'exist', 'skipOnError' => true, 'targetClass' => \tests\yii2migrate\models\User::className(), 'targetAttribute' => ['director_id' => 'id']],
		            [['hired_id'], 'exist', 'skipOnError' => true, 'targetClass' => \tests\yii2migrate\models\User::className(), 'targetAttribute' => ['hired_id' => 'id']],
		        ];
	}


	/**
	 * @inheritdoc
	 * @return \tests\yii2migrate\models\PvCompanyDirector|\yii\db\ActiveRecord
	 */
	public static function findOne($condition, $raise = false)
	{
		$model = parent::findOne($condition);
		if (!$model && $raise){
		    throw new \yii\web\HttpException(404, Yii::t('errors', "Model tests\\yii2migrate\\models\\PvCompanyDirector not found"));
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
		    'director_id' => Yii::t('models', 'Director ID'),
		    'hire_at' => Yii::t('models', 'Hire At'),
		    'hired_id' => Yii::t('models', 'Hired ID')
		];
	}


	/**
	 * @inheritdoc
	 * @return \tests\yii2migrate\models\query\PvCompanyDirectorQuery the active query used by this AR class.
	 */
	public static function find()
	{
		return new \tests\yii2migrate\models\query\PvCompanyDirectorQuery(get_called_class());
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
	public function getDirector()
	{
		return $this->hasOne(\tests\yii2migrate\models\User::className(), ['id' => 'director_id']);
	}


	/**
	 * @return \tests\yii2migrate\models\query\UserQuery|\yii\db\ActiveQuery
	 */
	public function getHired()
	{
		return $this->hasOne(\tests\yii2migrate\models\User::className(), ['id' => 'hired_id']);
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
