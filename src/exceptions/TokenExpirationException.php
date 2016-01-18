<?php

namespace BB8\Emoji\Exceptions;

class TokenExpirationException extends \Exception
{
    public function __construct($msg)
    {
        parent::__construct($msg);
    }
}
