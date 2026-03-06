<?php

declare(strict_types=1);

namespace app\modules\api\repositories;

use app\models\User;
use app\modules\api\exceptions\ValidationException;

final class UserRepository extends BaseRepository
{
    public function findByUsername(string $username): ?User
    {
        /** @var User|null $user */
        $user = User::findOne(['username' => $username]);
        return $user;
    }

    public function findByAccessToken(string $token): ?User
    {
        /** @var User|null $user */
        $user = User::find()
            ->where([
                'auth_token' => $token,
                'token_expires_at' => ['$gt' => time()],
            ])
            ->one();

        return $user;
    }

    public function save(User $user, ?array $attributes = null): void
    {
        $result = $attributes === null ? $user->save() : $user->save(false, $attributes);
        if (!$result) {
            throw new ValidationException('User could not be saved.', $user->getErrors());
        }
    }

    public function ensureIndexes(): void
    {
        $collection = User::getCollection();
        $collection->createIndex(['username' => 1], ['unique' => true]);
        $collection->createIndex(['auth_token' => 1]);
        $collection->createIndex(['token_expires_at' => 1]);
    }

    public function ensureUserExists(string $username, string $password): void
    {
        if ($username === '' || $password === '') {
            return;
        }

        if ($this->findByUsername($username) !== null) {
            return;
        }

        $user = new User();
        $user->username = $username;
        $user->setPassword($password);
        $user->token_expires_at = 0;
        $this->save($user);
    }
}
