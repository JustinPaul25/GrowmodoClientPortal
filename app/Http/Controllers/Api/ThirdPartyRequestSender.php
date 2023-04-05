<?php

namespace App\Http\Controllers\Api;

use App\Helpers\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class ThirdPartyRequestSender extends Controller
{
    //

    public function asana(Request $request) {
        $validator = Validator::make($request->all(), [
            'endpoint' => 'required|url',
            'method' => 'required|in:post,get,delete,put,patch,head,options',
            'body' => 'json',
            'headers' => 'json',
        ]);

        if ($validator->fails())
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, $validator->errors());

        $body = ! empty($request->body) ? json_decode($request->body, true) : [];
        $headers = ! empty($request->headers) ? json_decode($request->headers, true) : [];


        if (! empty($headers)) {
            $response = Http::withToken(env('ASANA_ACCESS_TOKEN'))
                ->withHeaders($headers)
                ->{$request->method}($request->endpoint, $body)
            // ->acceptJson()
            ;
        } else {
            $response = Http::withToken(env('ASANA_ACCESS_TOKEN'))->{$request->method}($request->endpoint, $body)
            // ->acceptJson()
            ;
        }

        return JsonResponse::make($response->object());
    }

    public function apideck(Request $request) {
        $validator = Validator::make($request->all(), [
            'endpoint' => 'required|url',
            'method' => 'required|in:post,get,delete,put,patch,head,options',
            'body' => 'json',
            'headers' => 'json',
        ]);

        $body = ! empty($request->body) ? json_decode($request->body, true) : [];
        $headers = ! empty($request->headers) ? json_decode($request->headers, true) : [];

        $auth_headers = [
            'Authorization' => 'Bearer ' . env('APIDECK_API_KEY'),
            'x-apideck-app-id' => env('APIDECK_APP_ID'),
            'x-apideck-consumer-id' => env('APIDECK_CONSUMER_ID'),
        ];

        $response = Http::withHeaders($headers)
            ->{$request->method}($request->endpoint, $body)

        // ->acceptJson()
        ;


        return JsonResponse::make($response->object());
    }

    public function googlefonts(Request $request) {
        $validator = Validator::make($request->all(), [
            'sorting' => 'string|nullable',
        ]);

        if ($validator->fails())
            return JsonResponse::make([], JsonResponse::INVALID_PARAMS, $validator->errors());

        $apiKey = config('services.google.fonts_api_key');
        $endpoint = 'https://www.googleapis.com/webfonts/v1/webfonts';

        $response = Http::get($endpoint, [
            'sort' => $request->sorting ? $request->sorting : 'popularity',
            'key' => $apiKey,
        ]);

        $fonts = json_decode($response->body(), true);

        return JsonResponse::make($fonts);
    }

}
