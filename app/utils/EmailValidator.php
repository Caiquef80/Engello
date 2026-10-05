<?php

declare(strict_types=1);

namespace App\Utils;

use InvalidArgumentException;

class EmailValidator
{
    public static function validate(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}