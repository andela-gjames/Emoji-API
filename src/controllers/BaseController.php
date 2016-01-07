<?php
namespace BB8\Emoji\Controllers;
use BB8\Emoji\Database\Connection;

class BaseController
{
    const AUTHENTICATIONERROR   = 'Username or password incorrect';
    const BADREQUESTERROR       = 'Bad request';

    protected $auth;
    public function __construct($container)
    {
        $this->auth = $container->get('auth');
        $dotenv = new \Dotenv\Dotenv(__DIR__.'/../../');
        $dotenv->load();

        $connection = new Connection();
    }

    protected function authenticateRouteResquest($token, $jit, &$response, $successMessage)
    {
        $result = $this->auth->isTokenValid($token, $jit);
        switch($result) {
            case 'Expired':
                $response = $response->withStatus(401);
                $message    =   [
                        'status' => 'Error',
                        'message' => 'Unathorized Access: Token has expired'
                    ];
                return $message;
            case 'Invalid Token Version':
                $response = $response->withStatus(401);
                $message    =   [
                        'status' => 'Error',
                        'message' => 'Unathorized Access: Usage of wrong token version'
                    ];
                return $message;
            default:
                $message    =   [
                        'status' => 'success',
                        'message' => $successMessage
                    ];
                return $message;
        }
    }

    protected function getMessage($message, &$response, $errorStatus = null)
    {
        $type = ($errorStatus == 200 || $errorStatus == null) ? 'success' : 'error';
        $response = ($errorStatus == null) ? $response->withStatus(200) : $response->withStatus($errorStatus);
        return [
            'status'    => $type,
            'message'   => $message
        ];
    }
}
