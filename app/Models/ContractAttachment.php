<?php

namespace App\Models;

class ContractAttachment extends BaseModel
{
    protected $fillable = [
        'contract_id',
        'files',
        'created_by',
        'type',
    ];
}
