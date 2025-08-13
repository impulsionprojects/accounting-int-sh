<?php

namespace App\Models;

use App\Traits\CompanyScoped;

class JournalItem extends BaseModel
{
//    use CompanyScoped;

    protected $fillable = [
        'journal',
        'account',
        'debit',
        'credit',
    ];

    public function accounts()
    {
        return $this->hasOne('App\Models\ChartOfAccount', 'id', 'account');
    }


}
