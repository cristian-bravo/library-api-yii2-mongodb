<?php

declare(strict_types=1);

namespace tests\Api;

use tests\Support\IntegrationTestCase;
use Yii;

final class BookTest extends IntegrationTestCase
{
    public function testCreateGetDeleteBookFlow(): void
    {
        $token = $this->loginAndGetToken();

        $authorResponse = $this->request(
            'api/author/create',
            'POST',
            [
                'full_name' => 'Book Test Author',
                'birth_date' => '1980-01-01',
                'books' => [],
            ],
            [],
            $token
        );

        self::assertSame(201, Yii::$app->response->statusCode);
        $authorId = (string) ($authorResponse['data']['id'] ?? '');
        self::assertNotSame('', $authorId);

        $createResponse = $this->request(
            'api/book/create',
            'POST',
            [
                'title' => 'Book Test Title',
                'authors' => [$authorId],
                'publication_year' => 2020,
                'description' => 'Book test description',
            ],
            [],
            $token
        );

        self::assertSame(201, Yii::$app->response->statusCode);
        self::assertSame('success', $createResponse['status'] ?? null);
        $bookId = (string) ($createResponse['data']['id'] ?? '');
        self::assertNotSame('', $bookId);

        $viewResponse = $this->request(
            'api/book/view',
            'GET',
            [],
            [],
            $token,
            ['id' => $bookId]
        );

        self::assertSame(200, Yii::$app->response->statusCode);
        self::assertSame($bookId, $viewResponse['data']['id'] ?? null);

        $deleteResponse = $this->request(
            'api/book/delete',
            'DELETE',
            [],
            [],
            $token,
            ['id' => $bookId]
        );

        self::assertSame(200, Yii::$app->response->statusCode);
        self::assertSame('success', $deleteResponse['status'] ?? null);
    }
}
