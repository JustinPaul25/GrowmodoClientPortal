<?php

namespace App\Http\Controllers\Api;

use App\Models\CompanyType;
use Illuminate\Http\Request;
use App\Helpers\JsonResponse;
use App\Http\Controllers\Controller;

class CompanyTypeController extends Controller
{
    public function index(Request $request)
    {
        $query = CompanyType::query();

        if (strtolower($request->input('sortBy')) === 'label') {
            if (strtolower($request->input('sortType')) === 'desc') {
                $query = $query->orderBy(strtolower($request->input('sortBy')), strtolower($request->input('sortType')));
            } else {
                $query = $query->orderBy(strtolower($request->input('sortBy')), 'asc');
            }
        }

        return JsonResponse::make($query->get());
    }
}
