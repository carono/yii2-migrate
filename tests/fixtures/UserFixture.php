<?php

namespace tests\yii2migrate\fixtures;

use tests\yii2migrate\models\User;
use yii\test\ActiveFixture;

class UserFixture extends ActiveFixture
{
    public $modelClass = User::class;
}
