<?php

namespace App\Http\Controllers\Api;

use App\Helpers\JsonResponse;
use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    //
    public function index(Request $request) {
        $perPage = $request->per_page ?? 10;
        // $page = $request->page;

        $search = $request->search;
        // $status = $request->status;

        // $testimonials = testimonial::whereRaw('1=1');
        // if (! auth()->user()->hasRole('superadmin'))
        //     $testimonials = auth()->user()->whereRaw('1=1');
        // else
            $testimonials = Testimonial::whereRaw('1=1');

        // Get current user organization id
        // if (! empty($request->user()->organization)) {
        //     $testimonials->where('organization_id', $request->user()->organization()->first()->id);
        // } elseif (! empty($request->user()->employer)) {
        //     $testimonials->where('organization_id', $request->user()->employer()->first()->organization_id);
        // } elseif (! auth()->user()->hasRole('superadmin')) {

        // } else {
        //     return JsonResponse::make([], JsonResponse::UNAUTHORIZED);
        // }

        if (! empty($search))
            $testimonials->where('comment', 'LIKE', '%' . $search . '%');
        // if (! empty($status))
        //     $testimonials->where('status', 'LIKE', '%' . $status . '%');

        return JsonResponse::make($testimonials->paginate($perPage));
    }
}
