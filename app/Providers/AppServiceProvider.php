<?php

namespace App\Providers;

use App\Models\AccountRequest;
use App\Models\Solicitud;
use App\Models\User;
use App\Policies\AccountRequestPolicy;
use App\Policies\SolicitudPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use SocialiteProviders\Azure\Provider;
use SocialiteProviders\Manager\SocialiteWasCalled;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(Solicitud::class, SolicitudPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(AccountRequest::class, AccountRequestPolicy::class);

        Event::listen(function (SocialiteWasCalled $event): void {
            $event->extendSocialite('azure', Provider::class);
        });
    }
}
