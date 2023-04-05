<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ConvertFieldsToLowerCase
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

        $keysToTransform = config('project.keys_to_lowercase');
        $input = $request->all();

        foreach ($keysToTransform as $k => $key) {
            if (! empty($input[$key])) {
                $input[$key] = strtolower($input[$key]);

                $request->replace($input);
            }
        }

        return $next($request);
    }

    protected function transform($key, $value)
    {
        $keysToTransform = config('project.keys_to_lowercase');

        // foreach ($variable as $key => $value) {

        // }

        return in_array($key, $keysToTransform) ? strtolower($value) :  $value;
    }

}
