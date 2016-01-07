<?php
require 'vendor/autoload.php';
use \Slim\App;
use BB8\Emoji\Database\Connection;
use BB8\Emoji\Models\User;
use BB8\Emoji\Database\Schema;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

$connection = new Connection();
Schema::createSchema();
$configuration = [
    'settings' => [
        'displayErrorDetails' => true,
    ],
];
$app = new App($configuration);

//Index page
$app->get('/', 'BB8\Emoji\Controllers\UserController:index');

$app->run();
