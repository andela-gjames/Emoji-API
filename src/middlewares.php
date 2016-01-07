<?php

use Carbon\Carbon;
use Firebase\JWT\JWT;

$checkTokenExpireTime   =   function ($request, $response) {
    $dotenv = new \Dotenv\Dotenv(__DIR__.'/../');
    $dotenv->load();

    $raw_token      =   $request->getHeader('Authorization');
    $raw_token      =   str_replace("Bearer ", "", $raw_token[0]);
    $token          =   JWT::decode($raw_token, getenv('SECRET_KEY'), array('HS256'));


    $exp    =   date('Y-m-d H:m:s', $token->exp);
    $expireDate     =   new Carbon($exp);


    return Carbon::now()->gte($expireDate);
};

$mwCheckToken  =  function($request, $response, $next) use($checkTokenExpireTime) {
    if(!$checkTokenExpireTime($request, $response)) {
        $response   =   $response->withStatus(401);
        $response   =   $response->withAddedHeader('Expired', 'true');
    }
    $response   =   $next($request, $response);
    return $response;
};

