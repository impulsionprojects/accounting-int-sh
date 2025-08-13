<?php

namespace App\Models;

use App\Traits\CompanyScoped;

class RetainerPayment extends BaseModel
{
    use CompanyScoped;

    protected $fillable = [
        'retainer_id',
        'date',
        'amount',
        'account_id',
        'payment_method',
        'order_id',
        'currency',
        'txn_id',
        'payment_type',
        'receipt',
        'reference',
        'description',

    ];
    public function bankAccount()
    {
        return $this->hasOne('App\Models\BankAccount', 'id', 'account_id');
    }
}
