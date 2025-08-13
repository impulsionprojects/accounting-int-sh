<?php

namespace App\Models;

class ContractComment extends BaseModel
{
    protected $fillable = [
        'contract_id',
        'comment',
        'created_by',
        'type',
    ];

    public function client()
    {
        return $this->hasOne('App\Models\Customer', 'id', 'created_by');
    }
}
