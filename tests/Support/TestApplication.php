<?php

declare(strict_types=1);

namespace tests\Support;

use Yii;
use yii\web\Application;

final class TestApplication
{
    private static ?Application $app = null;

    public static function boot(): Application
    {
        if (self::$app !== null) {
            return self::$app;
        }

        $config = require dirname(__DIR__, 2) . '/config/web.php';
        $config['id'] = 'library-api-test';
        $config['components']['request']['cookieValidationKey'] = $_ENV['COOKIE_VALIDATION_KEY'] ?? 'test-cookie-key';

        self::$app = new Application($config);

        return self::$app;
    }

    public static function app(): Application
    {
        return self::boot();
    }

    public static function resetResponse(): void
    {
        Yii::$app->response->statusCode = 200;
        Yii::$app->response->data = null;
    }
}
