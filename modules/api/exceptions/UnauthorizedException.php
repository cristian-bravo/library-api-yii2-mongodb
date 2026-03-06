<?php

declare(strict_types=1);

namespace app\modules\api\exceptions;

use app\commons\constants\ErrorCodes;

final class UnauthorizedException extends DomainException
{
    /**
     * @param array<string, mixed> $details
     */
    public function __construct(string $message = 'Unauthorized.', array $details = [])
    {
        parent::__construct($message, ErrorCodes::UNAUTHORIZED, $details);
    }
}
