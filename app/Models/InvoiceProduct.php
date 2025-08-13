<?php

namespace App\Models;

class InvoiceProduct extends BaseModel
{
    protected $fillable = [
        'product_id',
        'invoice_id',
        'quantity',
        'tax',
        'discount',
        'total',
        'price',
    ];

    public function product(){
        return $this->hasOne('App\Models\ProductService', 'id', 'product_id')->first();
    }

    public function service(){
        return $this->hasOne('App\Models\ProductService', 'id', 'product_id');
    }

    public function inventory(){
        return $this->hasOne('App\Models\Inventory', 'id', 'product_id')->first();
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

}
