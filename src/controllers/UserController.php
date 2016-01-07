<?php
namespace BB8\Emoji\Controllers;

use Carbon\Carbon;
use Firebase\JWT\JWT;
use BB8\Emoji\Models\User;
use BB8\Emoji\Controllers\BaseController;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class UserController extends BaseController
{
    public function __construct($container)
    {
        parent::__construct($container);

    }

    public function index(ServerRequestInterface $request, ResponseInterface $response)
    {
        return $response;
    }

    public function login(ServerRequestInterface $request, ResponseInterface $response)
    {
        $response           =   $response->withAddedHeader('Content-type', 'application/json');
        $data               =   $request->getParsedBody();
        $response_body      =   $response->getBody();

        if (isset($data['username'], $data['password'])) {
            $user   =   User::auth($data['username'], $data['password']);
            if (!!$user) {
                //Ensures user is not logged out from other device
                $user->jit  =   $user->jit == null ? rand(1000, 999999999) : $user->jit;
                $token      =   $this->buildToken($user->jit, $user->id, $user->username);
                $user->save();
                $message    = $this->getMessage($token, $response);
            } else {
                $message    =   $this->getMessage(static::AUTHENTICATIONERROR, $response, 401);
            }
        } else {
                $message    =   $this->getMessage(static::BADREQUESTERROR, $response, 400);
        }

        $response_body->write(json_encode($message));
        return $response;
    }

    public function logout(ServerRequestInterface $request, ResponseInterface $response)
    {
        $response   =   $response->withAddedHeader('content-type', 'application/json');
        $token      =   $request->getHeader('Authorization');
        $body       =   $response->getBody();
        $token      =   str_replace("Bearer ", "", $token[0]);
        $data       =   JWT::decode($token, getenv('SECRET_KEY'), array('HS256'));

        $user       =   User::find($data->data->uid);

        $message = $this->authenticateRouteRequest($token, $user->jit, $response, 'You have been logged out');

        if($message['status'] === 'success') {
            $user->jit = null;
            $user->save();
        }

        $body->write(json_encode($message));
        return $response;
    }

    private function buildToken($jit, $uid, $username)
    {
        $today      =   Carbon::now();
        $iat        =   strtotime($today->toDateTimeString());
        $exp        =   strtotime($today->addDays(10)->addHours(4)->toDateTimeString());

        $data       =   [
            'jit'   =>  $jit,
            'iat'   =>  $iat,
            'exp'   =>  $exp,
            'data'  =>  [
                            'uid'       =>  $uid,
                            'username'  =>  $username
                        ]
        ];
        $token      =   JWT::encode($data, getenv('SECRET_KEY'));
        return $token;
    }
}
