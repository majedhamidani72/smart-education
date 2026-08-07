<?php

namespace App\Exceptions\Auth;

use Exception;

class ExpiredOtpException extends Exception
{
    public function __construct()
    {
        parent::__construct(
            'OTP has expired.'
        );
    }
}
