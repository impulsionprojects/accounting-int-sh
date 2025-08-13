<?php

namespace App\Models;

class EmailTemplateLang extends BaseModel
{
    protected $fillable = [
        'parent_id',
        'lang',
        'subject',
        'content',
    ];
}
