<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\Log;
use Closure;
use Auth;

class LogAfterRequest
{
    public function handle($request, Closure $next)
    {
        return $next($request);
    }

    public function terminate($request, $response)
    {
        $url = $request->fullUrl();
        $ip = $request->ip();
        $user_id = (isset(Auth::user()->id) != '') ? Auth::user()->id : (isset(auth('api')->user()->id) ? auth('api')->user()->id : 0);
        $user_name = (isset(Auth::user()->name) != '') ? Auth::user()->name : (isset(auth('api')->user()->name) ? auth('api')->user()->name : '');
        $r = new \App\Models\Request;
        $r->ip = $ip;
        $r->url = $url;
        $r->request = json_encode($request->all());
        // $r->response_header = json_encode($response->header());
        // $r->response_status = json_encode($response->status());
        // $r->response_exception = json_encode($response->exception());
        // $r->response_body = json_encode($response->content());
        $r->response = json_encode($response);
        $r->userid = $user_id;
        $r->user = $user_name;
        $r->save();        
    }
}
