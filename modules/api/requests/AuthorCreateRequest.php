<?php

declare(strict_types=1);

namespace app\modules\api\requests;

use app\modules\api\dto\AuthorCreateDto;
use DateTimeImmutable;
use DateTimeZone;

final class AuthorCreateRequest extends BaseRequest
{
    public ?string $full_name = null;
    public ?string $birth_date = null;
    /** @var array<int, string> */
    public array $books = [];

    public function rules(): array
    {
        return [
            [['full_name', 'birth_date'], 'required'],
            [['full_name'], 'string', 'max' => 255],
            [['birth_date'], 'validateBirthDate'],
            [['books'], 'validateObjectIdArray'],
        ];
    }

    public function beforeValidate(): bool
    {
        $this->full_name = $this->normalizeString($this->full_name);
        $this->birth_date = $this->normalizeString($this->birth_date);
        $this->books = $this->normalizeObjectIdArray($this->books);

        return parent::beforeValidate();
    }

    public function validateBirthDate(string $attribute): void
    {
        if ($this->$attribute === null) {
            return;
        }

        $date = DateTimeImmutable::createFromFormat('Y-m-d', (string) $this->$attribute, new DateTimeZone('UTC'));
        if ($date === false || $date->format('Y-m-d') !== $this->$attribute) {
            $this->addError($attribute, 'Birth date must use Y-m-d format.');
        }
    }

    public function toDto(): AuthorCreateDto
    {
        return new AuthorCreateDto(
            (string) $this->full_name,
            (string) $this->birth_date,
            $this->books
        );
    }
}
