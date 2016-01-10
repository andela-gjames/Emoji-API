<?php

namespace BB8\Emoji\Models;

class Emoji extends BaseModel
{
    protected $fillable = ['name', 'char', 'category', 'created_at', 'updated_at'];

    public function user()
    {
        return $this->belongsTo("BB8\Emoji\Models\User");
    }

    public function keywords()
    {
        return $this->hasMany("BB8\Emoji\Models\EmojiKeyword");
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($emoji) {
             $emoji->keywords()->delete();
        });
    }
}
