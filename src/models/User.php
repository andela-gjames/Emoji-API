<?php

namespace BB8\Emoji\Models;

use BB8\Emoji\Exceptions\JWTException;
use BB8\Emoji\Exceptions\TokenExpirationException;
use Carbon\Carbon;
use Firebase\JWT\JWT;
use Illuminate\Database\Eloquent\Model as BaseModel;

class User extends BaseModel
{
    protected $fillable = ['username', 'password', 'jit'];

    public function emojis()
    {
        return $this->hasMany('BB8\Emoji\Models\Emoji');
    }

    public static function auth($username, $password)
    {
        $user = static::where('username', '=', $username)->first();

        if (isset($user->exists) && $user->exists) {
            if (strcmp(hash('sha256', $password), $user->password) == 0) {
                return $user;
            }
        }

        return false;
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($user) {
             $user->emojis()->delete();
        });
    }

    /**
     * Checks to see if token is valid.
     *
     * @param string $token Decoded token to validate
     * @param int    $jit   JWT version
     *
     * @return string status of the token
     */
    public function isTokenValid($token)
    {
        if (static::isTokenExpired($token)) {
            throw new TokenExpirationException('Token is expired, login to access', 401);
        } elseif (static::isTokenJITValid($token, $this->jit) == false) {
            throw new JWTException('Wrong token version', 401);
        }

        return true;
    }

    /**
     * Check if token is expired.
     *
     * @param string $token Decoded token to validate if expired
     *
     * @return bool returns true if valid and false if expired
     */
    public static function isTokenExpired($token)
    {
        $exp = date('Y-m-d H:m:s', $token->exp);
        $expireDate = new Carbon($exp);

        return Carbon::now()->gte($expireDate);
    }

    /**
     * Checks it JWT version is correct.
     *
     * @param string $token Decoded token
     * @param int    $jit   Expected JWT version
     *
     * @return bool true if valid and false if not valid JWT version
     */
    public static function isTokenJITValid($token, $jit)
    {
        return intval($token->jit) === intval($jit);
    }
}
