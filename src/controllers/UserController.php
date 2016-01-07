<?php
namespace BB8\Emoji\Controllers;

use Firebase\JWT\JWT;
use BB8\Emoji\Models\User;
use BB8\Emoji\Controllers\BaseController;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class UserController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index(ServerRequestInterface $request, ResponseInterface $response){
        $response           =   $response->withAddedHeader('Content-type', 'application/json');
        $data               =   $request->getParsedBody();
        $response_body      =   $response->getBody();

        User::create(['username'=>'Ramos16', 'password'=>hash('sha256', 'Ramos16')]);

        $response_body->write(json_encode(['message'=>'User Created']));
        return $response;
    }
}
