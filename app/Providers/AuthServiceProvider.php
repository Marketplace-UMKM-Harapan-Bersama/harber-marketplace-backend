<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Grant\ClientCredentialsGrant;



class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Passport::routes();

        $this->app->afterResolving(AuthorizationServer::class, function (AuthorizationServer $server) {
        $server->enableGrantType(
            new ClientCredentialsGrant(),
            Passport::tokensExpireIn() ?? now()->addHours(1)
        );
    });
    }
}
