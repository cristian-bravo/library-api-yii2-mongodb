<?php

declare(strict_types=1);

namespace app\modules\api\dto;

final class BookCreateDto
{
    /**
     * @param array<int, string> $authors
     */
    public function __construct(
        public readonly string $title,
        public readonly array $authors,
        public readonly int $publicationYear,
        public readonly ?string $description
    ) {
    }
}
