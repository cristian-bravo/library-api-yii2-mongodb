<?php

declare(strict_types=1);

namespace tests\Unit;

use app\modules\api\dto\AuthorCreateDto;
use app\modules\api\dto\AuthorUpdateDto;
use app\modules\api\exceptions\DomainException;
use app\modules\api\services\AuthorService;
use tests\Support\IntegrationTestCase;
use Yii;

final class AuthorServiceTest extends IntegrationTestCase
{
    public function testCreateAndGetAuthorUsingService(): void
    {
        $authorService = new AuthorService();

        $created = $authorService->create(new AuthorCreateDto(
            'Unit Service Author',
            '1991-02-13',
            []
        ));

        self::assertArrayHasKey('id', $created);
        self::assertSame('Unit Service Author', $created['full_name'] ?? null);

        $loaded = $authorService->getById((string) $created['id']);
        self::assertSame($created['id'], $loaded['id'] ?? null);
        self::assertSame(200, Yii::$app->response->statusCode);
    }

    public function testUpdateWithoutChangesThrowsDomainException(): void
    {
        $this->expectException(DomainException::class);

        $authorService = new AuthorService();
        $created = $authorService->create(new AuthorCreateDto(
            'Unit Service Author 2',
            '1982-11-30',
            []
        ));

        $authorService->update((string) $created['id'], new AuthorUpdateDto(null, null, null));
    }
}
