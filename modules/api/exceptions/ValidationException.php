<?php

declare(strict_types=1);

namespace app\modules\api\exceptions;

use app\commons\constants\ErrorCodes;
use yii\base\Model;

final class ValidationException extends DomainException
{
    /**
     * @param array<string, mixed> $details
     */
    public function __construct(
        string $message = 'Validation failed.',
        array $details = []
    ) {
        parent::__construct($message, ErrorCodes::VALIDATION_ERROR, $details);
    }

    public static function fromModel(Model $model): self
    {
        $errors = $model->getErrors();
        $message = 'Validation failed.';

        foreach ($errors as $fieldErrors) {
            if (is_array($fieldErrors) && isset($fieldErrors[0]) && is_string($fieldErrors[0])) {
                $message = $fieldErrors[0];
                break;
            }
        }

        return new self($message, $errors);
    }
}
