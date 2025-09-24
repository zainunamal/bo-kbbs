<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Intervention\Image\ImageManager;
use Image;
use Illuminate\Support\Facades\Route;
use App\Models\Merchant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class QrisController extends Controller
{

     /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
        $this->middleware('permission:generate-qris|qris-create|qris-edit|qris-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:qris-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:qris-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:qris-delete', ['only' => ['destroy']]);
    }
    public function index()
    {

        $getUserId = Auth::id();
        $userId = $getUserId;
        $user = Auth::user(); 


        $query = DB::table('QRIS_MERCHANT')
            ->distinct()
            ->join('user_has_merchant', 'QRIS_MERCHANT.ID', '=', 'user_has_merchant.MERCHANT_ID')
            ->join('users', 'user_has_merchant.USER_ID', '=', 'users.id')
            ->select('QRIS_MERCHANT.*');
            // ->groupBy('QRIS_MERCHANT.ID');

        if (!$user->hasRole(['Admin', 'Superadmin'])) {
                $query->where('users.id', $user->id);
            }

        $merchant = $query->get()->toArray();

        // $merchant = Merchant::orderBy('ID')->get()->toArray();

        // dd($merchant[0]->NMID);

        $qrType = getQrtype();

          // Bangun path file gambar berdasarkan NMID
          $nmid = $merchant[0]->NMID;
          $imagePath = "/opt/vsi-scheduler-qris-acquirer/data_pten/{$nmid}_A01.png";
  
          // dd($imagePath);
  
          // Periksa apakah file gambar ada
          $imageExists = file_exists($imagePath);

        return view('qris.index', compact('qrType', 'merchant', 'imagePath', 'imageExists'));
    }

    public function hit(Request $request)
    {

        date_default_timezone_set('Asia/Jakarta');
        // dd($request['qrType']);
        // Log::channel('weblog')->info('req : ' . $request);
        $exp = null;
        if ($request['TYPE'] == 'DINAMIS') {
            $current = date("Y-m-d H:i:s");
            $exp = date('Y-m-d H:i:s', strtotime('+15 minutes', strtotime($current)));
            $exp = date('Y-m-d H:i:s', strtotime('+7 hours', strtotime($exp)));
        }

        $data = [
            "qrType" =>  $request['qrType'],
            "param" => [
                "MPI" => [
                    "MERCHANT_ID" =>  $request['MERCHANT_ID'],
                    "AMOUNT" =>  $request['AMOUNT'],
                    "TIP_INDICATOR" =>  $request['TIP_INDICATOR'],
                    "FEE_AMOUNT" =>  $request['FEE_AMOUNT'],
                    "FEE_AMOUNT_PERCENTAGE" =>  $request['FEE_AMOUNT_PERCENTAGE'],
                    "TYPE" =>  $request['TYPE'],
                    "VA" =>  $request['VA'],
                    "EXPIRE_DATE_TIME" =>  $exp,
                ]
            ]
        ];

        // dd($data);

        Log::channel('weblog')->info('REQ SEND : ' .  json_encode($data));


        // dd($data['param']);

        $customApiBaseUrl = env('BASE_URL');

        $token = Http::timeout(5)->withHeaders([
            'Content-Type' => 'application/json',
            'Access-Control-Allow-Origin' => '*',
        ])->post(
            $customApiBaseUrl . '/api/gettoken',
            [
                "email" => "admin@gmail.com",
                "password" => "1234qwer"
            ]
        );
        
        $resptoken = $token->json();
        // dd($resptoken);

        // $response = Http::withHeaders([
        //     'Content-Type' => 'application/json',
        // ])->post(
        //     'http://192.168.26.75:9800/v1/api/aquerier/create/qr',
        //     $data
        // );

        // $request = Request::create('api/gettoken', 'POST', [
        //     "email" => "admin@gmail.com",
        //     "password" => "123456"
        // ]);
        // $request->headers->set('Content-Type', 'application/json');

        // $response = Route::dispatch($request);

        // dd($response);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $resptoken['access_token'],
            'Content-Type' => 'application/json',
        ])->post(
            $customApiBaseUrl . '/api/qris',
            $data
        );


        // dd($data['AMOUNT']);

        // Log::channel('weblog')->info('resp : ' . $response);
        //  dd($response['MPO']['QR']);

        // return response()([
        //     'qr'    => $response['MPO']['QR'],
        // ]);
        $res = $response['RC'];
        $error = ($response != '0000');
        if ($res != '0000') {
        }
        try {
            return response()->json([
                'qr'      => $response['MPO']['QR'],
                'detail'  => $response['MPO']['DETAIL'],
            ]);
        } catch (\Throwable $th) {

            return response()->json([
                'error'    => ' Failed To Generate QR Code ',
            ]);
        }
    }



    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */



    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy()
    {
        //
    }
}
