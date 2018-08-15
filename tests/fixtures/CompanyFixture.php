<?php

namespace tests\yii2migrate\fixtures;

use tests\yii2migrate\models\Company;
use yii\test\ActiveFixture;

class CompanyFixture extends ActiveFixture
{
    public $modelClass = Company::class;

    public $depends = [
        UserFixture::class
    ];
}
