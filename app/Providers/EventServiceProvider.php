<?php

namespace App\Providers;

use App\Events\CompletedSignUpStep3;
use App\Events\Registered;
use App\Listeners\SendEmailToSales;
use App\Listeners\SendEmailVerificationEmail;
// use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            // SendEmailVerificationNotification::class,
            SendEmailVerificationEmail::class
        ],
        CompletedSignUpStep3::class => [
            SendEmailToSales::class,
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
