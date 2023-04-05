<?php

namespace App\Listeners;

use App\Notifications\EmailSales;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class SendEmailToSales
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
        Notification::route('mail', [
            'roman@growmodo.com' => 'Roman Paust',
        ])->notify(new EmailSales($event->user, $event->organization));
    }
}
