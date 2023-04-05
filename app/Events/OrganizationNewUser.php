<?php

namespace App\Events;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class OrganizationNewUser implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;

    public $organization;
    public function __construct(User $user, Organization $organization)
    {
        $this->user = $user->makeHidden(['roles','invite_token'])->toArray();
        $this->user['role'] = collect($user->getRoleNames())->first();
        $this->organization = $organization;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('organization.'.$this->organization->id);
    }
}
