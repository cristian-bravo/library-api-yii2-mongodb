<?php

declare(strict_types=1);

namespace app\modules\api\controllers;

use app\modules\api\filters\ApiExceptionFilter;
use app\modules\api\filters\TokenAuthFilter;
use app\modules\api\responses\ApiResponse;
use yii\filters\Cors;
use yii\rest\Controller;
use yii\web\Response;
use Yii;

abstract class ApiController extends Controller
{
    public function behaviors(): array
    {
        $behaviors = parent::behaviors();

        unset($behaviors['authenticator'], $behaviors['rateLimiter']);
        $behaviors['contentNegotiator']['formats']['application/json'] = Response::FORMAT_JSON;

        $behaviors['exceptionFilter'] = [
            'class' => ApiExceptionFilter::class,
        ];

        $behaviors['corsFilter'] = [
            'class' => Cors::class,
            'cors' => [
                'Origin' => ['*'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
                'Access-Control-Allow-Headers' => ['Authorization', 'Content-Type'],
                'Access-Control-Max-Age' => 3600,
            ],
        ];

        $behaviors['authenticator'] = [
            'class' => TokenAuthFilter::class,
            'except' => array_values(array_unique(array_merge(['options'], $this->publicActions()))),
        ];

        return $behaviors;
    }

    public function beforeAction($action): bool
    {
        ApiResponse::ensureJsonFormat();
        return parent::beforeAction($action);
    }

    protected function publicActions(): array
    {
        return [];
    }

    public function actionOptions(?string $id = null): array
    {
        return [];
    }

    /**
     * @return array{page: int, per_page: int}
     */
    protected function resolvePagination(): array
    {
        $paginationConfig = (array) (Yii::$app->params['pagination'] ?? []);
        $defaultPerPage = (int) ($paginationConfig['defaultPerPage'] ?? 20);
        $maxPerPage = (int) ($paginationConfig['maxPerPage'] ?? 100);

        $page = max(1, (int) Yii::$app->request->get('page', 1));
        $perPage = max(1, (int) Yii::$app->request->get('per_page', $defaultPerPage));
        $perPage = min($maxPerPage, $perPage);

        return [
            'page' => $page,
            'per_page' => $perPage,
        ];
    }
}
