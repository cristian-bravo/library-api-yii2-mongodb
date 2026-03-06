<?php

declare(strict_types=1);

namespace app\modules\api\dto;

final class LoginDto
{
    public function __construct(
        public readonly string $username,
        public readonly string $password
    ) {
    }
}
