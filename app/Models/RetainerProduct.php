<?php

namespace App\Models;

use App\Traits\CompanyScoped;

class RetainerProduct extends BaseModel
{
    use CompanyScoped;

    protected $fillable = [
        'product_id',
        'retainer_id',
        'quantity',
        'tax',
        'discount',
        'total',
    ];

    public function product()
    {
        return $this->hasOne('App\Models\ProductService', 'id', 'product_id');
    }

}
