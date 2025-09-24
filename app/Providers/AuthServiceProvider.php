<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Passport;
use Illuminate\Support\Facades\DB;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        $temp = DB::table('system_config')->where('sys_key', 'token_expire (hours)')->get();
        $expire = isset($temp) && !empty($temp) ? $temp[0]->sys_value : 1;

        Passport::routes();
        Passport::personalAccessTokensExpireIn(now()->addHours(1));
        Passport::refreshTokensExpireIn(now()->addDays(7));
    }
}
