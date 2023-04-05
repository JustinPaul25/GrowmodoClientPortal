<?php

namespace App\Mail;

use App\Models\User;
use App\Models\Organization;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use App\Models\SubscriptionTalent;
use App\Models\SubscriptionPlanType;
use Illuminate\Queue\SerializesModels;

class SubscriptionRequest extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $subscriptionTalent;
    public $subscriptionBilling;
    public $startsOn;
    public $message;
    public $organization;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user, Organization $organization, SubscriptionTalent $subscriptionTalent, SubscriptionPlanType $subscriptionBilling, $startsOn, $message)
    {
        $this->subscriptionTalent = $subscriptionTalent;
        $this->subscriptionBilling = $subscriptionBilling;
        $this->startsOn = $startsOn;
        $this->message = $message;
        $this->user = $user;
        $this->organization = $organization;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.subscription-request', [
                    'user' => $this->user,
                    'organization' => $this->organization,
                    'subscription_talent' => $this->subscriptionTalent,
                    'subscription_billing' => $this->subscriptionBilling,
                    'starts_on' => $this->startsOn,
                    'message' => $this->message,
                ])
                ->subject('Subscription Change Request');
    }
}
