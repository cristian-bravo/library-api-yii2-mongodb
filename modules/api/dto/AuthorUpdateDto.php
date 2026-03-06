<?php

declare(strict_types=1);

namespace app\modules\api\dto;

final class AuthorUpdateDto
{
    /**
     * @param array<int, string>|null $books
     */
    public function __construct(
        public readonly ?string $fullName,
        public readonly ?string $birthDate,
        public readonly ?array $books
    ) {
    }

    public function hasChanges(): bool
    {
        return $this->fullName !== null
            || $this->birthDate !== null
            || $this->books !== null;
    }
}
