<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

if (class_exists(\Dotenv\Dotenv::class) && file_exists(dirname(__DIR__) . '/.env')) {
    \Dotenv\Dotenv::createImmutable(dirname(__DIR__))->safeLoad();
}

$appDebug = $_ENV['APP_DEBUG'] ?? $_SERVER['APP_DEBUG'] ?? getenv('APP_DEBUG');
$appEnv = $_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? getenv('APP_ENV');

defined('YII_DEBUG') or define('YII_DEBUG', filter_var($appDebug ?: 'true', FILTER_VALIDATE_BOOL));
defined('YII_ENV') or define('YII_ENV', $appEnv ?: 'dev');

// Ignore driver deprecations from ext-mongodb 1.x/2.x compatibility mismatches in yii2-mongodb.
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);

require dirname(__DIR__) . '/vendor/yiisoft/yii2/Yii.php';

$config = require dirname(__DIR__) . '/config/web.php';

(new yii\web\Application($config))->run();
