<?php

declare(strict_types=1);

namespace tests\Support;

use app\modules\api\repositories\UserRepository;
use PHPUnit\Framework\TestCase;
use Throwable;
use Yii;

abstract class IntegrationTestCase extends TestCase
{
    private static bool $mongoChecked = false;
    private static bool $mongoAvailable = false;
    private static string $mongoReason = '';

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        TestApplication::boot();

        if (self::$mongoChecked) {
            return;
        }

        try {
            Yii::$app->mongodb->createCommand(['ping' => 1])->execute();
            self::$mongoAvailable = true;
        } catch (Throwable $throwable) {
            self::$mongoAvailable = false;
            self::$mongoReason = 'MongoDB no disponible para tests: ' . $throwable->getMessage();
        }

        self::$mongoChecked = true;
    }

    protected function setUp(): void
    {
        parent::setUp();

        if (!self::$mongoAvailable) {
            $this->markTestSkipped(self::$mongoReason);
        }

        $this->resetDatabase();
        TestApplication::resetResponse();
    }

    protected function loginAndGetToken(): string
    {
        $response = $this->request(
            'api/auth/login',
            'POST',
            [
                'username' => (string) ($_ENV['BOOTSTRAP_ADMIN_USERNAME'] ?? 'admin'),
                'password' => (string) ($_ENV['BOOTSTRAP_ADMIN_PASSWORD'] ?? 'Admin123!'),
            ]
        );

        self::assertSame(200, Yii::$app->response->statusCode);
        self::assertSame('success', $response['status'] ?? null);

        return (string) ($response['data']['token'] ?? '');
    }

    /**
     * @param array<string, mixed> $body
     * @param array<string, mixed> $query
     * @param array<string, mixed> $routeParams
     * @return array<string, mixed>
     */
    protected function request(
        string $route,
        string $method = 'GET',
        array $body = [],
        array $query = [],
        ?string $token = null,
        array $routeParams = []
    ): array {
        $request = Yii::$app->request;
        $headers = $request->getHeaders();
        $headers->removeAll();

        if ($token !== null && $token !== '') {
            $headers->set('Authorization', 'Bearer ' . $token);
        }

        $_SERVER['REQUEST_METHOD'] = strtoupper($method);
        $request->setBodyParams($body);
        $request->setQueryParams($query);

        TestApplication::resetResponse();

        /** @var array<string, mixed> $result */
        $result = Yii::$app->runAction($route, $routeParams);
        return $result;
    }

    private function resetDatabase(): void
    {
        Yii::$app->mongodb->getCollection('books')->remove([]);
        Yii::$app->mongodb->getCollection('authors')->remove([]);
        Yii::$app->mongodb->getCollection('users')->remove([]);

        $userRepository = new UserRepository();
        $userRepository->ensureUserExists(
            (string) ($_ENV['BOOTSTRAP_ADMIN_USERNAME'] ?? 'admin'),
            (string) ($_ENV['BOOTSTRAP_ADMIN_PASSWORD'] ?? 'Admin123!')
        );
    }
}
