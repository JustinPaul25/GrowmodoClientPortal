<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\User;
use Stripe\StripeClient;
use App\Events\Registered;
use App\Models\Organization;
use App\Models\Subscription;
use Illuminate\Http\Request;
use App\Helpers\JsonResponse;
use Illuminate\Validation\Rule;
use App\Models\SubscriptionPlan;
use App\Models\OrganizationUsers;
use App\Notifications\VerifyEmail;
use App\Events\CompletedSignUpStep3;
use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlanType;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class SignUpController extends Controller
{
    public function step1(Request $request)
    {
        $rules = [
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'email' => 'required|email:rfc,dns|indisposable|unique:users,email',
            'password' => 'required|min:8',
        ];

        // return($request->getSchemeAndHttpHost());

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails())
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, $validator->errors());

        $user = new User();

        $user->firstname = $request->firstname;
        $user->lastname = $request->lastname;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->signup_token = base64_encode(Hash::make($request->email . '|' . $request->firstname . '|' . Carbon::now()));
        $user->signup_step = 'verify-email';
        $user->status = 'pending';


        if ($user->save()) {
            event(new Registered($user, $request->headers->get('origin')));

            // $user->sendEmailVerificationNotification();
            $data = $user->toArray();

            $token = $user->createToken('Password Grant Client')->accessToken;
            $data['token'] = $token;

            activity()
                ->performedOn($user)
                ->causedBy($user)
                ->withProperties(['attributes' => $request->all()])
                ->event('created')
                ->log('User registration - Step 1');

            return JsonResponse::make($data, JsonResponse::SUCCESS);
        } else {
            return JsonResponse::make([], JsonResponse::EXCEPTION, 'Unknown Error');
        }
    }

    public function verifyEmail(Request $request)
    { //, $id, $signup_token) {
        $rules = [
            'signup_token' => 'required|string|max:255|exists:users,signup_token',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails())
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, $validator->errors());


        $user = User::where('signup_token', $request->signup_token)->first();

        if (empty($user)) {
            return JsonResponse::make([], JsonResponse::ATTEMPT_FAILED, 'Verification Link expired');
        }

        // if ($user->signup_token != $request->signup_token) {
        //     return JsonResponse::make([], JsonResponse::ATTEMPT_FAILED, 'Token mismatch with the user ID');
        // }

        $user->email_verified_at = now(); //Carbon::now()->toDateTimeString();
        $user->signup_token = "";
        $user->signup_step = 'step_2';
        $user->status = 'active';

        if ($user->save()) {
            $user->refresh();
            $token = $user->createToken('Password Grant Client')->accessToken;
            $user->roles->makeHidden(['pivot', 'id', 'created_at', 'updated_at', 'guard_name']);
            $userd = $user->toArray();

            unset($userd['roles'], $userd['invite_token'], $userd['employer'], $userd['organization']);

            $userd['role'] = collect($user->getRoleNames())->first();

            $response = [
                'user' => $userd,
                // 'role' => collect($user->getRoleNames())->first(),
                'organization' => xz_org_response($user->id),
                'token' => $token
            ];

            activity()
                ->performedOn($user)
                ->causedBy($user)
                ->event('created')
                ->log('User Registration - Email Verified');

            return JsonResponse::make($response, JsonResponse::SUCCESS, 'Email has been verified');
        }

        return JsonResponse::make([], JsonResponse::EXCEPTION, 'Something went wrong');
    }

    public function resendEmailVerify(Request $request)
    {
        $rules = [
            'email' => 'required|string|max:255|exists:users,email',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails())
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, $validator->errors());


        $user = User::where('email', $request->email)->first();

        if ($user instanceof MustVerifyEmail && !$user->hasVerifiedEmail()) {
            $user->signup_token = base64_encode(Hash::make($request->email . '|' . $request->firstname . '|' . Carbon::now()));
            $user->save();
            $user->refresh();

            $user->notify(new VerifyEmail($request->headers->get('origin')));

            activity()
                ->causedBy($user)
                ->event('verified')
                ->log('Resend Email Verification');

            return JsonResponse::make([], JsonResponse::SUCCESS, "Verification link has been sent!");
        } else {
            return JsonResponse::make([], JsonResponse::ATTEMPT_FAILED, "Email address provided already verified");
        }
    }

    public function step2(Request $request)
    {
        $rules = [
            'business_name' => 'required|string|max:500',
            'country' => 'required|string|max:255',
            'company_type_id' => 'required|integer|exists:company_types,id',
            'organization_role' => [
                'required',
                Rule::in(title_list()),
            ],
            'employee_count_id' => 'required|integer|exists:employee_counts,id',
            'company_website' => 'max:500',
            // 'plan_id' => 'required|integer',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails())
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, $validator->errors());

        $user = $request->user();

        // if ($user->organization()->count() > 0) {
        if (empty($user->signup_step)) {
            return JsonResponse::make([], JsonResponse::TOO_MANY_ATTEMPTS, "Already created an organization");
        }

        $user->assignRole('organization_admin');

        $firstOrg = $user->organization()->first();
        if (empty($firstOrg)) {
            $organization = new Organization();
            $organization->owner_id = $user->id;
        } else {
            $organization = $firstOrg;
        }

        $organization->title = $request->business_name;
        $organization->country = $request->country;
        $organization->website = $request->website;
        $organization->company_type_id = $request->company_type_id;
        $organization->employee_count_id = $request->employee_count_id;

        if ($organization->save()) {
            $user->signup_step = 'step_3';

            $org_user = new OrganizationUsers();
            $org_user->organization_id = $organization->id;
            $org_user->user_id = $user->id;
            $org_user->save();

            $user->organization_role = define_role($request->organization_role);
            $user->title = $request->organization_role;
            $user->save();

            // event(new CompletedSignUpStep3($user, $organization));

            $user->refresh();

            $user->roles->makeHidden(['pivot', 'id', 'created_at', 'updated_at', 'guard_name']);
            // $user->organization;

            $userd = $user->toArray();

            unset($userd['roles'], $userd['invite_token'], $userd['employer'], $userd['organization']);

            $userd['role'] = collect($user->getRoleNames())->first();

            $response = [
                'user' => $userd,
                // 'role' => collect($user->getRoleNames())->first(),
                'organization' => xz_org_response($user->id),
                // 'token' => $token
            ];

            activity()
                ->performedOn($organization)
                ->causedBy($user)
                ->event('created')
                ->log('Create Organization');

            activity()
                ->performedOn($user)
                ->causedBy($user)
                ->withProperties(["attributes" => [
                    "organization_role" => define_role($request->organization_role),
                    "title" => $request->organization_role
                ]])
                ->event('updated')
                ->log('User Registration - Step 2');

            return JsonResponse::make($response, JsonResponse::SUCCESS, 'Organization details updated');
        }
    }

    public function step3(Request $request)
    {
        $rules = [
            'subscription_billing_id' => 'required|integer|exists:subscription_plan_types,id',
            'subscription_talent_id' => 'required|integer|exists:subscription_talent,id',
        ];

        $validator = Validator::make($request->all(), $rules);

        do {
            $payment_token = make_random_token_key();
        } while (User::where("payment_token", "=", $payment_token)->first() instanceof User);

        if ($validator->fails())
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, $validator->errors());

        $user = $request->user();

        if (empty($user->signup_step)) {
            $user->status = 'active';
            $user->save();

            return JsonResponse::make([], JsonResponse::ATTEMPT_FAILED, 'Already finished Step 3.');
        }

        $organization = $user->organization()->first();

        // Subscription
        $billing_type = SubscriptionPlanType::find($request->subscription_billing_id);
        // return $billing_type;
        $subscription = new Subscription();
        $subscription->organization_id = $organization->id;
        $subscription->subscription_plan_type_id = $request->subscription_billing_id;
        $subscription->subscription_talent_id = $request->subscription_talent_id;
        $subscription->subscription_start = now(); //"0000-00-00 00:00:00";
        $subscription->subscription_renewed = now(); //"0000-00-00 00:00:00";
        $subscription->subscription_end = now()->add($billing_type->value, \Str::plural($billing_type->interval_type)); //now(); //"0000-00-00 00:00:00";
        $subscription->status = "pending";
        $subscription->save();

        $user->signup_step = null;
        $user->payment_token = $payment_token;
        $user->save();
        $user->refresh();

        event(new CompletedSignUpStep3($user, $organization));

        $user->roles->makeHidden(['pivot', 'id', 'created_at', 'updated_at', 'guard_name']);
        $userd = $user->toArray();
        unset($userd['roles'], $userd['invite_token'], $userd['employer'], $userd['organization']);
        $userd['role'] = collect($user->getRoleNames())->first();

        $stripe = new StripeClient(
            env('STRIPE_SECRET')
        );

        $product_id = get_product_id($request->subscription_talent_id, $request->subscription_billing_id);

        $base_url = !empty($request->headers->get('origin')) ? $request->headers->get('origin') . '/' : env('FRONTEND_URL', 'https://hub.growmodo.com/');

        $checkout = $stripe->checkout->sessions->create([
            'success_url' => $base_url . 'checkout/success?token=' . $payment_token,
            'cancel_url' => $base_url . 'checkout/cancel',
            'line_items' => [
                [
                    'price' => $product_id,
                    'quantity' => 1,
                ],
            ],
            'metadata' => [
                'organization_id' => $organization->id,
                'user_id' => 1
            ],
            'mode' => 'subscription',
        ]);

        $checkout->success_url = null;

        $response = [
            'user' => $userd,
            // 'role' => collect($user->getRoleNames())->first(),
            'organization' => xz_org_response($user->id),
            'checkout' => $checkout,
        ];

        activity()
            ->performedOn($subscription)
            ->causedBy($user)
            ->event('created')
            ->log('Created subscription - Pending');

        activity()
            ->performedOn($user)
            ->causedBy($user)
            ->event('updated')
            ->log('User Registration - Step 3');

        return JsonResponse::make($response, JsonResponse::SUCCESS);
    }

    public function invitationEmailVerify(Request $request)
    { //, $id, $signup_token) {
        $rules = [
            'invite_token' => 'required|string|max:255|exists:users,invite_token',
        ];
        if (empty($request->invite_token))
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, $validator->errors());

        $invite_token = $request->invite_token;

        $validator = Validator::make(array('invite_token' => $invite_token), $rules);

        if ($validator->fails())
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, $validator->errors());


        $user = User::where('invite_token', $invite_token)->first();

        if (empty($user)) {
            return JsonResponse::make([], JsonResponse::ATTEMPT_FAILED, 'Verification Link expired');
        }

        $user->email_verified_at = now();
        $user->invite_token = "";
        $user->signup_step = null;
        $user->status = "active";

        if ($user->save()) {

            $token = $user->createToken('Password Grant Client')->accessToken;
            $user->roles->makeHidden(['pivot', 'id', 'created_at', 'updated_at', 'guard_name']);
            $userd = $user->toArray();

            unset($userd['roles'], $userd['invite_token'], $userd['employer'], $userd['organization']);

            $userd['role'] = collect($user->getRoleNames())->first();

            $response = [
                'user' => $userd,
                // 'role' => collect($user->getRoleNames())->first(),
                'organization' => xz_org_response($user->id),
                'token' => $token
            ];

            activity()
                ->performedOn($user)
                ->causedBy($user)
                ->event('accept invitation')
                ->log('Invitation Accepted.');

            return JsonResponse::make($response, JsonResponse::SUCCESS, 'Email has been verified');
        }

        return JsonResponse::make([], JsonResponse::EXCEPTION, 'Something went wrong');
    }
}
