<?php

namespace App\Providers;

use Laravel\Passport\Passport;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Passport::routes();

        //Passport::cookie('custom_name');

        //Define Scope
        Passport::tokensCan([
            'superadmin' => 'Super Admin Role',
            'admin' => 'Admin Role',
            'financial' =>  'Financial Role',
            'support' =>  'Support Role',
            'leader' =>  'Leaders Role',
            'basic' =>  'Basic Role',
            'nodeserver' =>  'Node Server Role'
        ]);

        //Define Scope
        Passport::setDefaultScope([
            'basic'
        ]);
    }
}
