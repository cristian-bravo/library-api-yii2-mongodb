<?php

declare(strict_types=1);

namespace tests\Api;

use tests\Support\IntegrationTestCase;
use Yii;

final class AuthorTest extends IntegrationTestCase
{
    public function testCreateAndGetAuthor(): void
    {
        $token = $this->loginAndGetToken();

        $createResponse = $this->request(
            'api/author/create',
            'POST',
            [
                'full_name' => 'Author Test Name',
                'birth_date' => '1990-05-17',
                'books' => [],
            ],
            [],
            $token
        );

        self::assertSame(201, Yii::$app->response->statusCode);
        self::assertSame('success', $createResponse['status'] ?? null);

        $authorId = (string) ($createResponse['data']['id'] ?? '');
        self::assertNotSame('', $authorId);

        $viewResponse = $this->request(
            'api/author/view',
            'GET',
            [],
            [],
            $token,
            ['id' => $authorId]
        );

        self::assertSame(200, Yii::$app->response->statusCode);
        self::assertSame($authorId, $viewResponse['data']['id'] ?? null);
        self::assertSame('Author Test Name', $viewResponse['data']['full_name'] ?? null);
    }
}
