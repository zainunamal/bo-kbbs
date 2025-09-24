<?php

namespace App\Http\Middleware\SnapQris;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Routing\ResponseFactory;

class ValidateSignature
{
    protected $factory, $RC;
    public $attributes;

    /**
     * JsonMiddleware constructor.
     *
     * @param ResponseFactory $factory
     */
    public function __construct(ResponseFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $this->RC = '4014700';

        try {
            $method = $request->method();
            $path = $request->path();
            $token = $request->bearerToken();
            $timestamp = $request->header('X-TIMESTAMP');
            $externalId = $request->header('X-EXTERNAL-ID');

            if (!$externalId) {
                Log::error('API error [Invalid  ExternalID]');
                throw new \Exception('Invalid Mandatory Field {X-EXTERNAL_ID}', '4004702');
            }

            if (Cache::has($externalId)) {
                Log::error('API error [Conflict [Duplicat ExternalID]]');
                throw new \Exception('Conflict', '4092400');
            } else {
                Cache::put($externalId, 'true');
            }

            $userData = $request->getContent();
            if ($request->method() == 'GET' || !empty($request->allFiles())) {
                $userData = "";
            }

            $minifyData = json_encode(json_decode($userData));
            if (empty($timestamp)) {
                Log::error('API error [Unauthorized StringToSign]');
                throw new \Exception('Unauthorized StringToSign', '4014300');
            }

            $data = $method . ':/' . $path . ':' . $token . ':' . hash(
                'sha256',
                $minifyData
            ) . ':' . $timestamp;
            
            $signatureBase64 = $request->header('X-SIGNATURE');
            $client = auth('snap')->user();
            $sign = base64_encode(hash_hmac('SHA512', $data, $client['SECRET_KEY'], true));

            if ($sign != $signatureBase64) {
                log::error('minimize Data  [' . print_r($minifyData, true) . ']');
                log::error('String To SIGN  [' . print_r($data, true) . ']');
                log::error('Client Secret  [' . print_r($client['SECRET_KEY'], true) . ']');
                log::error('Client SIGN  [' . print_r($signatureBase64, true) . ']');
                log::error('Generated SIGN  [' . print_r($sign, true) . ']');
                Log::Error('Response   [' . json_encode([
                    'responseCode' => $this->RC,
                    'responseMessage' => 'Unauthorized. [Signature]'
                ]) . ']');

                return response()->json(
                    [
                        'responseCode' => $this->RC,
                        'responseMessage' => 'Unauthorized. [Signature]'
                    ],
                    401
                );
            }

            $response = $next($request);
            $content = $response->content();
            log::debug('DOWNLINE RESP :' . print_r(substr($content, 0, 1000), true));

            return $response;

        } catch (\Exception $e) {
            Log::Error('Response   [' . json_encode([
                'responseCode' => $e->getCode(),
                'responseMessage' => $e->getMessage()
            ]) . ']');
            Log::Error('Catch Error ' . $e->getTraceAsString());

            return response()->json(
                [
                    'responseCode' => (string) $e->getCode(),
                    'responseMessage' => $e->getMessage(),
                ],
                401
            );
        }
    }

    public static function verifySignature($data, $signatureBase64, $publicKey)
    {
        $binarySignature = base64_decode($signatureBase64);
        return (bool) openssl_verify(
            $data,
            $binarySignature,
            $publicKey,
            OPENSSL_ALGO_SHA256
        );
    }
}
