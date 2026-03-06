<?php

declare(strict_types=1);

namespace app\models;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use MongoDB\BSON\UTCDateTime;

final class Author extends BaseMongoModel
{
    public static function collectionName(): array
    {
        return ['library_db', 'authors'];
    }

    public function attributes(): array
    {
        return [
            '_id',
            'full_name',
            'birth_date',
            'books',
            'created_at',
            'updated_at',
        ];
    }

    public function rules(): array
    {
        return [
            [['full_name', 'birth_date'], 'required'],
            [['full_name'], 'string', 'max' => 255],
            [['books'], 'validateObjectIdArray'],
            [['birth_date'], 'validateBirthDate'],
        ];
    }

    public function beforeValidate(): bool
    {
        if ($this->books === null) {
            $this->books = [];
        }

        return parent::beforeValidate();
    }

    public function validateBirthDate(string $attribute): void
    {
        $value = $this->$attribute;

        if ($value instanceof UTCDateTime || $value instanceof DateTimeInterface) {
            return;
        }

        if (!is_string($value)) {
            $this->addError($attribute, 'Birth date must use Y-m-d format.');
            return;
        }

        $date = DateTimeImmutable::createFromFormat('Y-m-d', $value, new DateTimeZone('UTC'));
        if ($date === false || $date->format('Y-m-d') !== $value) {
            $this->addError($attribute, 'Birth date must use Y-m-d format.');
        }
    }

    public function beforeSave($insert): bool
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        $this->books = static::normalizeObjectIdArray((array) $this->books);

        if (is_string($this->birth_date)) {
            $date = DateTimeImmutable::createFromFormat('Y-m-d', $this->birth_date, new DateTimeZone('UTC'));
            if ($date !== false) {
                $this->birth_date = new UTCDateTime($date->setTime(0, 0)->getTimestamp() * 1000);
            }
        } elseif ($this->birth_date instanceof DateTimeInterface) {
            $this->birth_date = new UTCDateTime($this->birth_date->getTimestamp() * 1000);
        }

        $this->touchTimestamps((bool) $insert);

        return true;
    }

    public function fields(): array
    {
        return [
            'id' => static fn (self $model): ?string => static::stringifyObjectId($model->_id),
            'full_name',
            'birth_date' => static function (self $model): ?string {
                if ($model->birth_date instanceof UTCDateTime) {
                    return $model->birth_date
                        ->toDateTime()
                        ->setTimezone(new DateTimeZone('UTC'))
                        ->format('Y-m-d');
                }

                if ($model->birth_date instanceof DateTimeInterface) {
                    return $model->birth_date->format('Y-m-d');
                }

                return is_string($model->birth_date) ? $model->birth_date : null;
            },
            'books' => static fn (self $model): array => static::stringifyObjectIdArray((array) $model->books),
        ];
    }

    public static function ensureIndexes(): void
    {
        $collection = static::getCollection();
        $collection->createIndex(['full_name' => 1]);
        $collection->createIndex(['books' => 1]);
    }

    /**
     * @param array<int, \MongoDB\BSON\ObjectId> $ids
     */
    public static function existsByIds(array $ids): bool
    {
        if ($ids === []) {
            return true;
        }

        $count = (int) static::find()->where(['_id' => ['$in' => $ids]])->count();

        return $count === count($ids);
    }
}
