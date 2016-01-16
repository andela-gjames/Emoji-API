<?php

namespace BB8\Emoji;

use Carbon\Carbon;
use Firebase\JWT\JWT;
use BB8\Emoji\Exceptions\JWTException;
use BB8\Emoji\Exceptions\TokenExpirationException;

class Auth
{
    /**
     * Decodes the token into an Object
     * @param  string $token Raw token to decode
     * @return object decoded token
     */
    public static function decodeToken($token)
    {
        $token = trim($token);
        //Check to ensure token is not empty or invalid
        if ($token === '' || $token === null || empty($token)) {
            throw new JWTException('Invalid Token');
        }
        //Remove Bearer if present
        $token = trim(str_replace('Bearer ', '', $token));
        
        //Decode token
        try{
            $token = JWT::decode($token, getenv('SECRET_KEY'), array('HS256'));
        } catch(\Exception $e) {
            throw new JWTException("Invalid Token");
        }
        
        //Ensure JIT is present
        if ($token->jit == null || $token->jit == "") {
            throw new JWTException('Invalid Token');
        }
        
        //Ensure User Id is present
        if ($token->data->uid == null || $token->data->uid == "") {
            throw new JWTException("Invalid Token");
        }
        return $token;
    }
}
