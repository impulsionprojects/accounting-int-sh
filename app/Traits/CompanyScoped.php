<?php

namespace App\Traits;

use App\Models\Company;
use Illuminate\Database\Eloquent\Builder;

trait CompanyScoped
{
    /**
     * Boot the trait.
     *
     * @return void
     */
    protected static function bootCompanyScoped()
    {
        // Add a global scope that filters by company_id
        static::addGlobalScope('company', function (Builder $builder) {
            if (session()->has('current_company_id')) {
                $companyId = session('current_company_id');
                $builder->where('company_id', $companyId);
            }
        });

        // Set company_id automatically on model creation
        static::creating(function ($model) {
            if (empty($model->company_id)) {
                $companyId = session('current_company_id');

                if ($companyId) {
                    $model->company_id = $companyId;
                }
            }
        });
    }

    /**
     * Define the company relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Scope a query to include all companies (remove the company filter).
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAllCompanies(Builder $query)
    {
        return $query->withoutGlobalScope('company');
    }

    /**
     * Scope a query to a specific company.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $companyId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForCompany(Builder $query, $companyId)
    {
        return $query->withoutGlobalScope('company')->where('company_id', $companyId);
    }
}
