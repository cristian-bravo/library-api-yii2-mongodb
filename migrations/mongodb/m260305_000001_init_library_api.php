<?php

declare(strict_types=1);

use yii\mongodb\Migration;

final class m260305_000001_init_library_api extends Migration
{
    public function up(): void
    {
        $this->createCollectionIfNotExists('books');
        $this->createCollectionIfNotExists('authors');
        $this->createCollectionIfNotExists('users');

        $this->createIndex('books', ['title' => 1]);
        $this->createIndex('books', ['publication_year' => 1]);
        $this->createIndex('books', ['authors' => 1]);

        $this->createIndex('authors', ['full_name' => 1]);
        $this->createIndex('authors', ['books' => 1]);

        $this->createIndex('users', ['username' => 1], ['unique' => true]);
        $this->createIndex('users', ['auth_token' => 1]);
        $this->createIndex('users', ['token_expires_at' => 1]);
    }

    public function down(): void
    {
        $this->dropIndex('users', ['token_expires_at' => 1]);
        $this->dropIndex('users', ['auth_token' => 1]);
        $this->dropIndex('users', ['username' => 1]);

        $this->dropIndex('authors', ['books' => 1]);
        $this->dropIndex('authors', ['full_name' => 1]);

        $this->dropIndex('books', ['authors' => 1]);
        $this->dropIndex('books', ['publication_year' => 1]);
        $this->dropIndex('books', ['title' => 1]);

        $this->dropCollectionIfExists('users');
        $this->dropCollectionIfExists('authors');
        $this->dropCollectionIfExists('books');
    }

    private function createCollectionIfNotExists(string $name): void
    {
        if ($this->collectionExists($name)) {
            return;
        }

        $this->createCollection($name);
    }

    private function dropCollectionIfExists(string $name): void
    {
        if (!$this->collectionExists($name)) {
            return;
        }

        $this->dropCollection($name);
    }

    private function collectionExists(string $name): bool
    {
        $collections = $this->db
            ->getDatabase()
            ->listCollections(['name' => $name]);

        return $collections !== [];
    }
}
