<?php

namespace App\Exception;

use RuntimeException;

final class UserAlreadyExistsException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct(
            'User with this login and pass already exists.'
        );
    }
}
