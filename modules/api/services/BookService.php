<?php

declare(strict_types=1);

namespace app\modules\api\services;

use app\models\BaseMongoModel;
use app\models\Book;
use app\modules\api\dto\BookCreateDto;
use app\modules\api\dto\BookUpdateDto;
use app\modules\api\exceptions\DomainException;
use app\modules\api\exceptions\NotFoundException;
use app\modules\api\exceptions\ValidationException;
use app\modules\api\repositories\AuthorRepository;
use app\modules\api\repositories\BookRepository;
use app\commons\constants\ErrorCodes;

final class BookService
{
    public function __construct(
        private readonly BookRepository $bookRepository = new BookRepository(),
        private readonly AuthorRepository $authorRepository = new AuthorRepository()
    ) {
    }

    /**
     * @return array{items: array<int, array<string, mixed>>, pagination: array<string, int>}
     */
    public function list(int $page, int $perPage, string $expand = ''): array
    {
        $result = $this->bookRepository->paginate($page, $perPage);
        $items = $result['items'];
        $expandAuthors = $this->shouldExpand($expand, 'authors');

        $authorMap = $expandAuthors ? $this->loadAuthorsForBooks($items) : [];
        $serializedItems = array_map(
            fn (Book $book): array => $this->serializeBook($book, $expandAuthors, $authorMap),
            $items
        );

        return [
            'items' => $serializedItems,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $result['total'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getById(string $id, string $expand = ''): array
    {
        $book = $this->findBookOrFail($id);
        $expandAuthors = $this->shouldExpand($expand, 'authors');
        $authorMap = $expandAuthors ? $this->loadAuthorsForBooks([$book]) : [];

        return $this->serializeBook($book, $expandAuthors, $authorMap);
    }

    /**
     * @return array<string, mixed>
     */
    public function create(BookCreateDto $dto): array
    {
        if (!$this->authorRepository->existsByIds($dto->authors)) {
            throw new ValidationException('One or more authors do not exist.', [
                'authors' => ['One or more authors do not exist.'],
            ]);
        }

        $book = $this->bookRepository->create(
            $dto->title,
            $dto->publicationYear,
            $dto->description,
            $dto->authors
        );

        $bookId = (string) $book->_id;
        $this->authorRepository->addBookToAuthors($bookId, $dto->authors);

        return $this->serializeBook($book);
    }

    /**
     * @return array<string, mixed>
     */
    public function update(string $id, BookUpdateDto $dto): array
    {
        if (!$dto->hasChanges()) {
            throw new DomainException('At least one field is required to update.');
        }

        $book = $this->findBookOrFail($id);
        $previousAuthorIds = BaseMongoModel::stringifyObjectIdArray((array) $book->authors);
        $newAuthorIds = $dto->authors ?? $previousAuthorIds;

        if ($newAuthorIds === []) {
            throw new ValidationException('At least one author is required.', [
                'authors' => ['At least one author is required.'],
            ]);
        }

        if (!$this->authorRepository->existsByIds($newAuthorIds)) {
            throw new ValidationException('One or more authors do not exist.', [
                'authors' => ['One or more authors do not exist.'],
            ]);
        }

        $updated = $this->bookRepository->update(
            $book,
            $dto->title,
            $dto->publicationYear,
            $dto->description,
            $newAuthorIds
        );

        $this->syncAuthorRelations((string) $updated->_id, $newAuthorIds, $previousAuthorIds);

        return $this->serializeBook($updated);
    }

    public function delete(string $id): void
    {
        $book = $this->findBookOrFail($id);
        $bookId = (string) $book->_id;

        $this->bookRepository->delete($book);
        $this->authorRepository->removeBookFromAllAuthors($bookId);
    }

    private function findBookOrFail(string $id): Book
    {
        if (!BaseMongoModel::isValidObjectId($id)) {
            throw new DomainException(
                'Invalid resource identifier format.',
                ErrorCodes::INVALID_IDENTIFIER
            );
        }

        $book = $this->bookRepository->findById($id);
        if ($book === null) {
            throw new NotFoundException('Book not found.');
        }

        return $book;
    }

    /**
     * @param array<int, string> $newAuthorIds
     * @param array<int, string> $oldAuthorIds
     */
    private function syncAuthorRelations(string $bookId, array $newAuthorIds, array $oldAuthorIds): void
    {
        $toAdd = array_values(array_diff($newAuthorIds, $oldAuthorIds));
        $toRemove = array_values(array_diff($oldAuthorIds, $newAuthorIds));

        if ($toAdd !== []) {
            $this->authorRepository->addBookToAuthors($bookId, $toAdd);
        }

        if ($toRemove !== []) {
            $this->authorRepository->removeBookFromAuthors($bookId, $toRemove);
        }
    }

    private function shouldExpand(string $expand, string $value): bool
    {
        if ($expand === '') {
            return false;
        }

        $parts = array_map('trim', explode(',', $expand));
        return in_array($value, $parts, true);
    }

    /**
     * @param array<int, Book> $books
     * @return array<string, array<string, mixed>>
     */
    private function loadAuthorsForBooks(array $books): array
    {
        $authorIds = [];
        foreach ($books as $book) {
            foreach (BaseMongoModel::stringifyObjectIdArray((array) $book->authors) as $authorId) {
                $authorIds[$authorId] = $authorId;
            }
        }

        $authors = $this->authorRepository->findByIds(array_values($authorIds));
        $map = [];

        foreach ($authors as $author) {
            $asArray = $author->toArray();
            $id = (string) ($asArray['id'] ?? '');
            if ($id === '') {
                continue;
            }

            $map[$id] = [
                'id' => $asArray['id'],
                'full_name' => $asArray['full_name'],
                'birth_date' => $asArray['birth_date'],
            ];
        }

        return $map;
    }

    /**
     * @param array<string, array<string, mixed>> $authorMap
     * @return array<string, mixed>
     */
    private function serializeBook(Book $book, bool $expandAuthors = false, array $authorMap = []): array
    {
        $data = $book->toArray();

        if ($expandAuthors) {
            $expanded = [];
            foreach ((array) ($data['authors'] ?? []) as $authorId) {
                if (is_string($authorId) && isset($authorMap[$authorId])) {
                    $expanded[] = $authorMap[$authorId];
                }
            }
            $data['authors_expanded'] = $expanded;
        }

        return $data;
    }
}
