<?php

namespace App\Http\Middleware\SnapQris;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class JwtValidation
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    protected $RC;
    public function handle(Request $request, Closure $next)
    {
        $this->RC = '4014701';
        $token = $request->bearerToken();

        if (!$token) {
            Log::error('API error [Token Not Found]');

            return response()->json(
                [
                    'responseCode' => '4014703',
                    'responseMessage' => 'Token Not Found (B2B)'
                ],
                401
            );
        }

        try {
            $user = auth('snap')->user();
            if (!$user) {
                Log::error('API error [Invalid Token]');
                throw new \Exception('Invalid Token (B2B)', $this->RC);
            }
            $request->attributes->add(['client_id' => $user->CLIENT_ID]);
        } catch (TokenExpiredException $e) {
            Log::error('API error [Expired Token (B2B)]');
            return response()->json(
                [
                    'responseCode' => '4017302',
                    'responseMessage' => 'Expired Token (B2B)'
                ],
                401
            );
        } catch (TokenInvalidException $e) {
            Log::error('API error [Invalid Token (B2B)]');
            return response()->json(
                [
                    'responseCode' => $this->RC,
                    'responseMessage' => 'Invalid Token (B2B)'
                ],
                401
            );
        } catch (JWTException $e) {
            Log::error('API error [Invalid Token (B2B)]');
            return response()->json(
                [
                    'responseCode' => $this->RC,
                    'responseMessage' => 'Invalid Token (B2B)'
                ],
                401
            );
        } catch (\Exception $e) {
            Log::error('API error [' . $e->getMessage() . ']');
            return response()->json(
                [
                    'responseCode' => $e->getCode(),
                    'responseMessage' => $e->getMessage()
                ],
                401
            );
        }

        return $next($request);
    }
}
