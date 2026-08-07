<?php

namespace App\Exceptions\Auth;

use Exception;

class InvalidOtpException extends Exception
{
    public function __construct()
    {
        parent::__construct(
            'OTP is incorrect.'
        );
    }
}
