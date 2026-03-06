<?php

declare(strict_types=1);

namespace app\modules\api\requests;

use app\modules\api\dto\LoginDto;

final class LoginRequest extends BaseRequest
{
    public ?string $username = null;
    public ?string $password = null;

    public function rules(): array
    {
        return [
            [['username', 'password'], 'required'],
            [['username'], 'string', 'min' => 3, 'max' => 100],
            [['password'], 'string', 'min' => 1],
        ];
    }

    public function beforeValidate(): bool
    {
        $this->username = $this->normalizeString($this->username);

        return parent::beforeValidate();
    }

    public function toDto(): LoginDto
    {
        return new LoginDto(
            (string) $this->username,
            (string) $this->password
        );
    }
}
