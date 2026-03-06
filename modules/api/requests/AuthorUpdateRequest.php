<?php

declare(strict_types=1);

namespace app\modules\api\requests;

use app\modules\api\dto\AuthorUpdateDto;
use app\modules\api\exceptions\ValidationException;
use DateTimeImmutable;
use DateTimeZone;

final class AuthorUpdateRequest extends BaseRequest
{
    public ?string $full_name = null;
    public ?string $birth_date = null;
    /** @var array<int, string>|null */
    public ?array $books = null;

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
            [['full_name'], 'string', 'max' => 255],
            [['birth_date'], 'validateBirthDateIfProvided'],
            [['books'], 'validateObjectIdArray'],
            [['full_name'], 'validateAtLeastOneProvided'],
            [['full_name'], 'validateFullNameIfProvided'],
        ];
    }

    public function beforeValidate(): bool
    {
        if ($this->isProvided('full_name')) {
            $this->full_name = $this->normalizeString($this->full_name);
        }

        if ($this->isProvided('birth_date')) {
            $this->birth_date = $this->normalizeString($this->birth_date);
        }

        if ($this->isProvided('books')) {
            $this->books = $this->normalizeObjectIdArray($this->books);
        }

        return parent::beforeValidate();
    }

    public function validateAtLeastOneProvided(string $attribute): void
    {
        if ($this->provided === []) {
            $this->addError($attribute, 'At least one field is required to update.');
        }
    }

    public function validateFullNameIfProvided(string $attribute): void
    {
        if (!$this->isProvided('full_name')) {
            return;
        }

        if ($this->full_name === null) {
            $this->addError($attribute, 'Full name cannot be empty.');
        }
    }

    public function validateBirthDateIfProvided(string $attribute): void
    {
        if (!$this->isProvided('birth_date')) {
            return;
        }

        if ($this->$attribute === null) {
            $this->addError($attribute, 'Birth date cannot be empty.');
            return;
        }

        $date = DateTimeImmutable::createFromFormat('Y-m-d', (string) $this->$attribute, new DateTimeZone('UTC'));
        if ($date === false || $date->format('Y-m-d') !== $this->$attribute) {
            $this->addError($attribute, 'Birth date must use Y-m-d format.');
        }
    }

    public function toDto(): AuthorUpdateDto
    {
        return new AuthorUpdateDto(
            $this->isProvided('full_name') ? $this->full_name : null,
            $this->isProvided('birth_date') ? $this->birth_date : null,
            $this->isProvided('books') ? $this->books : null
        );
    }

    private function isProvided(string $field): bool
    {
        return isset($this->provided[$field]);
    }
}
