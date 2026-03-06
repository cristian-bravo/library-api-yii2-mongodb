<?php

declare(strict_types=1);

return [
    'id' => 'library-api-console',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'yii\console\controllers',
    'controllerMap' => [
        'mongodb-migrate' => [
            'class' => yii\mongodb\console\controllers\MigrateController::class,
            'migrationPath' => '@app/migrations/mongodb',
            'migrationCollection' => 'migration',
        ],
    ],
    'bootstrap' => [],
    'components' => [
        'mongodb' => require __DIR__ . '/mongodb.php',
    ],
    'params' => require __DIR__ . '/api.php',
];
