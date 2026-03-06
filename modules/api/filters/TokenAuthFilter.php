<?php

declare(strict_types=1);

namespace app\modules\api\filters;

use app\modules\api\exceptions\UnauthorizedException;
use app\modules\api\services\AuthService;
use yii\filters\auth\AuthMethod;

final class TokenAuthFilter extends AuthMethod
{
    public string $header = 'Authorization';

    private ?AuthService $authService = null;

    public function authenticate($user, $request, $response)
    {
        $header = $request->getHeaders()->get($this->header);
        if (!is_string($header) || $header === '') {
            return null;
        }

        if (!preg_match('/^Bearer\s+(.+)$/i', $header, $matches)) {
            throw new UnauthorizedException('Invalid Authorization header format.');
        }

        $token = trim($matches[1]);
        if ($token === '') {
            throw new UnauthorizedException('Token is required.');
        }

        $identity = $this->getAuthService()->validateToken($token);
        if ($identity === null) {
            throw new UnauthorizedException('Invalid or expired token.');
        }

        return $identity;
    }

    public function challenge($response): void
    {
        $response->getHeaders()->set('WWW-Authenticate', 'Bearer realm="library-api"');
    }

    public function handleFailure($response): void
    {
        throw new UnauthorizedException('Authorization token is required.');
    }

    private function getAuthService(): AuthService
    {
        return $this->authService ??= new AuthService();
    }
}
