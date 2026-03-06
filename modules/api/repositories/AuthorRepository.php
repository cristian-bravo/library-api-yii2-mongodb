<?php

declare(strict_types=1);

namespace app\modules\api\repositories;

use app\models\Author;
use app\modules\api\exceptions\ValidationException;

final class AuthorRepository extends BaseRepository
{
    /**
     * @return array{items: array<int, Author>, total: int}
     */
    public function paginate(int $page, int $perPage): array
    {
        $offset = ($page - 1) * $perPage;
        $query = Author::find()->orderBy(['full_name' => SORT_ASC, '_id' => SORT_ASC]);

        $total = (int) (clone $query)->count();
        /** @var array<int, Author> $items */
        $items = $query->offset($offset)->limit($perPage)->all();

        return [
            'items' => $items,
            'total' => $total,
        ];
    }

    public function findById(string $id): ?Author
    {
        $objectId = $this->toObjectId($id);

        /** @var Author|null $author */
        $author = Author::findOne(['_id' => $objectId]);
        return $author;
    }

    /**
     * @param array<int, string> $ids
     * @return array<int, Author>
     */
    public function findByIds(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        $objectIds = $this->toObjectIds($ids);

        /** @var array<int, Author> $authors */
        $authors = Author::find()->where(['_id' => ['$in' => $objectIds]])->all();
        return $authors;
    }

    /**
     * @param array<int, string> $bookIds
     */
    public function create(string $fullName, string $birthDate, array $bookIds): Author
    {
        $author = new Author();
        $author->full_name = $fullName;
        $author->birth_date = $birthDate;
        $author->books = $this->toObjectIds($bookIds);

        $this->save($author);

        return $author;
    }

    /**
     * @param array<int, string>|null $bookIds
     */
    public function update(
        Author $author,
        ?string $fullName,
        ?string $birthDate,
        ?array $bookIds
    ): Author {
        if ($fullName !== null) {
            $author->full_name = $fullName;
        }

        if ($birthDate !== null) {
            $author->birth_date = $birthDate;
        }

        if ($bookIds !== null) {
            $author->books = $this->toObjectIds($bookIds);
        }

        $this->save($author);

        return $author;
    }

    public function save(Author $author): void
    {
        if (!$author->save()) {
            throw new ValidationException('Author validation failed.', $author->getErrors());
        }
    }

    public function delete(Author $author): void
    {
        if ($author->delete() === false) {
            throw new ValidationException('Author could not be deleted.');
        }
    }

    /**
     * @param array<int, string> $ids
     */
    public function existsByIds(array $ids): bool
    {
        if ($ids === []) {
            return true;
        }

        $objectIds = $this->toObjectIds($ids);
        $count = (int) Author::find()->where(['_id' => ['$in' => $objectIds]])->count();

        return $count === count($objectIds);
    }

    /**
     * @param array<int, string> $authorIds
     */
    public function addBookToAuthors(string $bookId, array $authorIds): void
    {
        if ($authorIds === []) {
            return;
        }

        $bookObjectId = $this->toObjectId($bookId);
        $authorObjectIds = $this->toObjectIds($authorIds);

        Author::updateAll(
            ['$addToSet' => ['books' => $bookObjectId]],
            ['_id' => ['$in' => $authorObjectIds]]
        );
    }

    /**
     * @param array<int, string> $authorIds
     */
    public function removeBookFromAuthors(string $bookId, array $authorIds): void
    {
        if ($authorIds === []) {
            return;
        }

        $bookObjectId = $this->toObjectId($bookId);
        $authorObjectIds = $this->toObjectIds($authorIds);

        Author::updateAll(
            ['$pull' => ['books' => $bookObjectId]],
            ['_id' => ['$in' => $authorObjectIds]]
        );
    }

    public function removeBookFromAllAuthors(string $bookId): void
    {
        $bookObjectId = $this->toObjectId($bookId);

        Author::updateAll(
            ['$pull' => ['books' => $bookObjectId]],
            ['books' => $bookObjectId]
        );
    }

    public function ensureIndexes(): void
    {
        $collection = Author::getCollection();
        $collection->createIndex(['full_name' => 1]);
        $collection->createIndex(['books' => 1]);
    }
}
