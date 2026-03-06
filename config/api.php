<?php

declare(strict_types=1);

/**
 * @return string
 */
$envString = static function (string $name, string $default = ''): string {
    $value = $_ENV[$name] ?? $_SERVER[$name] ?? getenv($name);
    if ($value === false || $value === null) {
        return $default;
    }

    $value = trim((string) $value);
    return $value === '' ? $default : $value;
};

/**
 * @return int
 */
$envInt = static function (string $name, int $default) use ($envString): int {
    $value = $envString($name, (string) $default);
    return is_numeric($value) ? (int) $value : $default;
};

/**
 * @return bool
 */
$envBool = static function (string $name, bool $default) use ($envString): bool {
    $value = strtolower($envString($name, $default ? 'true' : 'false'));

    return match ($value) {
        '1', 'true', 'yes', 'on' => true,
        '0', 'false', 'no', 'off' => false,
        default => $default,
    };
};

$defaultMongoUri = 'mongodb://localhost:27017/library_db';

return [
    'mongoUri' => $envString('MONGO_URI', $envString('MONGODB_DSN', $defaultMongoUri)),
    'tokenTtl' => max(60, $envInt('TOKEN_TTL', 1800)),
    'pagination' => [
        'defaultPerPage' => max(1, $envInt('API_PAGE_SIZE', 20)),
        'maxPerPage' => max(1, $envInt('API_MAX_PAGE_SIZE', 100)),
    ],
    'bootstrapAdminUser' => $envBool('BOOTSTRAP_ADMIN_USER', false),
    'bootstrapAdminUsername' => $envString('BOOTSTRAP_ADMIN_USERNAME', 'admin'),
    'bootstrapAdminPassword' => $envString('BOOTSTRAP_ADMIN_PASSWORD', 'Admin123!'),
];
