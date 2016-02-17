<?php

namespace BB8\Emoji\Models;

class BaseModel extends \Illuminate\Database\Eloquent\Model
{
    public function __construct(array $attribute = [])
    {
        parent::__construct($attribute);
    }
}
