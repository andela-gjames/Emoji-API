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

    public function login(ServerRequestInterface $request, ResponseInterface $response)
    {
        $response           =   $response->withAddedHeader('Content-type', 'application/json');
        $data               =   $request->getParsedBody();
        $response_body      =   $response->getBody();

        if (array_key_exists('username', $data) && array_key_exists('password', $data)) {
            $user   =   User::auth($data['username'], $data['password']);

            if (!!$user) {
                $response   =   $response->withStatus(200);

                $iat        =   time();
                $jit        =   rand(1000, 999999999);
                $exp        =   $iat + 1000000000;

                //Ensures user is not logged out from other device
                $user->jit  =   $user->jit == null ? $jit : $user->jit;
                $user->save();

                $data       =   [
                    'jit'   =>  $jit,
                    'iat'   =>  $iat,
                    'exp'   =>  $exp,
                    'data'  =>  [
                                    'uid'       =>  $user->id,
                                    'username'  =>  $user->username
                                ]
                ];

                $token      =   JWT::encode($data, getenv('SECRET_KEY'));
                $message = [
                  'token'   =>  $token
                ];
            } else {
                $response   =   $response->withStatus(401);
                $message    =   [
                    'message'   =>  'Validation failed',
                    'errors'    =>  [
                        [
                            'message'   =>  'Username or password incorrect'
                        ]
                    ]
                ];
            }
        } else {
            $response   =   $response->withStatus(400);
            $message    =   ['message'   =>  'Bad Request'];
        }

        $response_body->write(json_encode($message));
        return $response;
    }
}
