<?php
require 'vendor/autoload.php';

use \Slim\App;
use \Slim\Container;
use BB8\Emoji\Database\Connection;
use BB8\Emoji\Database\Schema;
use BB8\Emoji\Models\User;
use BB8\Emoji\Middleware;

//Create connection to database
$connection = new Connection();

//Creaet database tables if table does not exist
Schema::createSchema();

//Initialize a new dependency container
$container = new Container();

//Add container to handle all exceptions/errors, fail safe and return json
$container['errorHandler'] = function ($container) {
    return function ($request, $response, $exception) use ($container) {
        //Format of exception to return
        $data = [
            'message' => $exception->getMessage()
        ];
        return $container->get('response')->withStatus(500)
            ->withHeader('Content-Type', 'application/json')
            ->write(json_encode($data));
    };
};

//Register authentication container Dependency
$container['auth'] = function ($container) {
    return new BB8\Emoji\Auth($container);
};

//Initialize the slim app
$app = new App($container);

//Add middleware at app level
$app->add('BB8\Emoji\Middleware:init');

//Index page
$app->get('/', 'BB8\Emoji\Controllers\UserController:index');

//Create new user
$app->post('/signup', 'BB8\Emoji\Controllers\UserController:create');

//Login Route
$app->post('/auth/login', 'BB8\Emoji\Controllers\UserController:login');

//Logout Route
$app->get('/auth/logout', 'BB8\Emoji\Controllers\UserController:logout');

//List all emojis Route
$app->get('/emojis', 'BB8\Emoji\Controllers\EmojiController:index');

//Gets an emoji
$app->get('/emojis/{id}', 'BB8\Emoji\Controllers\EmojiController:show');

//Adds a new Emoji
$app->post('/emojis', 'BB8\Emoji\Controllers\EmojiController:create')
    ->add('BB8\Emoji\Middleware:authorize');;

//Updates an Emoji
$app->put('/emojis/{id}', 'BB8\Emoji\Controllers\EmojiController:update')
    ->add('BB8\Emoji\Middleware:authorize');

//Updates an Emoji Keyword
$app->put('/emojis/{id}/{kId}', 'BB8\Emoji\Controllers\EmojiController:updateKey')
    ->add('BB8\Emoji\Middleware:authorize');

//Partially Updates an Emoji
$app->patch('/emojis/{id}', 'BB8\Emoji\Controllers\EmojiController:update')
    ->add('BB8\Emoji\Middleware:authorize');;

//Deletes an Emoji
$app->delete('/emojis/{id}', 'BB8\Emoji\Controllers\EmojiController:destroy')
    ->add('BB8\Emoji\Middleware:authorize');;

//Load and run the application
$app->run();
