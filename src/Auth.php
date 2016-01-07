<?php
namespace BB8\Emoji;

use Firebase\JWT\JWT;
use Carbon\Carbon;
class Auth
{
    public function isTokenValid($token, $jit)
    {
        $result = null;
        if($this->isTokenExpired($token) == true) {
            $result = "Expired";
        } elseif($this->isTokenJITValid($token, $jit) == false) {
            $result = "Invalid Token Version";
        }
        return $result;
    }

    public function isTokenExpired($token)
    {
        $token          =   $this->decodeToken($token);

        $exp            =   date('Y-m-d H:m:s', $token->exp);
        $expireDate     =   new Carbon($exp);

        return Carbon::now()->gte($expireDate);
    }

    public function isTokenJITValid($token, $jit)
    {
        $token  =   $this->decodeToken($token);

        return intval($token->jit) === intval($jit);
    }

    public function decodeToken($token)
    {

        if($token == '' || $token == null) {
            throw new \Exception("Invalid Token");
        }

        $token             =   str_replace("Bearer ", "", $token);
        $token          =   JWT::decode($token, getenv('SECRET_KEY'), array('HS256'));

        return $token;
    }
}
