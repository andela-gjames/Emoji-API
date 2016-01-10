<?php

namespace BB8\Emoji\Models;

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
}
