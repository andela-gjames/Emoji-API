<?php
namespace BB8\Emoji\Models;
//use \Illuminate\Database\Eloquent\Model as BaseModel;
use BB8\Emoji\Models;
class EmojiKeyword extends BaseModel
{
    public $timestamps  = false;
    protected $table    = 'keywords';
    protected $fillable = ['name', 'emoji_id'];

    public function emojis()
    {
        return $this->belongsTo("BB8\Emoji\Models\Emoji");
    }
}
