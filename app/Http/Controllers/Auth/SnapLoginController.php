<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log as log;
use App\Models\MerchantSnapCredentials;
use App\Http\Requests\SnapQris\LoginRequest;

class SnapLoginController extends Controller
{
    public function getToken(LoginRequest $request)
    {
        try {
            Log::debug('---------GET TOKEN SNAP Callback-------');
            Log::channel('info')->info('GET TOKEN REQUEST Header : ' . json_encode($request->headers->all()));
            Log::channel('info')->info('GET TOKEN REQUEST Body : ' . json_encode($request->all()));

            // Get the client-id from the request header
            $clientId = $request->header('X-CLIENT-KEY');
            Log::debug(' Client =>' . $clientId);

            // Look up the user using their client-id
            $user = MerchantSnapCredentials::where('CLIENT_ID', $clientId)->first();

            // If the user does not exist, return an error
            if (!$user) {
                Log::error('API error [Unauthorized Client]');
                throw new \Exception('Unauthorized. [Unknown client]', '4017300');
            }

            // Get the RSA signature from the request
            $signature = $request->header('X-SIGNATURE');
            $timestamp = $request->header('X-TIMESTAMP');

            if (empty($timestamp)) {
                Log::error('API error [Unauthorized StringToSign]');
                throw new \Exception('Unauthorized StringToSign', '4014300');
            }

            if (
                !preg_match('/^' .
                    '(\d{4})-(\d{2})-(\d{2})T' . // YYYY-MM-DDT ex: 2014-01-01T
                    '(\d{2}):(\d{2}):(\d{2})' . // HH-MM-SS  ex: 17:00:00
                    '(Z|((-|\+)\d{2}:\d{2}))' . // Z or +01:00 or -01:00
                    '$/', $timestamp, $parts)
            ) {
                log::error('INVALID HEADER X-TIMESTAMP FORMAT ' . $timestamp);
                throw new \Exception('Invalid Mandatory Field Header X-TIMESTAMP', 4007302);
            }

            $reqTime = new \DateTime($timestamp);
            $systemTime = new \DateTime();
            $tTime = $reqTime->diff($systemTime);
            $timeRange = 20;
            if ($tTime->i > $timeRange) {
                log::error('time req :' . $timestamp . '  and system time  :' . $systemTime . '');

                log::error('INVALID HEADER X-TIMESTAMP Range TIMESTAMP max ' . $timeRange . ' minutes  :' . $tTime->i . ' Minutes');
                throw new \Exception('Invalid Mandatory Field Header X-TIMESTAMP', 4007302);
            }

            $payload = $clientId . '|' . $timestamp;
            $binarySignature = base64_decode($signature);

            // Use the user's public key to verify the signature
            $pubKey = $user->PUBLIC_KEY;
            if ($request->hasHeader('X-LOCAL')) {
                Log::debug(' LOCAL Postman =>' . $clientId);
                // $pubKey = $this->localPubKey();
                $pubKey = file_get_contents(storage_path('sofkey/rsa_public_key.pem'));
            }

            $result = openssl_verify(
                $payload,
                $binarySignature,
                $pubKey,
                'sha256'
            );

            // If the signature is valid, log the user in; otherwise, return an error
            if ($result == 1) {
                // Log the user in and return success
                $token = auth('snap')->login($user);

                if (!$token) {
                    Log::error('API error [Failed Generate Token]');
                    throw new \Exception('General Error', 500002);
                }
                $ttl = $user['TOKEN_EXPIRED'];

                $response = response()->json(
                    [
                        "responseCode" => "2007300",
                        "responseMessage" => "Successful",
                        "accessToken" => $token,
                        "tokenType" => "Bearer",
                        "expiresIn" => $ttl * 60
                    ],
                    200
                );
            } else {
                $response = response()->json(
                    [
                        'responseCode' => '4017300',
                        'responseMessage' => 'Unauthorized. [Signature]'
                    ],
                    401
                );
            }
        } catch (\Exception $error) {
            $code = $error->getCode() != 0 ? $error->getCode() : '4017300';
            $httpStatus = substr($code, 0, 3);
            $response = response()->json(
                ['responseCode' => '' . $code, 'responseMessage' => $error->getMessage()],
                $httpStatus
            );
            log::Error('Response   [' . json_encode([
                'responseCode' => '' . $code,
                'responseMessage' => $error->getMessage()
            ]) . ']');
            log::Error('Catch Error   [' . $error->getTraceAsString() . ']');
        }
        return $response;
    }
}
