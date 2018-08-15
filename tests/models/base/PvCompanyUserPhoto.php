<?php

/**
 * This class is generated using the package carono/codegen
 */

namespace tests\yii2migrate\models\base;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the base-model class for table "pv_company_user_photo".
 *
 * @property integer $company_id
 * @property integer $user_id
 * @property integer $photo_id
 *
 * @property \tests\yii2migrate\models\Company $company
 * @property \tests\yii2migrate\models\Photo $photo
 * @property \tests\yii2migrate\models\User $user
 */
class PvCompanyUserPhoto extends ActiveRecord
{
	protected $_relationClasses = [
		'company_id' => 'tests\yii2migrate\models\Company',
		'photo_id' => 'tests\yii2migrate\models\Photo',
		'user_id' => 'tests\yii2migrate\models\User',
	];


	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%pv_company_user_photo}}';
	}


	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
		            [['company_id', 'user_id', 'photo_id'], 'required'],
		            [['company_id', 'user_id', 'photo_id'], 'integer'],
		            [['company_id', 'user_id', 'photo_id'], 'unique', 'targetAttribute' => ['company_id', 'user_id', 'photo_id']],
		            [['company_id'], 'exist', 'skipOnError' => true, 'targetClass' => \tests\yii2migrate\models\Company::className(), 'targetAttribute' => ['company_id' => 'id']],
		            [['photo_id'], 'exist', 'skipOnError' => true, 'targetClass' => \tests\yii2migrate\models\Photo::className(), 'targetAttribute' => ['photo_id' => 'id']],
		            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => \tests\yii2migrate\models\User::className(), 'targetAttribute' => ['user_id' => 'id']],
		        ];
	}


	/**
	 * @inheritdoc
	 * @return \tests\yii2migrate\models\PvCompanyUserPhoto|\yii\db\ActiveRecord
	 */
	public static function findOne($condition, $raise = false)
	{
		$model = parent::findOne($condition);
		if (!$model && $raise){
		    throw new \yii\web\HttpException(404, Yii::t('errors', "Model tests\\yii2migrate\\models\\PvCompanyUserPhoto not found"));
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
		    'user_id' => Yii::t('models', 'User ID'),
		    'photo_id' => Yii::t('models', 'Photo ID')
		];
	}


	/**
	 * @inheritdoc
	 * @return \tests\yii2migrate\models\query\PvCompanyUserPhotoQuery the active query used by this AR class.
	 */
	public static function find()
	{
		return new \tests\yii2migrate\models\query\PvCompanyUserPhotoQuery(get_called_class());
	}


	/**
	 * @return \tests\yii2migrate\models\query\CompanyQuery|\yii\db\ActiveQuery
	 */
	public function getCompany()
	{
		return $this->hasOne(\tests\yii2migrate\models\Company::className(), ['id' => 'company_id']);
	}


	/**
	 * @return \tests\yii2migrate\models\query\PhotoQuery|\yii\db\ActiveQuery
	 */
	public function getPhoto()
	{
		return $this->hasOne(\tests\yii2migrate\models\Photo::className(), ['id' => 'photo_id']);
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
