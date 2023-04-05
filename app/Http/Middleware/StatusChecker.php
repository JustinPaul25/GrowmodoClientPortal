<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Organization;
use Illuminate\Http\Request;
use App\Helpers\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Exceptions\UnauthorizedException;

class StatusChecker
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        return $next($request); //disables this middleware

        if (Auth::user()) {
            $user = Auth::user();

            if($user->roles->first() === 'superadmin')
                return $next($request);

            if($user->status !== 'active')
                return JsonResponse::make([], JsonResponse::FORBIDDEN, 'User is not active.');

            $organization = $user->organization->first();
            $subscription = $organization->subscriptions;

            // if($organization->status !== 'active')
            //     return JsonResponse::make([], JsonResponse::FORBIDDEN, 'Organization is not active.');

            if($subscription) {
                if($subscription->status !== 'active') {
                    return JsonResponse::make([], JsonResponse::FORBIDDEN, 'No active subscription.');
                } else {
                    return $next($request);
                }
            }
            return JsonResponse::make([], JsonResponse::FORBIDDEN, 'No active subscription.');
        }

        throw UnauthorizedException::notLoggedIn();
    }
}
