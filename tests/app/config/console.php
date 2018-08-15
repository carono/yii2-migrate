<?php

return [
    'id' => 'yii2-test-console',
    'basePath' => dirname(__DIR__),
    'aliases' => [
        '@tests/yii2migrate' => dirname(dirname(__DIR__)),
        '@vendor' => dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'vendor',
    ],
    'bootstrap' => ['gii'],
    'components' => [
        'log' => null,
        'cache' => null,
        'db' => require __DIR__ . '/db-mysql.php'
    ],
    'modules' => [
        'gii' => [
            'class' => 'yii\gii\Module',
        ]
    ],
    'controllerMap' => [
        'giix' => [
            'class' => 'carono\giix\GiixController',
            'modelNamespace' => 'tests\yii2migrate\models',
            'modelQueryNamespace' => 'tests\yii2migrate\models\query'
        ],
        'fixture' => [
            'class' => \yii\console\controllers\FixtureController::class,
            'namespace' => 'tests\yii2migrate\fixtures'
        ]
    ]
];