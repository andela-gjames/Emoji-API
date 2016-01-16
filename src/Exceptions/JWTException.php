<?php

namespace BB8\Emoji\Exceptions;

class JWTException extends \Exception
{
    protected $statusCode;

    public function __construct($msg, $statusCode = 400)
    {
        $this->statusCode = $statusCode;
        parent::__construct($msg);
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }
}
