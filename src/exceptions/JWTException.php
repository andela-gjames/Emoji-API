<?php

namespace BB8\Emoji\Exceptions;

class JWTException extends \Exception
{
    public function __construct($msg)
    {
        parent::__construct($msg);
    }
}
