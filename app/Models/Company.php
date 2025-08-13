<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Company extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'name',
        'tax_number',
        'address',
        'phone',
        'email',
        'currency',
        'timezone'
    ];

    /**
     * The users that belong to the company.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'company_user');
    }
}
