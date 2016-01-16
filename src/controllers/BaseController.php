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
}
