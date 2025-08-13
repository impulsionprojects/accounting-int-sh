<?php

namespace App\Models;

class EmailTemplate extends BaseModel
{
    protected $fillable = [
        'name',
        'from',
        'slug',
        'created_by',
    ];

    public function template()
    {
        return $this->hasOne('App\Models\UserEmailTemplate', 'template_id', 'id')->where('user_id', '=', \Auth::user()->id);
    }
}
