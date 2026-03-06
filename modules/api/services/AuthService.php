<?php

declare(strict_types=1);

namespace app\modules\api\services;

use app\models\User;
use app\modules\api\dto\LoginDto;
use app\modules\api\exceptions\UnauthorizedException;
use app\modules\api\repositories\UserRepository;
use Yii;

final class AuthService
{
    public function __construct(
        private readonly UserRepository $userRepository = new UserRepository()
    ) {
    }

    /**
     * @return array{token: string, expires_in: int}
     */
    public function login(LoginDto $dto): array
    {
        $user = $this->userRepository->findByUsername($dto->username);
        if ($user === null || !$user->validatePassword($dto->password)) {
            throw new UnauthorizedException('Invalid credentials.');
        }

        $ttl = (int) (Yii::$app->params['tokenTtl'] ?? 1800);
        return $this->issueToken($user, $ttl);
    }

    /**
     * @return array{token: string, expires_in: int}
     */
    public function issueToken(User $user, int $ttl): array
    {
        $user->issueAccessToken($ttl);
        $this->userRepository->save($user, ['auth_token', 'token_expires_at', 'updated_at']);

        return [
            'token' => (string) $user->auth_token,
            'expires_in' => $ttl,
        ];
    }

    public function validateToken(string $token): ?User
    {
        return $this->userRepository->findByAccessToken($token);
    }
}
