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
     * Homepage route.
     *
     * @param ServerRequestInterface ServerRequestInterface $request  PSR-7 standard for receiving client request
     * @param ResponseInterface      ResponseInterface      $response PSR-& standard for sending server response
     *
     * @return ResponseInterface HTTP response of client request
     */
    public function index(ServerRequestInterface $request, ResponseInterface $response)
    {
        $body = $response->getBody();
        $body->write('<H1>WELCOME TO EMOJICON</H1>');
    }

    /**
     * PUTS route for creating a user.
     *
     * @param ServerRequestInterface ServerRequestInterface $request  PSR-7 standard for receiving client request
     * @param ResponseInterface      ResponseInterface      $response PSR-& standard for sending server response
     *
     * @return ResponseInterface HTTP response of client request
     */
    public function create(ServerRequestInterface $request, ResponseInterface $response)
    {
        //Get the parsed data
        $data = $request->getParsedBody();

        //Ensure user does not exist already
        $user = User::where('username', '=', $data['username'])->first();
        if ($user == null) {
            //Create user
            User::create(['username' => $data['username'], 'password' => hash('SHA256', $data['password'])]);
            $message = ['message' => 'User created'];
        } else {
            //Return message that user already exists
            $response = $response->withStatus(409);
            $message = ['message' => 'User already exists'];
        }

        $response->getBody()->write(json_encode($message));

        return $response;
    }

    /**
     * Auth/Login route for logging a user in.
     *
     * @param ServerRequestInterface ServerRequestInterface $request  PSR-7 standard for receiving client request
     * @param ResponseInterface      ResponseInterface      $response PSR-& standard for sending server response
     *
     * @return ResponseInterface HTTP response of client request
     */
    public function login(ServerRequestInterface $request, ResponseInterface $response)
    {
        $data = $request->getParsedBody();

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
                $response = $response->withStatus(401);
                $message = ['message' => 'username or password incorrect'];
            }
        } else {
            $response = $response->withStatus(400);
            $message = ['message' => 'Username or password not set'];
        }

        $response->getBody()->write(json_encode($message));

        return $response;
    }

    /**
     * Auth/Logout route for logging a user out.
     *
     * @param ServerRequestInterface ServerRequestInterface $request  PSR-7 standard for receiving client request
     * @param ResponseInterface      ResponseInterface      $response PSR-& standard for sending server response
     *
     * @return ResponseInterface HTTP response of client request
     */
    public function logout(ServerRequestInterface $request, ResponseInterface $response)
    {
        //Get the token, format and decode
        $token = $request->getHeader('Authorization');
        $token = str_replace('Bearer ', '', $token[0]);
        $data = JWT::decode($token, getenv('SECRET_KEY'), ['HS256']);

        //Ensure user in token exist and is valid
        $user = User::find($data->data->uid);

        $user->jit = null;
        $user->save();

        $response->getBody()->write(json_encode(['message' => 'user has been logged out']));

        return $response;
    }

    /**
     * Builds the token to be served to user during login.
     *
     * @param int    $jit      JIT to verify JWT version
     * @param int    $uid      User ID
     * @param string $username Username of user logged in
     *
     * @return string JWT token s
     */
    private function buildToken($jit, $uid, $username)
    {
        $today = Carbon::now(); //Initialize Time to now
        $iat = strtotime($today->toDateTimeString()); // The time the token was created
        $exp = strtotime($today->addDays(10)->addHours(4)->toDateTimeString()); //Time token will expire

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
