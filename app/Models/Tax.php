<?php

namespace App\Models;

class Tax extends BaseModel
{
    protected $fillable = [
        'name', 'rate', 'created_by'
    ];
}
