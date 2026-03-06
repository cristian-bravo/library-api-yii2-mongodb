<?php

declare(strict_types=1);

namespace app\modules\api\requests;

use app\modules\api\exceptions\ValidationException;
use yii\base\Model;

abstract class BaseRequest extends Model
{
    public static function fromPayload(array $payload): static
    {
        $request = new static();
        $request->load($payload, '');

        if (!$request->validate()) {
            throw ValidationException::fromModel($request);
        }

        return $request;
    }

    protected function normalizeString(mixed $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $trimmed = trim($value);
        return $trimmed === '' ? null : $trimmed;
    }

    /**
     * @param array<int, mixed>|mixed $values
     * @return array<int, string>
     */
    protected function normalizeObjectIdArray(mixed $values): array
    {
        if (!is_array($values)) {
            return [];
        }

        $normalized = [];
        foreach ($values as $value) {
            if (!is_string($value)) {
                continue;
            }

            $trimmed = trim($value);
            if ($trimmed === '' || !$this->isValidObjectId($trimmed)) {
                continue;
            }

            $normalized[$trimmed] = $trimmed;
        }

        return array_values($normalized);
    }

    protected function isValidObjectId(string $value): bool
    {
        return (bool) preg_match('/^[a-fA-F0-9]{24}$/', $value);
    }

    /**
     * @param array<int, mixed>|mixed $values
     */
    public function validateObjectIdArray(string $attribute, mixed $params = null): void
    {
        $value = $this->$attribute;
        if ($value === null) {
            return;
        }

        if (!is_array($value)) {
            $this->addError($attribute, sprintf('%s must be an array of ObjectId.', $attribute));
            return;
        }

        foreach ($value as $index => $item) {
            if (!is_string($item) || !$this->isValidObjectId(trim($item))) {
                $this->addError($attribute, sprintf('%s[%d] is not a valid ObjectId.', $attribute, $index));
            }
        }
    }
}
