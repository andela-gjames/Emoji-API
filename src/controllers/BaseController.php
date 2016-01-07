<?php
namespace BB8\Emoji\Controllers;
use BB8\Emoji\Database\Connection;

class BaseController
{
    public function __construct()
    {
        $dotenv = new \Dotenv\Dotenv(__DIR__.'/../../');
        $dotenv->load();

        $connection = new Connection();
    }
}
