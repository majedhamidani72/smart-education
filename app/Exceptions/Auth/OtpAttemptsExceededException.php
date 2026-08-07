<?php

namespace App\Exceptions\Auth;

use Exception;

class OtpAttemptsExceededException extends Exception
{
    public function __construct()
    {
        parent::__construct(
            'Maximum attempts exceeded.'
        );
    }
}
