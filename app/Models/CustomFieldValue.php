<?php

namespace App\Models;

class CustomFieldValue extends BaseModel
{
    protected $fillable = [
        'record_id',
        'field_id',
        'value',
    ];
}
