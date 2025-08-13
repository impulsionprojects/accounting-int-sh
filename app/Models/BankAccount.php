<?php

namespace App\Models;

class BankAccount extends BaseModel
{
    protected $fillable = [
        'holder_name',
        'bank_name',
        'account_number',
        'opening_balance',
        'contact_number',
        'bank_address',
        'account',
        'created_by',
    ];

    public function chartOfAccount()
    {
        return $this->hasOne('App\Models\ChartOfAccount', 'id', 'account');
    }

}

