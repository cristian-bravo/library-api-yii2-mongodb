<?php

declare(strict_types=1);

namespace tests\Unit;

use app\modules\api\dto\AuthorCreateDto;
use app\modules\api\dto\BookCreateDto;
use app\modules\api\dto\BookUpdateDto;
use app\modules\api\exceptions\DomainException;
use app\modules\api\services\AuthorService;
use app\modules\api\services\BookService;
use tests\Support\IntegrationTestCase;
use Yii;

final class BookServiceTest extends IntegrationTestCase
{
    public function testCreateAndGetBookUsingService(): void
    {
        $authorService = new AuthorService();
        $bookService = new BookService();

        $author = $authorService->create(new AuthorCreateDto('Unit Author', '1975-04-20', []));
        $authorId = (string) ($author['id'] ?? '');

        $created = $bookService->create(new BookCreateDto(
            'Unit Book',
            [$authorId],
            2021,
            'Unit test description'
        ));

        self::assertArrayHasKey('id', $created);
        self::assertSame('Unit Book', $created['title'] ?? null);

        $loaded = $bookService->getById((string) $created['id']);
        self::assertSame($created['id'], $loaded['id'] ?? null);
        self::assertSame(200, Yii::$app->response->statusCode);
    }

    public function testUpdateWithoutChangesThrowsDomainException(): void
    {
        $this->expectException(DomainException::class);

        $authorService = new AuthorService();
        $bookService = new BookService();

        $author = $authorService->create(new AuthorCreateDto('Unit Author 2', '1988-07-11', []));
        $created = $bookService->create(new BookCreateDto(
            'Unit Book 2',
            [(string) $author['id']],
            2022,
            null
        ));

        $bookService->update((string) $created['id'], new BookUpdateDto(null, null, null, null));
    }
}
