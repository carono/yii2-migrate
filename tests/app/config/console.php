<?php

return [
    'id' => 'yii2-test-console',
    'basePath' => dirname(__DIR__),
    'aliases' => [
        '@tests' => dirname(dirname(__DIR__)),
        '@vendor' => dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'vendor',
    ],
    'bootstrap' => ['gii'],
    'components' => [
        'log' => null,
        'cache' => null,
        'db' => require __DIR__ . '/db-mysql.php',
        'db2' => require __DIR__ . '/db-pgsql.php'
    ],
    'modules' => [
        'gii' => [
            'class' => 'yii\gii\Module',
        ]
    ],
    'controllerMap' => [
        'giix' => [
            'class' => 'carono\giix\GiixController',
        ]
    ]
];