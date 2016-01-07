<?php
namespace BB8\Emoji\Models;
use \Illuminate\Database\Eloquent\Model;
use BB8\Emoji\Models\BaseModel;
class Emoji  extends BaseModel
{
    protected $fillable = array('name', 'char', 'category', 'created_at', 'updated_at');

    public function user()
    {
        return $this->belongsTo("BB8\Emoji\Models\User");
    }

    public function keywords()
    {
        return $this->hasMany("BB8\Emoji\Models\EmojiKeyword");
    }
}
