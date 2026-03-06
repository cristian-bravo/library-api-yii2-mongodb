<?php

declare(strict_types=1);

namespace app\modules\api\services;

use app\commons\constants\ErrorCodes;
use app\models\Author;
use app\models\BaseMongoModel;
use app\modules\api\dto\AuthorCreateDto;
use app\modules\api\dto\AuthorUpdateDto;
use app\modules\api\exceptions\DomainException;
use app\modules\api\exceptions\NotFoundException;
use app\modules\api\exceptions\ValidationException;
use app\modules\api\repositories\AuthorRepository;
use app\modules\api\repositories\BookRepository;

final class AuthorService
{
    public function __construct(
        private readonly AuthorRepository $authorRepository = new AuthorRepository(),
        private readonly BookRepository $bookRepository = new BookRepository()
    ) {
    }

    /**
     * @return array{items: array<int, array<string, mixed>>, pagination: array<string, int>}
     */
    public function list(int $page, int $perPage, string $expand = ''): array
    {
        $result = $this->authorRepository->paginate($page, $perPage);
        $items = $result['items'];
        $expandBooks = $this->shouldExpand($expand, 'books');

        $bookMap = $expandBooks ? $this->loadBooksForAuthors($items) : [];
        $serializedItems = array_map(
            fn (Author $author): array => $this->serializeAuthor($author, $expandBooks, $bookMap),
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
        $author = $this->findAuthorOrFail($id);
        $expandBooks = $this->shouldExpand($expand, 'books');
        $bookMap = $expandBooks ? $this->loadBooksForAuthors([$author]) : [];

        return $this->serializeAuthor($author, $expandBooks, $bookMap);
    }

    /**
     * @return array<string, mixed>
     */
    public function create(AuthorCreateDto $dto): array
    {
        if (!$this->bookRepository->existsByIds($dto->books)) {
            throw new ValidationException('One or more books do not exist.', [
                'books' => ['One or more books do not exist.'],
            ]);
        }

        $author = $this->authorRepository->create(
            $dto->fullName,
            $dto->birthDate,
            $dto->books
        );

        $authorId = (string) $author->_id;
        if ($dto->books !== []) {
            $this->bookRepository->addAuthorToBooks($authorId, $dto->books);
        }

        return $this->serializeAuthor($author);
    }

    /**
     * @return array<string, mixed>
     */
    public function update(string $id, AuthorUpdateDto $dto): array
    {
        if (!$dto->hasChanges()) {
            throw new DomainException('At least one field is required to update.');
        }

        $author = $this->findAuthorOrFail($id);
        $previousBookIds = BaseMongoModel::stringifyObjectIdArray((array) $author->books);
        $newBookIds = $dto->books ?? $previousBookIds;

        if (!$this->bookRepository->existsByIds($newBookIds)) {
            throw new ValidationException('One or more books do not exist.', [
                'books' => ['One or more books do not exist.'],
            ]);
        }

        $updated = $this->authorRepository->update(
            $author,
            $dto->fullName,
            $dto->birthDate,
            $newBookIds
        );

        $this->syncBookRelations((string) $updated->_id, $newBookIds, $previousBookIds);

        return $this->serializeAuthor($updated);
    }

    public function delete(string $id): void
    {
        $author = $this->findAuthorOrFail($id);
        $authorId = (string) $author->_id;

        $this->authorRepository->delete($author);
        $this->bookRepository->removeAuthorFromAllBooks($authorId);
    }

    private function findAuthorOrFail(string $id): Author
    {
        if (!BaseMongoModel::isValidObjectId($id)) {
            throw new DomainException(
                'Invalid resource identifier format.',
                ErrorCodes::INVALID_IDENTIFIER
            );
        }

        $author = $this->authorRepository->findById($id);
        if ($author === null) {
            throw new NotFoundException('Author not found.');
        }

        return $author;
    }

    /**
     * @param array<int, string> $newBookIds
     * @param array<int, string> $oldBookIds
     */
    private function syncBookRelations(string $authorId, array $newBookIds, array $oldBookIds): void
    {
        $toAdd = array_values(array_diff($newBookIds, $oldBookIds));
        $toRemove = array_values(array_diff($oldBookIds, $newBookIds));

        if ($toAdd !== []) {
            $this->bookRepository->addAuthorToBooks($authorId, $toAdd);
        }

        if ($toRemove !== []) {
            $this->bookRepository->removeAuthorFromBooks($authorId, $toRemove);
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
     * @param array<int, Author> $authors
     * @return array<string, array<string, mixed>>
     */
    private function loadBooksForAuthors(array $authors): array
    {
        $bookIds = [];
        foreach ($authors as $author) {
            foreach (BaseMongoModel::stringifyObjectIdArray((array) $author->books) as $bookId) {
                $bookIds[$bookId] = $bookId;
            }
        }

        $books = $this->bookRepository->findByIds(array_values($bookIds));
        $map = [];

        foreach ($books as $book) {
            $asArray = $book->toArray();
            $id = (string) ($asArray['id'] ?? '');
            if ($id === '') {
                continue;
            }

            $map[$id] = [
                'id' => $asArray['id'],
                'title' => $asArray['title'],
                'publication_year' => $asArray['publication_year'],
                'description' => $asArray['description'],
            ];
        }

        return $map;
    }

    /**
     * @param array<string, array<string, mixed>> $bookMap
     * @return array<string, mixed>
     */
    private function serializeAuthor(Author $author, bool $expandBooks = false, array $bookMap = []): array
    {
        $data = $author->toArray();

        if ($expandBooks) {
            $expanded = [];
            foreach ((array) ($data['books'] ?? []) as $bookId) {
                if (is_string($bookId) && isset($bookMap[$bookId])) {
                    $expanded[] = $bookMap[$bookId];
                }
            }
            $data['books_expanded'] = $expanded;
        }

        return $data;
    }
}
