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

//Login Route
$app->post('/auth/login', 'BB8\Emoji\Controllers\UserController:login');

//Logout Route
$app->get('/auth/logout', 'BB8\Emoji\Controllers\UserController:logout');

//List all emojis Route
$app->get('/emojis', 'BB8\Emoji\Controllers\EmojiController:index');

//Gets an emoji
$app->get('/emoji/{id}', 'BB8\Emoji\Controllers\EmojiController:show');

//Adds a new Emoji
$app->post('/emojis', 'BB8\Emoji\Controllers\EmojiController:create');

//Updates an Emoji
$app->put('/emojis/{id}', 'BB8\Emoji\Controllers\EmojiController:update');

//Partially Updates an Emoji
$app->patch('/emojis/{id}', 'BB8\Emoji\Controllers\EmojiController:update');

//Deletes an Emoji
$app->delete('/emojis/{id}', 'BB8\Emoji\Controllers\EmojiController:destroy');


$app->run();
