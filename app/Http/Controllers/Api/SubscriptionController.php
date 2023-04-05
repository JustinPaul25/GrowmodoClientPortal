<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use App\Models\Organization;
use App\Models\Subscription;
use Illuminate\Http\Request;
use App\Helpers\JsonResponse;
use App\Models\SubscriptionPlan;
use App\Mail\SubscriptionRequest;
use App\Models\OrganizationUsers;
use App\Models\SubscriptionTalent;
use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlanType;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class SubscriptionController extends Controller
{
    //

    public function plans(Request $request) {
        return JsonResponse::make(SubscriptionPlan::all());
    }

    public function planTypes(Request $request) {
        return JsonResponse::make(SubscriptionPlanType::all());
    }

    public function maintenance(Request $request){
        $current_user = $request->user();
        if (empty ($current_user->id))
            return JsonResponse::make([], JsonResponse::UNAUTHORIZED, 'Unauthorized to use this method.');

        if($current_user->roles->first()->name == 'organization_admin'){
            $organization = Organization::where( 'owner_id', 'LIKE', '' . $current_user->id . '' )->get()->first();

            if( empty($organization->id) )
                return JsonResponse::make([], JsonResponse::NOT_FOUND, 'Organization is not found.');

            $org_id = $organization->id;
        } else{
            $org_user = OrganizationUsers::where( 'user_id', 'LIKE', '' . $current_user->id . '' )->get()->first();

            if (empty ($org_user->id))
                return JsonResponse::make([], JsonResponse::NOT_FOUND, 'Organization is not found.');

            $org_id = $org_user->organization_id;
        }

        $subscription = Subscription::where( 'organization_id', 'LIKE', '' . $org_id . '' )->get()->first();

        if (empty ($subscription->id))
            return JsonResponse::make([], JsonResponse::NOT_FOUND, 'Subscription is not found.');

        $subscription->is_maintenance = true;

        if ($subscription->save()) {
            $subscription->refresh();
            return JsonResponse::make($subscription, JsonResponse::SUCCESS,  'Successfully changed to maintenance plan');
        } else{
            return JsonResponse::make([], JsonResponse::EXCEPTION, 'Unknown Error');
        }
    }

    public function pause(Request $request){
        $rules = [
            'pause_duration' => 'required|string|max:50',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails())
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, $validator->errors());

        $current_user = $request->user();
        if (empty ($current_user->id))
            return JsonResponse::make([], JsonResponse::UNAUTHORIZED, 'Unauthorized to use this method.');

        if($current_user->roles->first()->name == 'organization_admin'){
            $organization = Organization::where( 'owner_id', 'LIKE', '' . $current_user->id . '' )->get()->first();

            if( empty($organization->id) )
                return JsonResponse::make([], JsonResponse::NOT_FOUND, 'Organization is not found.');

            $org_id = $organization->id;
        } else{
            $org_user = OrganizationUsers::where( 'user_id', 'LIKE', '' . $current_user->id . '' )->get()->first();

            if (empty ($org_user->id))
                return JsonResponse::make([], JsonResponse::NOT_FOUND, 'Organization is not found.');

            $org_id = $org_user->organization_id;
        }

        $subscription = Subscription::where( 'organization_id', 'LIKE', '' . $org_id . '' )->get()->first();

        if (empty ($subscription->id))
            return JsonResponse::make([], JsonResponse::NOT_FOUND, 'Subscription is not found.');

        if ($subscription->status !== 'active')
            return JsonResponse::make([], JsonResponse::UNAUTHORIZED, 'Unauthorized to use this method.');

        $duration = explode(" ", $request['pause_duration']);

        $duration[1] = strtolower($duration[1]);

        $days = $duration[1] === 'month' || $duration[1] === 'months' ? $duration[0] * 30 : $duration[0] * 7;

        $subscription->status = 'paused';
        $subscription->paused_on = now();
        $subscription->subscription_end = CarbonImmutable::parse($subscription->subscription_end)->add($days, 'days');
        $subscription->pause_duration = $request['pause_duration'];

        if ($subscription->save()) {
            $subscription->refresh();
            return JsonResponse::make($subscription, JsonResponse::SUCCESS,  'Subscription has been paused');
        } else{
            return JsonResponse::make([], JsonResponse::EXCEPTION, 'Unknown Error');
        }
    }

    public function resume()
    {
        $current_user = auth()->user();
        if (empty ($current_user))
            return JsonResponse::make([], JsonResponse::UNAUTHORIZED, 'Unauthorized to use this method.');

        if($current_user->roles->first()->name == 'organization_admin'){
            $organization = Organization::where( 'owner_id', 'LIKE', '' . $current_user->id . '' )->get()->first();

            if( empty($organization->id) )
                return JsonResponse::make([], JsonResponse::NOT_FOUND, 'Organization is not found.');

            $org_id = $organization->id;
        } else{
            $org_user = OrganizationUsers::where( 'user_id', 'LIKE', '' . $current_user->id . '' )->get()->first();

            if (empty ($org_user->id))
                return JsonResponse::make([], JsonResponse::NOT_FOUND, 'Organization is not found.');

            $org_id = $org_user->organization_id;
        }

        $subscription = Subscription::where( 'organization_id', 'LIKE', '' . $org_id . '' )->get()->first();

        if (empty ($subscription->id))
            return JsonResponse::make([], JsonResponse::NOT_FOUND, 'Subscription is not found.');

        if ($subscription->status !== 'paused')
            return JsonResponse::make([], JsonResponse::UNAUTHORIZED, 'Unauthorized to use this method.');

        $start = Carbon::parse($subscription->paused_on);
        $finish = now();

        $total_duration = $finish->diffInDays($start);

        $duration = explode(" ", $subscription->pause_duration);

        $duration[1] = strtolower($duration[1]);

        $days = $duration[1] === 'month' || $duration[1] === 'months' ? 30 * $duration[0] : 7 * $duration[0];

        $days = $days - $total_duration;

        $subscription->status = 'active';
        $subscription->paused_on = null;
        $subscription->pause_duration = null;
        $subscription->subscription_end = CarbonImmutable::parse($subscription->subscription_end)->sub($days, 'days');

        if ($subscription->save()) {
            $subscription->refresh();
            return JsonResponse::make($subscription, JsonResponse::SUCCESS,  'Subscription has been resumed');
        } else{
            return JsonResponse::make([], JsonResponse::EXCEPTION, 'Unknown Error');
        }
    }

    public function request(Request $request)
    {
        $rules = [
            'subscription_talent_id' => 'required|integer',
            'subscription_billing_id' => 'required|integer',
            'starts_on' => 'required'
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails())
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, $validator->errors());

        $current_user = auth()->user();
        if (empty ($current_user->id))
            return JsonResponse::make([], JsonResponse::UNAUTHORIZED, 'Unauthorized to use this method.');

        if($current_user->roles->first()->name == 'organization_admin'){
            $organization = Organization::where( 'owner_id', 'LIKE', '' . $current_user->id . '' )->get()->first();

            if( empty($organization->id) )
                return JsonResponse::make([], JsonResponse::NOT_FOUND, 'Organization is not found.');

            $org_id = $organization->id;
        } else{
            $org_user = OrganizationUsers::where( 'user_id', 'LIKE', '' . $current_user->id . '' )->get()->first();

            if (empty ($org_user->id))
                return JsonResponse::make([], JsonResponse::NOT_FOUND, 'Organization is not found.');

            $org_id = $org_user->organization_id;

            $organization = Organization::where( 'id', $org_id)->first();
        }

        $subscription = Subscription::where( 'organization_id', 'LIKE', '' . $org_id . '' )->get()->first();

        if (empty ($subscription->id))
            return JsonResponse::make([], JsonResponse::NOT_FOUND, 'Subscription is not found.');

        $subscriptionTalent = SubscriptionTalent::find($request->input('subscription_talent_id'));
        $subscriptionBilling = SubscriptionPlanType::find($request->input('subscription_billing_id'));

        Mail::to('roman@growmodo.com')->send(new SubscriptionRequest(
            $current_user,
            $organization,
            $subscriptionTalent,
            $subscriptionBilling,
            $request->input('starts_on'),
            $request->input('message'),
        ));

        return JsonResponse::make([], JsonResponse::SUCCESS, 'Request Sent');
    }
}
