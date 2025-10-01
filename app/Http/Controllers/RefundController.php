<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;

use Illuminate\Foundation\Auth\AuthenticatesUsers;



class RefundController extends Controller
{
    use AuthenticatesUsers;

    function __construct()
    {
        $this->middleware('permission:refund-list|refund-create|refund-edit|refund-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:refund-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:refund-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:refund-delete', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $getUserId = Auth::id();
        $userId = $getUserId;

        // $query = DB::table('QRIS_MERCHANT')
        //     ->join('user_has_merchant', 'QRIS_MERCHANT.ID', '=', 'user_has_merchant.MERCHANT_ID')
        //     ->join('users', 'user_has_merchant.USER_ID', '=', 'users.id')
        //     ->select('QRIS_MERCHANT.*');

        // if ($userId != 1) {
        //     $query->where('users.id', $userId);
        // }



        $datas = [];
        switch (env('APP_ENV')) {
            case 'local':
                $userId = Auth::id();
                $user = Auth::user(); 


                $query = DB::table('QRIS_TRANSACTION_AQUERIER_MAIN')
                    ->distinct()
                    ->join('user_has_merchant', 'QRIS_TRANSACTION_AQUERIER_MAIN.MERCHANT_ID', '=', 'user_has_merchant.MERCHANT_ID')
                    ->join('users', 'user_has_merchant.USER_ID', '=', 'users.id')
                    ->select('QRIS_TRANSACTION_AQUERIER_MAIN.*');

                    if (!$user->hasRole(['Admin', 'Superadmin'])) {
                        $query->where('users.id', $user->id);
                    }
                $datas = $query->get()->toArray();
                break;
            case 'dev':
                $datas = Http::get(env('API_URL_TM'))->json();  // Menggunakan variabel API_URL dari file .env
                break;
            case 'prod':
                $userId = Auth::id();
                $user = Auth::user(); 


                $query = DB::table('QRIS_TRANSACTION_AQUERIER_MAIN')
                    ->distinct()
                    ->join('user_has_merchant', 'QRIS_TRANSACTION_AQUERIER_MAIN.MERCHANT_ID', '=', 'user_has_merchant.MERCHANT_ID')
                    ->join('users', 'user_has_merchant.USER_ID', '=', 'users.id')
                    ->select('QRIS_TRANSACTION_AQUERIER_MAIN.*');

                    if (!$user->hasRole(['Admin', 'Superadmin'])) {
                        $query->where('users.id', $user->id);
                    }
                $datas = $query->get()->toArray();
                break;
        }



        $data = []; // Membuat array kosong
        foreach ($datas as $item) {
            $data[] = (array) $item; // Mengubah objek menjadi array asosiatif
        }

        $amount = null; // Nilai default jika $amount null
        foreach ($data as $p) {
            if ($p['AMOUNT'] !== null) {
                $amount = $p['AMOUNT'];
                break; // Keluar dari loop jika $amount sudah ditemukan
            }
        }

        return view('refund.index', compact('data', 'amount'));
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function hit(Request $request)
    {

        // dd($request->toArray());

        $request->validate([
            'RRN' => 'required',
            'AMOUNTS' => 'required|integer',
            'AMOUNT' => 'required|integer|lte:AMOUNTS',
            'ACC_SRC' => 'required',
            'INVOICE_NUMBER' => 'required',
        ]);

        $data = [

            "RRN" => $request['RRN'],
            "AMOUNT" => $request['AMOUNT'],
            "ACC_SRC" => $request['ACC_SRC'],
            "INVOICE_NUMBER" => $request['INVOICE_NUMBER'],

        ];

        // dd($request->user());

        // $request->validate([

        //     $this->username() => 'required|string',
        //     'password' => 'required|string',
        // ]);

        // dd($request);

        // dd($data);
        $user = $request->user();

        $baseurl = ENV('BASE_URL');

        $token = Http::timeout(5)->withHeaders([
            'Content-Type' => 'application/json',
            'Access-Control-Allow-Origin' => '*',
        ])->post(
            $baseurl . '/api/gettoken',
            [
                "email" => $user->email,
                "password" => $user->getAuthPassword()
            ]
        );
        $resptoken = $token->json();

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $resptoken['access_token'],
            'Content-Type' => 'application/json',
        ])->post(
            $baseurl . '/api/refund',
            $data
        );

        // dd($response->json());
        $res = $response['RC'];

        // dd($res);
        $error = ($response != '0000');
        $Msg =  $response['RM'];

        try {
            if ($res != '0000') {
                return redirect()->route('refund.index')
                    ->with('errors', 'Refund Failed. ' . json_encode($Msg));
            }

            return redirect()->route('refund.index')
                ->with('success', 'Refund successful');
        } catch (Exception $e) {
            return redirect()->route('refund.index')
                ->with('errors', $e->getMessage());
        }
    }
}
