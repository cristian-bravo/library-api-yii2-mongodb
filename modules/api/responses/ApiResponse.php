<?php

declare(strict_types=1);

namespace app\modules\api\responses;

use yii\web\Response;
use Yii;

final class ApiResponse
{
    /**
     * @param array<string, mixed> $meta
     * @return array<string, mixed>
     */
    public static function success(mixed $data, array $meta = [], int $statusCode = 200): array
    {
        Yii::$app->response->setStatusCode($statusCode);

        $response = [
            'status' => 'success',
            'data' => $data,
        ];

        if ($meta !== []) {
            $response['meta'] = $meta;
        }

        return $response;
    }

    /**
     * @param array<string, mixed> $details
     * @return array<string, mixed>
     */
    public static function error(
        string $code,
        string $message,
        array $details = [],
        int $statusCode = 400
    ): array {
        Yii::$app->response->setStatusCode($statusCode);

        $payload = [
            'status' => 'error',
            'error' => [
                'code' => $code,
                'message' => $message,
            ],
        ];

        if ($details !== []) {
            $payload['error']['details'] = $details;
        }

        return $payload;
    }

    public static function ensureJsonFormat(): void
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
    }
}
