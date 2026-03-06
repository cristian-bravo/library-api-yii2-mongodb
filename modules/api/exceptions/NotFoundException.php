<?php

declare(strict_types=1);

namespace app\modules\api\exceptions;

use app\commons\constants\ErrorCodes;

final class NotFoundException extends DomainException
{
    /**
     * @param array<string, mixed> $details
     */
    public function __construct(string $message = 'Resource not found.', array $details = [])
    {
        parent::__construct($message, ErrorCodes::NOT_FOUND, $details);
    }
}
