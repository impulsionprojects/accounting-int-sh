<?php

namespace App\Models;

use App\Traits\CompanyScoped;

class Expense extends BaseModel
{
    use CompanyScoped;

    protected $fillable = [
        'category_id','description','amount','date','project_id','user_id','attachment','created_by'
    ];

    public function category(){
        return $this->hasOne('App\Models\ExpensesCategory','id','category_id');
    }
    public function projects(){
        return $this->hasOne('App\Models\Projects','id','project');
    }
    public function user(){
        return $this->hasOne('App\Models\User','id','user_id');
    }
}
