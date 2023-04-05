<?php

namespace App\Http\Controllers\Api;

use App\Models\Store;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class StoreController extends Controller
{
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|max:255',
            'branch' => 'required|max:255',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails())
            return response()->json([
                'success' => true,
                'message' => $validator->errors(),
            ], 500);

        $store = new Store();
        $store->name = $request->name;
        $store->branch = $request->branch;
        $store->owner_id = auth()->id();

        if($store->save()) {
            return response()->json([
                'success' => true,
                'data' => $store,
                'message' => 'Store save successfully!',
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Saving failed.',
            ], 500);
        }
    }
}
