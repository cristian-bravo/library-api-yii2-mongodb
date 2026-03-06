<?php

declare(strict_types=1);

namespace app\modules\api\requests;

use app\modules\api\dto\BookUpdateDto;
use app\modules\api\exceptions\ValidationException;

final class BookUpdateRequest extends BaseRequest
{
    public ?string $title = null;
    /** @var array<int, string>|null */
    public ?array $authors = null;
    public int|string|null $publication_year = null;
    public ?string $description = null;

    /** @var array<string, bool> */
    private array $provided = [];

    public static function fromPayload(array $payload): static
    {
        $request = new static();
        $request->provided = array_fill_keys(array_keys($payload), true);
        $request->load($payload, '');

        if (!$request->validate()) {
            throw ValidationException::fromModel($request);
        }

        return $request;
    }

    public function rules(): array
    {
        return [
            [['title'], 'string', 'max' => 255],
            [['description'], 'string'],
            [['publication_year'], 'integer', 'min' => 0, 'max' => 3000],
            [['authors'], 'validateObjectIdArray'],
            [['authors'], 'validateAuthorsCountIfProvided'],
            [['title'], 'validateTitleIfProvided'],
            [['publication_year'], 'validatePublicationYearIfProvided'],
            [['description'], 'validateDescriptionIfProvided'],
            [['title'], 'validateAtLeastOneProvided'],
        ];
    }

    public function beforeValidate(): bool
    {
        if ($this->isProvided('title')) {
            $this->title = $this->normalizeString($this->title);
        }

        if ($this->isProvided('description')) {
            $this->description = $this->normalizeString($this->description);
        }

        if ($this->isProvided('authors')) {
            $this->authors = $this->normalizeObjectIdArray($this->authors);
        }

        if ($this->isProvided('publication_year') && is_string($this->publication_year) && is_numeric($this->publication_year)) {
            $this->publication_year = (int) $this->publication_year;
        }

        return parent::beforeValidate();
    }

    public function toDto(): BookUpdateDto
    {
        return new BookUpdateDto(
            $this->isProvided('title') ? $this->title : null,
            $this->isProvided('authors') ? $this->authors : null,
            $this->isProvided('publication_year') ? (int) $this->publication_year : null,
            $this->isProvided('description') ? $this->description : null
        );
    }

    public function validateAtLeastOneProvided(string $attribute): void
    {
        if ($this->provided === []) {
            $this->addError($attribute, 'At least one field is required to update.');
        }
    }

    public function validateAuthorsCountIfProvided(string $attribute): void
    {
        if (!$this->isProvided('authors')) {
            return;
        }

        if (!is_array($this->authors) || count($this->authors) < 1) {
            $this->addError($attribute, 'At least one author is required.');
        }
    }

    public function validateTitleIfProvided(string $attribute): void
    {
        if (!$this->isProvided('title')) {
            return;
        }

        if ($this->title === null) {
            $this->addError($attribute, 'Title cannot be empty.');
        }
    }

    public function validatePublicationYearIfProvided(string $attribute): void
    {
        if (!$this->isProvided('publication_year')) {
            return;
        }

        if ($this->publication_year === null || $this->publication_year === '') {
            $this->addError($attribute, 'Publication year cannot be empty.');
        }
    }

    public function validateDescriptionIfProvided(string $attribute): void
    {
        if (!$this->isProvided('description')) {
            return;
        }

        if ($this->description === null) {
            $this->description = '';
        }
    }

    private function isProvided(string $field): bool
    {
        return isset($this->provided[$field]);
    }
}
