<?php

namespace App\Http\Controllers\API\client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use App\Models\Merchant;
use App\Models\Mcc;
use App\Models\MerchantDetails;
use App\Models\MerchantDomestic;
use App\Models\UserMerchant;

class RefundClientController extends Controller
{
    public function get(Request $request)
    {
        // dd($request);

        $data = [
            "MPI" => [
                "RRN" => $request['RRN'],
                "AMOUNT" => $request['AMOUNT'],
                "ACC_SRC" => $request['ACC_SRC'],
                "INVOICE_NUMBER" => $request['INVOICE_NUMBER'],
                
                ]
            ];
            
        // Log::channel('apilog')->info('REQ SEND API (REFUND) : ' .  json_encode($data));
            // dd($data);
            $customApiBaseUrl = env('API_URL');
            
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                ])->post(
                    $customApiBaseUrl . '/v1/api/refund',
                    $data
                );

                // dd($response);

                $res = $response->json();
                
                
        // Log::channel('apilog')->info('RESP API (REFUND) : ' .  json_encode($res));
        // dd($response->json());
        return response()->json($res);
    }
}