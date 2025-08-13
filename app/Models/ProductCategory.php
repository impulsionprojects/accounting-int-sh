<?php

namespace App\Models;

class ProductCategory extends BaseModel
{
    protected $fillable = [
        'name', 'created_by', 'description',
    ];


    protected $hidden = [

    ];
}
