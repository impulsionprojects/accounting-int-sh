<?php

namespace App\Models;

class ContractNote extends BaseModel
{
    protected $fillable = [
        'contract_id',
        'note',
        'created_by',
        'type',
    ];

}
