<?php

namespace App\Models;

use App\Traits\CompanyScoped;

class BillPayment extends BaseModel
{
    use CompanyScoped;

    protected $fillable = [
        'bill_id',
        'date',
        'account_id',
        'payment_method',
        'reference',
        'description',
    ];


    public function bankAccount()
    {
        return $this->hasOne('App\Models\BankAccount', 'id', 'account_id');
    }
}
