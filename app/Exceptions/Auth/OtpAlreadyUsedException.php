<?php

namespace App\Exceptions\Auth;

use Exception;

class OtpAlreadyUsedException extends Exception
{
    public function __construct()
    {
        parent::__construct(
            'OTP has already been used.'
        );
    }
}
