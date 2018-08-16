<?php
$config = require __DIR__ . '/test.php';
$config['components']['db'] = require __DIR__ . '/db-mysql.php';
return $config;