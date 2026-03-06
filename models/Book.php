<?php

declare(strict_types=1);

namespace app\models;

final class Book extends BaseMongoModel
{
    public static function collectionName(): array
    {
        return ['library_db', 'books'];
    }

    public function attributes(): array
    {
        return [
            '_id',
            'title',
            'authors',
            'publication_year',
            'description',
            'created_at',
            'updated_at',
        ];
    }

    public function rules(): array
    {
        return [
            [['title', 'publication_year', 'authors'], 'required'],
            [['title'], 'string', 'max' => 255],
            [['description'], 'string'],
            [['publication_year'], 'integer', 'min' => 0, 'max' => 3000],
            [['authors'], 'validateObjectIdArray'],
        ];
    }

    public function beforeValidate(): bool
    {
        if ($this->authors === null) {
            $this->authors = [];
        }

        return parent::beforeValidate();
    }

    public function beforeSave($insert): bool
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        $this->authors = static::normalizeObjectIdArray((array) $this->authors);
        $this->touchTimestamps((bool) $insert);

        return true;
    }

    public function fields(): array
    {
        return [
            'id' => static fn (self $model): ?string => static::stringifyObjectId($model->_id),
            'title',
            'authors' => static fn (self $model): array => static::stringifyObjectIdArray((array) $model->authors),
            'publication_year',
            'description',
        ];
    }

    public static function ensureIndexes(): void
    {
        $collection = static::getCollection();
        $collection->createIndex(['title' => 1]);
        $collection->createIndex(['publication_year' => 1]);
        $collection->createIndex(['authors' => 1]);
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
