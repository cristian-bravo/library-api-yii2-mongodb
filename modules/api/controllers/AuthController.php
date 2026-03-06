<?php

declare(strict_types=1);

namespace app\modules\api\controllers;

use app\commons\constants\HttpStatus;
use app\modules\api\requests\LoginRequest;
use app\modules\api\responses\ApiResponse;
use app\modules\api\services\AuthService;
use Yii;

final class AuthController extends ApiController
{
    private ?AuthService $authService = null;

    public function verbs(): array
    {
        return [
            'login' => ['POST', 'OPTIONS'],
            'options' => ['OPTIONS'],
        ];
    }

    protected function publicActions(): array
    {
        return ['login'];
    }

    public function actionLogin(): array
    {
        $request = LoginRequest::fromPayload((array) Yii::$app->request->getBodyParams());
        $tokenData = $this->service()->login($request->toDto());

        return ApiResponse::success($tokenData, [], HttpStatus::OK);
    }

    private function service(): AuthService
    {
        return $this->authService ??= new AuthService();
    }
}
