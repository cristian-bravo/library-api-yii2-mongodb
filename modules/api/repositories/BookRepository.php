<?php

declare(strict_types=1);

namespace app\modules\api\repositories;

use app\models\Book;
use app\modules\api\exceptions\ValidationException;
use MongoDB\BSON\ObjectId;

final class BookRepository extends BaseRepository
{
    /**
     * @return array{items: array<int, Book>, total: int}
     */
    public function paginate(int $page, int $perPage): array
    {
        $offset = ($page - 1) * $perPage;
        $query = Book::find()->orderBy(['publication_year' => SORT_DESC, '_id' => SORT_ASC]);

        $total = (int) (clone $query)->count();
        /** @var array<int, Book> $items */
        $items = $query->offset($offset)->limit($perPage)->all();

        return [
            'items' => $items,
            'total' => $total,
        ];
    }

    public function findById(string $id): ?Book
    {
        $objectId = $this->toObjectId($id);

        /** @var Book|null $book */
        $book = Book::findOne(['_id' => $objectId]);
        return $book;
    }

    /**
     * @param array<int, string> $ids
     * @return array<int, Book>
     */
    public function findByIds(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        $objectIds = $this->toObjectIds($ids);

        /** @var array<int, Book> $books */
        $books = Book::find()->where(['_id' => ['$in' => $objectIds]])->all();
        return $books;
    }

    /**
     * @param array<int, string> $authorIds
     */
    public function create(string $title, int $publicationYear, ?string $description, array $authorIds): Book
    {
        $book = new Book();
        $book->title = $title;
        $book->publication_year = $publicationYear;
        $book->description = $description;
        $book->authors = $this->toObjectIds($authorIds);

        $this->save($book);

        return $book;
    }

    /**
     * @param array<int, string> $authorIds
     */
    public function update(
        Book $book,
        ?string $title,
        ?int $publicationYear,
        ?string $description,
        ?array $authorIds
    ): Book {
        if ($title !== null) {
            $book->title = $title;
        }

        if ($publicationYear !== null) {
            $book->publication_year = $publicationYear;
        }

        if ($description !== null) {
            $book->description = $description;
        }

        if ($authorIds !== null) {
            $book->authors = $this->toObjectIds($authorIds);
        }

        $this->save($book);

        return $book;
    }

    public function save(Book $book): void
    {
        if (!$book->save()) {
            throw new ValidationException('Book validation failed.', $book->getErrors());
        }
    }

    public function delete(Book $book): void
    {
        if ($book->delete() === false) {
            throw new ValidationException('Book could not be deleted.');
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
        $count = (int) Book::find()->where(['_id' => ['$in' => $objectIds]])->count();

        return $count === count($objectIds);
    }

    /**
     * @param array<int, string> $bookIds
     */
    public function addAuthorToBooks(string $authorId, array $bookIds): void
    {
        if ($bookIds === []) {
            return;
        }

        $authorObjectId = $this->toObjectId($authorId);
        $bookObjectIds = $this->toObjectIds($bookIds);

        Book::updateAll(
            ['$addToSet' => ['authors' => $authorObjectId]],
            ['_id' => ['$in' => $bookObjectIds]]
        );
    }

    /**
     * @param array<int, string> $bookIds
     */
    public function removeAuthorFromBooks(string $authorId, array $bookIds): void
    {
        if ($bookIds === []) {
            return;
        }

        $authorObjectId = $this->toObjectId($authorId);
        $bookObjectIds = $this->toObjectIds($bookIds);

        Book::updateAll(
            ['$pull' => ['authors' => $authorObjectId]],
            ['_id' => ['$in' => $bookObjectIds]]
        );
    }

    public function removeAuthorFromAllBooks(string $authorId): void
    {
        $authorObjectId = $this->toObjectId($authorId);

        Book::updateAll(
            ['$pull' => ['authors' => $authorObjectId]],
            ['authors' => $authorObjectId]
        );
    }

    public function ensureIndexes(): void
    {
        $collection = Book::getCollection();
        $collection->createIndex(['title' => 1]);
        $collection->createIndex(['publication_year' => 1]);
        $collection->createIndex(['authors' => 1]);
    }
}
