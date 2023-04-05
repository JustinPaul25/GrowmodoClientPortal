<?php

namespace App\Listeners;

use App\Notifications\InviteTokenVerifyEmail;
use App\Notifications\VerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendEmailVerificationEmail
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        //
        if ($event->user instanceof MustVerifyEmail && ! $event->user->hasVerifiedEmail()) {

            if( !empty ($event->user->invite_token) ){
                $event->user->notify(new InviteTokenVerifyEmail($event->httpOrigin));
            } else{
                $event->user->notify(new VerifyEmail($event->httpOrigin));
            }

        }
    }
}
