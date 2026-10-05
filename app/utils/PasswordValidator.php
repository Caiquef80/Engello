<?php

declare(strict_types=1);

namespace App\Utils;

use InvalidArgumentException;

class PasswordValidator
{
        public static function validate(string $password): bool
    {
        return strlen($password) >= 8
            && preg_match('/[A-Z]/', $password)
            && preg_match('/[^a-zA-Z0-9]/', $password);
    }
}