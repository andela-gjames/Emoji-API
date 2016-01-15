<?php

namespace BB8\Emoji\Controllers;

use BB8\Emoji\Models\User;
use Carbon\Carbon;
use Firebase\JWT\JWT;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class UserController extends BaseController
{
    public function __construct($container)
    {
        parent::__construct($container);
    }

    /**
     * Homepage route 
     * @param  ServerRequestInterface ServerRequestInterface $request PSR-7 standard for receiving client request
     * @param  ResponseInterface      ResponseInterface      $response     PSR-& standard for sending server response
     * @return ResponseInterface      HTTP response of client request
     */
    public function index(ServerRequestInterface $request, ResponseInterface $response)
    {
        $body = $response->getBody();
        $body->write('<H1>WELCOME TO EMOJICON</H1>');

        return $response;
    }

    /**
     * PUTS route for creating a user
     * @param  ServerRequestInterface ServerRequestInterface $request PSR-7 standard for receiving client request
     * @param  ResponseInterface      ResponseInterface      $response     PSR-& standard for sending server response
     * @return ResponseInterface      HTTP response of client request
     */
    public function create(ServerRequestInterface $request, ResponseInterface $response)
    {
        //Initialize response and get body of response to write to
        $response = $response->withAddedHeader('content-type', 'application/json');
        $data = $request->getParsedBody();
        $body = $response->getBody();

        //Ensure user does not exist already
        $user = User::where('username', '=', $data['username'])->first();
        if ($user == null) {
            //Create user
            User::create(['username' => $data['username'], 'password' => hash('SHA256', $data['password'])]);
            $message = $this->getMessage(static::USERCREATED, $response);
        } else {
            //Return message that user already exists
            $message = $this->getMessage('user already exists', $response, 200);
        }

        $body->write(json_encode($message));

        return $response;
    }

    /**
     * Auth/Login route for logging a user in
     * @param  ServerRequestInterface ServerRequestInterface $request PSR-7 standard for receiving client request
     * @param  ResponseInterface      ResponseInterface      $response     PSR-& standard for sending server response
     * @return ResponseInterface      HTTP response of client request
     */
    public function login(ServerRequestInterface $request, ResponseInterface $response)
    {
        //Initialize response type and get body to write to 
        $response = $response->withAddedHeader('Content-type', 'application/json');
        $data = $request->getParsedBody();
        $response_body = $response->getBody();

        //Check to make sure user sends username and password
        if (isset($data['username'], $data['password'])) {
            //Authenticate that username and password are correct
            $user = User::auth($data['username'], $data['password']);
            //Check if username exits
            if ((bool) $user) {
                //Ensures user is not logged out from other device
                $user->jit = $user->jit == null ? rand(1000, 999999999) : $user->jit;
                //Generate token
                $token = $this->buildToken($user->jit, $user->id, $user->username);
                $user->save();
                $message = ['token' => $token];
            } else {
                //Message when username or password is incorrect
                $message = $this->getMessage(static::AUTHENTICATIONERROR, $response, 401);
            }
        } else {
            //Message when username or password is not set
            $message = $this->getMessage(static::BADREQUESTERROR, $response, 400);
        }

        $response_body->write(json_encode($message));

        return $response;
    }

    /**
     * Auth/Logout route for logging a user out
     * @param  ServerRequestInterface ServerRequestInterface $request PSR-7 standard for receiving client request
     * @param  ResponseInterface      ResponseInterface      $response     PSR-& standard for sending server response
     * @return ResponseInterface      HTTP response of client request
     */
    public function logout(ServerRequestInterface $request, ResponseInterface $response)
    {
        //Intialize response, get body and token
        $response = $response->withAddedHeader('content-type', 'application/json');
        $token = $request->getHeader('Authorization');
        $body = $response->getBody();
        
        //Format token and decode
        $token = str_replace('Bearer ', '', $token[0]);
        $data = JWT::decode($token, getenv('SECRET_KEY'), ['HS256']);

        //Ensure user in token exist and is valid
        $user = User::find($data->data->uid);

        //Authenticate that token is valid
        $message = $this->authenticateRouteRequest($token, $user->jit, $response, 'You have been logged out');

        //If token is valid set jit to null save user
        if ($message['status'] === 'success') {
            $user->jit = null;
            $user->save();
        }

        $body->write(json_encode($message));

        return $response;
    }

  
    /**
     * Builds the token to be served to user during login
     * @param  integer $jit      JIT to verify JWT version
     * @param  integer $uid      User ID
     * @param  string  $username Username of user logged in
     * @return string  JWT token s
     */
    private function buildToken($jit, $uid, $username)
    {
        
        $today = Carbon::now(); //Initialize Time to now
        $iat = strtotime($today->toDateTimeString()); // The time the token was created 
        $exp = strtotime($today->addDays(10)->addHours(4)->toDateTimeString());//Time token will expire

        //Build data
        $data = [
            'jit'   => $jit,
            'iat'   => $iat,
            'exp'   => $exp,
            'data'  => [
                            'uid'       => $uid,
                            'username'  => $username,
                        ],
        ];
        
        //Encode token
        $token = JWT::encode($data, getenv('SECRET_KEY'));

        return $token;
    }
}
