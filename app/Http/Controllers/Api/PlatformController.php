<?php

namespace App\Http\Controllers\Api;

use App\Helpers\JsonResponse;
use App\Http\Controllers\Controller;
use App\Models\Platform;
use Illuminate\Http\Request;

class PlatformController extends Controller
{
    //


    public function index(Request $request) {
        // $platforms = Platform::all();

        // $formatted = [];

        // // foreach ($platforms as $p => $platform) {
        // //     $formatted[$platform->slug] = $platform->toArray();
        // // }

        // // return JsonResponse::make($formatted);
        // return JsonResponse::make($platforms);



        $perPage = $request->per_page ?? 10;
        // $page = $request->page;

        $search = $request->search;
        $status = $request->status;

        $platforms = Platform::whereRaw('1=1');

        if (! empty($search))
            $platforms->where('title', 'LIKE', '%' . $search . '%');
        if (! empty($status))
            $platforms->where('status', 'LIKE', '%' . $status . '%');

        return JsonResponse::make($platforms->paginate($perPage));
    }
}
