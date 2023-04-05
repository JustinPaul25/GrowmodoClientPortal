<?php

namespace App\Http\Controllers\Api;

use App\Helpers\JsonResponse;
use App\Http\Controllers\Controller;
use App\Models\BrandCategory;
use Illuminate\Http\Request;

class BrandCategoryController extends Controller
{
    //
    public function index(Request $request) {
        return JsonResponse::make(BrandCategory::all());
    }

    public function storeAdmin(Request $request) {
        // return json_decode($request->getContent(), true);
        $request_data = json_decode($request->getContent(), true);
        $validator = Validator::make($request_data, [
            'label' => 'required|string|max:255',
            'color' => 'required|string|max:255',
            'bg_color' => 'required|string|max:255',
        ]);

        if ($validator->fails())
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, $validator->errors());

        $brandCat = new BrandCategory();
        $brandCat->label = $request_data['label'];
        $brandCat->color = $request_data['color'];
        $brandCat->bg_color = $request_data['bg_color'];

        if ($brandCat->save()) {

            $response = $brandCat->toArray();

            return JsonResponse::make($response);
        } else {
            return JsonResponse::make([], JsonResponse::EXCEPTION, 'Unknown Error');
        }

    }
    
    public function updateAdmin(Request $request, $id = null) {
        if (empty ($id))
            return JsonResponse::make([], JsonResponse::NOT_FOUND, 'Brand Category not found.');

        $request_data = json_decode($request->getContent(), true);
        $validator = Validator::make($request_data, [
            'label' => 'required|string|max:255',
            'color' => 'string|max:255',
            'bg_color' => 'string|max:255',
        ]);

        if ($validator->fails())
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, $validator->errors());

        $brandCat = BrandCategory::find($id);
        if (empty ($brandCat->id))
            return JsonResponse::make([], JsonResponse::NOT_FOUND, 'Brand Category not found.');

        if( !empty ( $request_data['label'] ) )
            $brandCat->label = $request_data['label'];
        if( !empty ( $request_data['color'] ) )
            $brandCat->color = $request_data['color'];
        if( !empty ( $request_data['bg_color'] ) )
            $brandCat->bg_color = $request_data['bg_color'];

        if ($brandCat->save()) {

            return JsonResponse::make($response);
        } else {
            return JsonResponse::make([], JsonResponse::EXCEPTION, 'Unknown Error');
        }

    }

    public function deleteAdmin(Request $request, $id)
    {
        if (empty ($id))
            return JsonResponse::make([], JsonResponse::NOT_FOUND, 'Brand Category not found.');

        $brandCat = BrandCategory::find($id);
        if (empty ($brandCat->id))
            return JsonResponse::make([], JsonResponse::NOT_FOUND, 'Brand Category not found.');

        $s = $brandCat->delete();

        return JsonResponse::make($s, $s ? JsonResponse::SUCCESS : JsonResponse::EXCEPTION, $s ? 'Brand Category has been deleted.' : 'Unable to delete user');
    }

}