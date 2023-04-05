<?php

namespace App\Http\Controllers\Api\Admin;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use App\Models\Organization;
use App\Models\Subscription;
use Illuminate\Http\Request;
use App\Helpers\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class SubscriptionController extends Controller
{
    public function approveRequest(Request $request)
    {
        $rules = [
            'organization_id' => 'required|exists:organizations,id',
            'subscription_talent_id' => 'required|integer',
            'subscription_billing_id' => 'required|integer',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails())
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, $validator->errors());

        $current_user = $request->user();
        if (empty ($current_user->id))
            return JsonResponse::make([], JsonResponse::UNAUTHORIZED, 'Unauthorized to use this method.');

        $subscription = Subscription::where( 'organization_id', 'LIKE', '' . $request->input('organization_id') . '' )->get()->first();

        if (empty ($subscription->id))
            return JsonResponse::make([], JsonResponse::NOT_FOUND, 'Subscription is not found.');

        $subscription->subscription_talent_id = $request->input('subscription_talent_id');
        $subscription->subscription_plan_type_id = $request->input('subscription_billing_id');

        if($subscription->update()) {
            return JsonResponse::make($subscription, JsonResponse::SUCCESS);
        } else {
            return JsonResponse::make([], JsonResponse::EXCEPTION, 'Unknown Error');
        }
    }

    public function cancel(Request $request)
    {
        $rules = [
            'organization_id' => 'required|exists:organizations,id',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails())
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, $validator->errors());

        $subscription = Subscription::where( 'organization_id', 'LIKE', '' . $request->input('organization_id') . '' )->get()->first();

        if (empty ($subscription->id))
            return JsonResponse::make([], JsonResponse::NOT_FOUND, 'Subscription is not found.');

        $current_user = $request->user();
        if (empty ($current_user->id))
            return JsonResponse::make([], JsonResponse::UNAUTHORIZED, 'Unauthorized to use this method.');

        $subscription->status = 'canceled';

        if($subscription->update()) {
            return JsonResponse::make($subscription, JsonResponse::SUCCESS);
        } else {
            return JsonResponse::make([], JsonResponse::EXCEPTION, 'Unknown Error');
        }
    }

    public function resume(Request $request)
    {
        $rules = [
            'organization_id' => 'required|exists:organizations,id',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails())
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, $validator->errors());

        $subscription = Subscription::where( 'organization_id', 'LIKE', '' . $request->input('organization_id') . '' )->get()->first();

        if (empty ($subscription->id))
            return JsonResponse::make([], JsonResponse::NOT_FOUND, 'Subscription is not found.');

        if ($subscription->status !== 'paused')
            return JsonResponse::make([], JsonResponse::UNAUTHORIZED, 'Unauthorized to use this method.');

        $current_user = $request->user();
        if (empty ($current_user->id))
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

    public function maintenance(Request $request)
    {
        $rules = [
            'organization_id' => 'required|exists:organizations,id',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails())
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, $validator->errors());

        $subscription = Subscription::where( 'organization_id', 'LIKE', '' . $request->input('organization_id') . '' )->get()->first();

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

}
