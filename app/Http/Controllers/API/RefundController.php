<?php

namespace App\Http\Controllers\API;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RefundController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // return view('qris.index');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
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
            
        Log::channel('apilog')->info('REQ SEND API (REFUND) : ' .  json_encode($data));
            // dd($data);
            
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                ])->post(
                    'http://10.11.13.51:9800/v1/api/refund',
                    $data
                );

                // dd($response);

                $res = $response->json();
                
                
        Log::channel('apilog')->info('RESP API (REFUND) : ' .  json_encode($res));
        // dd($response->json());
        return response()->json($res);
    }
}
