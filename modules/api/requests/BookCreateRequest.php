<?php

declare(strict_types=1);

namespace app\modules\api\requests;

use app\modules\api\dto\BookCreateDto;

final class BookCreateRequest extends BaseRequest
{
    public ?string $title = null;
    /** @var array<int, string> */
    public array $authors = [];
    public int|string|null $publication_year = null;
    public ?string $description = null;

    public function rules(): array
    {
        return [
            [['title', 'publication_year', 'authors'], 'required'],
            [['title'], 'string', 'max' => 255],
            [['description'], 'string'],
            [['publication_year'], 'integer', 'min' => 0, 'max' => 3000],
            [['authors'], 'validateObjectIdArray'],
            [['authors'], 'validateAuthorsCount'],
        ];
    }

    public function beforeValidate(): bool
    {
        $this->title = $this->normalizeString($this->title);
        $this->description = $this->normalizeString($this->description);
        $this->authors = $this->normalizeObjectIdArray($this->authors);

        if (is_string($this->publication_year) && is_numeric($this->publication_year)) {
            $this->publication_year = (int) $this->publication_year;
        }

        return parent::beforeValidate();
    }

    public function validateAuthorsCount(string $attribute): void
    {
        if (count($this->$attribute) < 1) {
            $this->addError($attribute, 'At least one author is required.');
        }
    }

    public function toDto(): BookCreateDto
    {
        return new BookCreateDto(
            (string) $this->title,
            $this->authors,
            (int) $this->publication_year,
            $this->description
        );
    }
}
