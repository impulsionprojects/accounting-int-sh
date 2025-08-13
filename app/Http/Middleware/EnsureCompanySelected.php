<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureCompanySelected
{
    /**
     * Ensure the company will be set in the session if not already set.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            // If no company is selected in session and user belongs to companies
            if (!session()->has('current_company_id') && Auth::user()->companies()->count() > 0) {
                $company = Auth::user()->companies()->first();
                session(['current_company_id' => $company->id]);
            }

            // If user doesn't belong to the company in session, reset it
            if (session()->has('current_company_id') &&
                !Auth::user()->belongsToCompany(session('current_company_id'))) {
                session()->forget('current_company_id');

                // Set the first available company as current
                $company = Auth::user()->companies()->first();
                if ($company) {
                    session(['current_company_id' => $company->id]);
                }
            }
        }

        return $next($request);
    }
}
