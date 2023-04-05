<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Option;
use App\Models\Payment;
use Stripe\StripeClient;
use App\Helpers\AsanaApi;
use Illuminate\Support\Str;
use App\Models\Organization;
use App\Models\Subscription;
use Illuminate\Http\Request;
use App\Helpers\JsonResponse;
use Illuminate\Support\Facades\Response;

class WebhookController extends Controller
{
    var $asanaApi = null;


    public function __construct()
    {
        $this->asanaApi = new AsanaApi();
    }
    public function handleWebhook(Request $request)
    {
        $payload = json_decode($request->getContent(), true);
        // $method = 'handle' . Str::studly(str_replace('.', '_', $payload['type']));

        if ($payload['type'] === "checkout.session.completed") {
            $user = User::find($payload['data']['object']['metadata']['user_id']);
            $organization = Organization::find($payload['data']['object']['metadata']['organization_id']);
            $subscription = Subscription::where('organization_id', $organization->id)->first();

            $subscription->update([
                'status' => 'active',
                'customer_id' => $payload['data']['object']['customer'],
            ]);

            $organization->update([
                'stripe_subscription_id' => $payload['data']['object']['subscription']
            ]);

            Payment::create([
                'user_id' => $user->id,
                'stripe_id' => $payload['data']['object']['customer'],
                'subtotal' => $payload['data']['object']['amount_subtotal'],
                'total' => $payload['data']['object']['amount_total'],
            ]);

            return 'Organization Updated';
        }
    }
}
