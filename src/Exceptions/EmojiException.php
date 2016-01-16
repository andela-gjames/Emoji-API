<?php

namespace BB8\Emoji\Exceptions;

class EmojiException extends \Exception
{
    public function __construct($msg)
    {
        parent::__construct($msg);
    }
}
