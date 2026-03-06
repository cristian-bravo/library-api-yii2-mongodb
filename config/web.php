<?php

declare(strict_types=1);

use yii\rest\UrlRule;
use yii\web\JsonParser;
use yii\web\Response;

return [
    'id' => 'library-api',
    'basePath' => dirname(__DIR__),
    'modules' => [
        'api' => [
            'class' => app\modules\api\Module::class,
        ],
    ],
    'components' => [
        'request' => [
            'cookieValidationKey' => getenv('COOKIE_VALIDATION_KEY') ?: 'change-me-library-api',
            'parsers' => [
                'application/json' => JsonParser::class,
            ],
        ],
        'mongodb' => require __DIR__ . '/mongodb.php',
        'user' => [
            'identityClass' => app\models\User::class,
            'enableAutoLogin' => false,
            'enableSession' => false,
            'loginUrl' => null,
        ],
        'errorHandler' => [
            'errorAction' => null,
        ],
        'response' => [
            'class' => Response::class,
            'format' => Response::FORMAT_JSON,
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                [
                    'class' => UrlRule::class,
                    'controller' => ['api/book', 'api/author'],
                    'pluralize' => true,
                    'tokens' => [
                        '{id}' => '<id:[a-fA-F0-9]{24}>',
                    ],
                ],
                'POST api/login' => 'api/auth/login',
            ],
        ],
    ],
    'params' => require __DIR__ . '/api.php',
];
