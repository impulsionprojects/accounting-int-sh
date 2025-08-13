<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

    }

    /**
     * Get the current company ID.
     */
    public function getCompanyId()
    {
        return session('current_company_id') ?? null;
    }
}
