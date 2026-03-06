<?php

declare(strict_types=1);

namespace app\modules\api\dto;

final class AuthorCreateDto
{
    /**
     * @param array<int, string> $books
     */
    public function __construct(
        public readonly string $fullName,
        public readonly string $birthDate,
        public readonly array $books
    ) {
    }
}
