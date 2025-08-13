<?php

namespace App\Models;

class UserEmailTemplate extends BaseModel
{
    protected $fillable = [
        'template_id',
        'user_id',
        'is_active',
    ];
}
