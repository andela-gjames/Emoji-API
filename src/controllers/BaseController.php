<?php

namespace BB8\Emoji\Controllers;

class BaseController
{
    //Create message constants to be inherited and used by all controllers extending the base controller
    const INVALIDTOKENVERSIONERROR = 'Usage of wrong token version';
    const LOGINTOACCESSERROR = 'Unauthorized: Login to access';
    const USERDOESNOTEXISTERROR = 'User does not exist';
    const AUTHENTICATIONERROR = 'Username or password incorrect';
    const TOKENEXPIREDERROR = 'Token has expired, login to access';
    const BADREQUESTERROR = 'Bad request';
    const EMOJINOTFOUNDERROR = 'Emoji not found';
    const EMOJICREATED = 'Emoji created sucessfully';
    const EMOJIUPDATED = 'Emoji updated';
    const USERCREATED = 'User created';

    protected $auth;

    /**
     * Initialize controller with dependency container
     * @param Container $container Slim dependency container
     */
    public function __construct($container)
    {
        $this->auth = $container->get('auth');
    }
    
    /**
     * Authenticates access to a route
     * @param  string            $token          JWT token to containing user information
     * @param  integer           $jti            JWT version, for verifying correct token sent
     * @param  ResponseInterface &$response      HTTP response object to send back to requester
     * @param  string            $successMessage Message to return in response
     * @param  integer           [$status        = 200] HTTP status to deliver to user
     * @return array             containing the format of data to return in response
     */
    protected function authenticateRouteRequest($token, $jti, &$response, $successMessage, $status = 200)
    {
        //Checks if token is valid
        $result = $this->auth->isTokenValid($token, $jti);
        
        //Generate message to return based on $result from token varification and $statuss
        switch ($result) {
            case 'Expired':
                $message = $this->getMessage(static::TOKENEXPIREDERROR, $response, 401);
                return $message;
            case 'Invalid Token Version':
                $response = $response->withStatus(401);
                $message = $this->getMessage(static::INVALIDTOKENVERSIONERROR, $response, 401);
                return $message;
            default:
                $message = $this->getMessage($successMessage, $response, $status);
                return $message;
        }
    }

    /**
     * Generates message in the proper format
     * @param  string            $message              message to return
     * @param  ResponseInterface &$response            HTTP response object to send back to requester
     * @param  integer           [$errorStatus         = null] HTTP status to return in response
     * @param  boolean           [$raw                 = false]        Checks if message should be only formated
     * @return [[Type]]          [[Description]]
     */
    protected function getMessage($message, &$response, $errorStatus = null, $raw = false)
    {
        //Set a default status message
        $type = ($errorStatus == 200 || $errorStatus == 201 || $errorStatus == null) ? 'success' : 'error';
        
        //Set the default response status
        $response = ($errorStatus == null) ? $response->withStatus(200) : $response->withStatus($errorStatus);

        //Check if messge needs formating
        if (!$raw) {
            $message = [
                'status'    => $type,
                'message'   => $message,
            ];
        }

        return $message;
    }
}
