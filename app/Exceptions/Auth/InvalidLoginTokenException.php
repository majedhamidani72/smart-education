<?php

namespace App\Exceptions\Auth;

use Exception;

class InvalidLoginTokenException extends Exception
{
    public function __construct()
    {
        parent::__construct(
            'Login token is invalid.'
        );
    }
}
