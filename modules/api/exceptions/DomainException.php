<?php

declare(strict_types=1);

namespace app\modules\api\exceptions;

use app\commons\constants\ErrorCodes;
use RuntimeException;

class DomainException extends RuntimeException
{
    /** @var array<string, mixed> */
    private array $details;
    private string $errorCode;

    /**
     * @param array<string, mixed> $details
     */
    public function __construct(
        string $message = 'Domain error.',
        string $errorCode = ErrorCodes::DOMAIN_ERROR,
        array $details = [],
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->errorCode = $errorCode;
        $this->details = $details;
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    /**
     * @return array<string, mixed>
     */
    public function getDetails(): array
    {
        return $this->details;
    }
}
