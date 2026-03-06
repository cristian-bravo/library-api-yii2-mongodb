<?php

declare(strict_types=1);

namespace app\modules\api\dto;

final class BookUpdateDto
{
    /**
     * @param array<int, string>|null $authors
     */
    public function __construct(
        public readonly ?string $title,
        public readonly ?array $authors,
        public readonly ?int $publicationYear,
        public readonly ?string $description
    ) {
    }

    public function hasChanges(): bool
    {
        return $this->title !== null
            || $this->authors !== null
            || $this->publicationYear !== null
            || $this->description !== null;
    }
}
