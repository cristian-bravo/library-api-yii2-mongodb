<?php

declare(strict_types=1);

namespace app\models;

use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;
use yii\mongodb\ActiveRecord;

abstract class BaseMongoModel extends ActiveRecord
{
    public static function isValidObjectId(string $value): bool
    {
        return (bool) preg_match('/^[a-f0-9]{24}$/i', $value);
    }

    public static function toObjectId(string|ObjectId $value): ObjectId
    {
        if ($value instanceof ObjectId) {
            return $value;
        }

        return new ObjectId($value);
    }

    /**
     * @param array<int, string|ObjectId> $values
     * @return array<int, ObjectId>
     */
    public static function normalizeObjectIdArray(array $values): array
    {
        $normalized = [];

        foreach ($values as $value) {
            if ($value instanceof ObjectId) {
                $normalized[(string) $value] = $value;
                continue;
            }

            if (!is_string($value)) {
                continue;
            }

            $trimmed = trim($value);
            if ($trimmed === '' || !static::isValidObjectId($trimmed)) {
                continue;
            }

            $normalized[$trimmed] = new ObjectId($trimmed);
        }

        return array_values($normalized);
    }

    public static function stringifyObjectId(string|ObjectId|null $value): ?string
    {
        if ($value instanceof ObjectId) {
            return (string) $value;
        }

        if (is_string($value) && static::isValidObjectId($value)) {
            return $value;
        }

        return null;
    }

    /**
     * @param array<int, string|ObjectId> $values
     * @return array<int, string>
     */
    public static function stringifyObjectIdArray(array $values): array
    {
        $stringIds = [];

        foreach ($values as $value) {
            $string = static::stringifyObjectId($value);
            if ($string === null) {
                continue;
            }

            $stringIds[$string] = $string;
        }

        return array_values($stringIds);
    }

    public function validateObjectIdArray(string $attribute): void
    {
        $value = $this->$attribute;

        if ($value === null || $value === []) {
            return;
        }

        if (!is_array($value)) {
            $this->addError($attribute, sprintf('"%s" must be an array of ObjectId values.', $attribute));
            return;
        }

        foreach ($value as $index => $item) {
            if ($item instanceof ObjectId) {
                continue;
            }

            if (!is_string($item) || !static::isValidObjectId($item)) {
                $this->addError($attribute, sprintf('Item %d in "%s" is not a valid ObjectId.', $index, $attribute));
            }
        }
    }

    protected function touchTimestamps(bool $insert): void
    {
        $now = new UTCDateTime((int) (microtime(true) * 1000));

        if ($insert && $this->hasAttribute('created_at')) {
            $this->created_at = $now;
        }

        if ($this->hasAttribute('updated_at')) {
            $this->updated_at = $now;
        }
    }
}
