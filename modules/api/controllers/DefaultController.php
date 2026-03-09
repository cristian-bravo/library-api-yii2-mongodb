<?php

declare(strict_types=1);

namespace app\modules\api\controllers;

use app\modules\api\responses\ApiResponse;
use yii\helpers\Url;

final class DefaultController extends ApiController
{
    public function verbs(): array
    {
        return [
            'index' => ['GET', 'OPTIONS'],
            'options' => ['OPTIONS'],
        ];
    }

    protected function publicActions(): array
    {
        return ['index'];
    }

    public function actionIndex(): array
    {
        return ApiResponse::success([
            'name' => 'Library API',
            'version' => '2.0.0',
            'documentation' => [
                'swagger_ui' => Url::to(['/docs/index'], true),
                'openapi_yaml' => Url::to(['/docs/openapi'], true),
            ],
            'endpoints' => [
                'login' => Url::to(['/api/auth/login'], true),
                'books' => Url::to(['/api/book/index'], true),
                'authors' => Url::to(['/api/author/index'], true),
            ],
        ]);
    }
}
