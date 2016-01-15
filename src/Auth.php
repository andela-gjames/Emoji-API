<?php

namespace BB8\Emoji;

use Carbon\Carbon;
use Firebase\JWT\JWT;

class Auth
{
    /**
     * Checks to see if token is valid
     * @param  string  $token Raw token to validate
     * @param  integer $jit   JWT version
     * @return string  status of the token
     */
    public function isTokenValid($token, $jit)
    {
        $result = null;
        if ($this->isTokenExpired($token) == true) {
            $result = 'Expired';
        } elseif ($this->isTokenJITValid($token, $jit) == false) {
            $result = 'Invalid Token Version';
        }

        return $result;
    }

    /**
     * Check if token is expired
     * @param  string  $token Raw token to validate if expired
     * @return boolean returns true if valid and false if expired
     */
    public function isTokenExpired($token)
    {
        $token = $this->decodeToken($token);

        $exp = date('Y-m-d H:m:s', $token->exp);
        $expireDate = new Carbon($exp);

        return Carbon::now()->gte($expireDate);
    }

    
    /**
     * Checks it JWT version is correct
     * @param  string  $token Raw token
     * @param  integer $jit   Expected JWT version
     * @return boolean true if valid and false if not valid JWT version
     */
    public function isTokenJITValid($token, $jit)
    {
        $token = $this->decodeToken($token);

        return intval($token->jit) === intval($jit);
    }

    /**
     * Decodes the token into an Object
     * @param  string $token Raw token to decode
     * @return object decoded token
     */
    public function decodeToken($token)
    {
        //Check to ensure token is not empty or invalid
        if ($token == '' || $token == null) {
            throw new \Exception('Invalid Token');
        }

        //Remove Bearer if presetn
        $token = str_replace('Bearer ', '', $token);
        
        //Decode token
        $token = JWT::decode($token, getenv('SECRET_KEY'), ['HS256']);

        return $token;
    }
}
