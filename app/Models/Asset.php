<?php

namespace App\Models;

class Asset extends BaseModel
{
    protected $fillable = [
        'name',
        'purchase_date',
        'supported_date',
        'amount',
        'description',
        'created_by',
    ];

}
