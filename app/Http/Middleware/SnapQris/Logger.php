<?php

namespace App\Http\Middleware\SnapQris;

use Closure;
use Illuminate\Support\Facades\Log;

class Logger
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $method = $request->method();
        $path = $request->path();
        $payload = $request->getContent();
        Log::debug('SNAP API HIT : [' . $method . '][' . $path . '][' . $payload . ']');

        return $next($request);
    }
}
