<?php

declare(strict_types=1);

namespace tests\Api;

use tests\Support\IntegrationTestCase;
use Yii;

final class AuthTest extends IntegrationTestCase
{
    public function testLoginReturnsToken(): void
    {
        $response = $this->request(
            'api/auth/login',
            'POST',
            [
                'username' => 'admin',
                'password' => 'Admin123!',
            ]
        );

        self::assertSame(200, Yii::$app->response->statusCode);
        self::assertSame('success', $response['status'] ?? null);
        self::assertArrayHasKey('token', $response['data'] ?? []);
        self::assertNotEmpty($response['data']['token']);
        self::assertSame(1800, $response['data']['expires_in'] ?? null);
    }

    public function testProtectedEndpointWithoutTokenReturnsUnauthorized(): void
    {
        $response = $this->request('api/book/index', 'GET');

        self::assertSame(401, Yii::$app->response->statusCode);
        self::assertSame('error', $response['status'] ?? null);
        self::assertSame('UNAUTHORIZED', $response['error']['code'] ?? null);
    }
}
