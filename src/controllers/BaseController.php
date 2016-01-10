<?php
namespace BB8\Emoji\Controllers;
use BB8\Emoji\Database\Connection;

class BaseController
{
    const INVALIDTOKENVERSIONERROR = "Usage of wrong token version";
    const LOGINTOACCESSERROR    = "Unauthorized: Login to access";
    const USERDOESNOTEXISTERROR = 'User does not exist';
    const AUTHENTICATIONERROR   = 'Username or password incorrect';
    const TOKENEXPIREDERROR     = 'Token has expired, login to access';
    const BADREQUESTERROR       = 'Bad request';
    const EMOJINOTFOUNDERROR    = 'Emoji not found';
    const EMOJICREATED          = 'Emoji created sucessfully';
    const EMOJIUPDATED          = 'Emoji updated';
    const USERCREATED           = 'User created';


    protected $auth;

    public function __construct($container)
    {
        $this->auth = $container->get('auth');
    }

    protected function authenticateRouteRequest($token, $jit, &$response, $successMessage, $status = 200, $raw = false)
    {
        $result = $this->auth->isTokenValid($token, $jit);
        switch($result) {
            case 'Expired':
                $message    =  $this->getMessage(static::TOKENEXPIREDERROR, $response, 401);
                return $message;
            case 'Invalid Token Version':
                $response = $response->withStatus(401);
                $message    =  $this->getMessage(static::INVALIDTOKENVERSIONERROR, $response, 401);
                return $message;
            default:
                $message    =  $this->getMessage($successMessage, $response, $status);
                return $message;
        }
    }

    protected function getMessage($message, &$response, $errorStatus = null, $raw = false)
    {
        $type = ($errorStatus == 200 || $errorStatus == 201 || $errorStatus == null) ? 'success' : 'error';
        $response = ($errorStatus == null) ? $response->withStatus(200) : $response->withStatus($errorStatus);

        if(!$raw) {
            $message = [
                'status'    => $type,
                'message'   => $message
            ];
        }

        return $message;
    }
}
