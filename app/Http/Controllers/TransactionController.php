<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use DataTables;
use Illuminate\Support\Facades\Http;
use App\Models\Merchant;
use App\Models\MerchantDetails;
use App\Models\Nns;
// use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon; // Library untuk manipulasi tanggal


class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
        $this->middleware('permission:transaction-list|transaction-create|transaction-edit|transaction-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:transaction-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:transaction-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:transaction-delete', ['only' => ['destroy']]);
        $this->middleware('permission:transaction-broadcast', ['only' => ['broadcast']]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    // public function index(TransactionDataTable $dataTable)
    // {
    //     return $dataTable->render('transaction.index');
    // }

    public function index()
    {

        $today = Carbon::today()->toDateString();

        // $data = Http::get('http://192.168.26.26:10002/tm.php')->json();

        // foreach ($data as $key => $value) {

        //     $merchant = Merchant::where('ID',$value['MERCHANT_ID'])->first();
        //     if ($merchant != '') {
        //         // dd($merchant);
        //         $data[$key]['MERCHANT'] = $merchant->toArray();
        //     } else {
        //         $data[$key]['MERCHANT'] = $merchant;
        //     }



        // }

        // dd($data);
        return view('transactions.index', ['startDate' => $today, 'endDate' => $today]);
    }

    public function data(Request $request)
    {

        if ($request->ajax()) {

            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            // Menambahkan format tanggal untuk memastikan inklusi penuh hari terakhir
            if (!empty($startDate) && !empty($endDate)) {
                $startDate = trim($startDate);
                $endDate = Carbon::createFromFormat('Y-m-d', trim($endDate))->endOfDay()->toDateTimeString();
            }


            switch (env('APP_ENV')) {
                case 'local':

                    $userId = Auth::id();
                    $user = Auth::user();

                    // Retrieve data from the database based on user access to merchants
                    $query = DB::table('QRIS_TRANSACTION_AQUERIER_MAIN')
                        ->distinct()
                        ->join('user_has_merchant', 'QRIS_TRANSACTION_AQUERIER_MAIN.MERCHANT_ID', '=', 'user_has_merchant.MERCHANT_ID')
                        ->join('users', 'user_has_merchant.USER_ID', '=', 'users.id')
                        ->select('QRIS_TRANSACTION_AQUERIER_MAIN.*')
                        ->whereBetween('QRIS_TRANSACTION_AQUERIER_MAIN.created_at', [$startDate, $endDate]);

                    // if ($userId != 1) { // Filter data for non-admin users
                    //     $query->where('users.id', $userId);
                    // }

                    if (!$user->hasRole(['Admin', 'Superadmin'])) {
                        $query->where('users.id', $userId);
                    }

                    $data = $query->get()->map(function ($item) {
                        // Convert each item to an array if necessary, depending on how you need to process it
                        return (array) $item;
                    })->toArray();

                    // Add additional processing if needed (e.g., enriching data with other details from the database)
                    foreach ($data as $key => $value) {
                        $merchant = Merchant::where('ID', $value['MERCHANT_ID'])->first();
                        $data[$key]['MERCHANT'] = $merchant ? $merchant->toArray() : null;

                        $nns = Nns::where('NNS', $value['ISSUING_INSTITUTION_NAME'])->first();
                        if ($nns) {
                            $data[$key]['NNS'] = $nns['NAME'];
                        } else {
                            $data[$key]['NNS'] = null;
                        }
                    }
                    break;

                case 'dev':
                    $data = Http::get('http://192.168.26.26:10002/tm.php')->json();

                    foreach ($data as $key => $value) {
                        $merchant = Merchant::where('ID', $value['MERCHANT_ID'])->first();
                        $data[$key]['MERCHANT'] = $merchant ? $merchant->toArray() : null;
                        $nns = Nns::where('NNS', $value['ISSUING_INSTITUTION_NAME'])->first();
                        // dd($nns->toArray());
                        if ($nns != '') {
                            // dd($nns);
                            // dd($data[$key]['NNS']['NAME']);
                            $data[$key]['NNS'] = $nns['NAME'];
                        } else {
                            $data[$key]['NNS'] = $nns;
                        }
                    }

                    break;

                case 'prod':
                    $userId = Auth::id();
                    $user = Auth::user();


                    // Retrieve data from the database based on user access to merchants
                    $query = DB::table('QRIS_TRANSACTION_AQUERIER_MAIN')
                        ->distinct()
                        ->join('user_has_merchant', 'QRIS_TRANSACTION_AQUERIER_MAIN.MERCHANT_ID', '=', 'user_has_merchant.MERCHANT_ID')
                        ->join('users', 'user_has_merchant.USER_ID', '=', 'users.id')
                        ->select('QRIS_TRANSACTION_AQUERIER_MAIN.*')
                        ->whereBetween('QRIS_TRANSACTION_AQUERIER_MAIN.created_at', [$startDate, $endDate]);

                    if (!$user->hasRole(['Admin', 'Superadmin'])) {
                        $query->where('users.id', $userId);
                    }

                    $data = $query->get()->map(function ($item) {
                        return (array) $item;
                    })->toArray();

                    // Add additional processing if needed (e.g., enriching data with other details from the database)
                    foreach ($data as $key => $value) {
                        $merchant = Merchant::where('ID', $value['MERCHANT_ID'])->first();
                        $data[$key]['MERCHANT'] = $merchant ? $merchant->toArray() : null;

                        $nns = Nns::where('NNS', $value['ISSUING_INSTITUTION_NAME'])->first();
                        if ($nns) {
                            $data[$key]['NNS'] = $nns['NAME'];
                        } else {
                            $data[$key]['NNS'] = null;
                        }
                    }
                    break;
            }

            // if ($startDate && $endDate) {
            //     $data = array_filter($data, function ($item) use ($startDate, $endDate) {
            //         $createdAt = $item['CREATED_AT'] ?? null;
            //         return $createdAt >= $startDate && $createdAt <= $endDate;
            //     });
            // }



            // dd($data);
            return DataTables::of($data)

                ->toJson();
        }
    }
    public function data2(Request $request)
    {


        $searchValue = $request['search']['value']; // Search value
        $searchByAmount = $request['searchByAmount'];
        $searchByStatus = $request['searchByStatus'];
        ## Search 
        $searchQuery = " ";
        if ($searchByAmount != '') {
            $searchQuery .= " and (emp_name like '%" . $searchByAmount . "%' ) ";
        }
        if ($searchByStatus != '') {
            $searchQuery .= " and (gender='" . $searchByStatus . "') ";
        }
        if ($searchValue != '') {
            $searchQuery .= " and (emp_name like '%" . $searchValue . "%' or 
                  email like '%" . $searchValue . "%' or 
                  city like'%" . $searchValue . "%' ) ";
        }
    }

    public function detail($id)
    {
        $idString = strval($id);

        switch (env('APP_ENV')) {
            case 'local':
                $datas = Transaction::where('ID',$idString)->get()->toArray();
                break;
            case 'dev':
                $datas = Http::get('http://192.168.26.26:10002/tm.php')->json();
                break;
            case 'prod':
                $datas = Transaction::where('ID',$idString)->get()->toArray();
                break;
        }




        // $dataDecode = json_decode($datas);

        foreach ($datas as $key => $value) {
            // dd($value);

            $merchant = Merchant::where('ID', $value['MERCHANT_ID'])->first();
            if ($merchant != '') {
                // dd($merchant);
                $datas[$key]['MERCHANT'] = $merchant->toArray();
            } else {
                $datas[$key]['MERCHANT'] = $merchant;
            }

            $nns = Nns::where('NNS', $value['ISSUING_INSTITUTION_NAME'])->first();
            // dd($nns->toArray());
            if ($nns != '') {
                // dd($nns);
                // dd($datas[$key]['NNS']['NAME']);
                $datas[$key]['NNS'] = $nns['NAME'];
            } else {
                $datas[$key]['NNS'] = $nns;
            }

            $mpan = MerchantDetails::where('MERCHANT_ID', $value['MERCHANT_ID'])->first();
            if ($mpan != '') {
                $datas[$key]['MPAN'] = $mpan['MPAN'];
            } else {
                $datas[$key]['MPAN'] = $mpan;
            }
            // dd($mpan);
        }





        foreach ($datas as $data) {

            if ($data['ID'] == $idString) {

                $data['AMOUNT_MDR'] = '';
                $data['PAID_AT'] = '';

                return response()->json([

                    'MERCHANT_ACC_NUMBER'           =>   $data['MERCHANT_ACC_NUMBER'],
                    'TIP_INDICATOR'                 =>   $data['TIP_INDICATOR'],
                    'TRANSFER_REFF'                 =>   $data['TRANSFER_REFF'],
                    'AMOUNT_TIP_PERCENTAGE'         =>   $data['AMOUNT_TIP_PERCENTAGE'],
                    'DESCRIPTION'                   =>   $data['DESCRIPTION'],
                    'QRIS'                          =>   $data['QRIS'],
                    'TRANSACTION_ID'                =>   $data['TRANSACTION_ID'],
                    'STATUS_TRANSFER'               =>   $data['STATUS_TRANSFER'],
                    'STATUS'                        =>   $data['STATUS'],
                    'AMOUNT'                        =>   $data['AMOUNT'],
                    'EXPIRE_DATE_TIME'              =>   $data['EXPIRE_DATE_TIME'],
                    'RETRIEVAL_REFERENCE_NUMBER'    =>   $data['RETRIEVAL_REFERENCE_NUMBER'],
                    'CREATED_AT'                    =>   $data['CREATED_AT'],
                    'TRANSACTION_TYPE'              =>   $data['TRANSACTION_TYPE'],
                    'ID'                            =>   $data['ID'],
                    'FEE_AMOUNT'                    =>   $data['FEE_AMOUNT'],
                    'AMOUNT_REFUND'                 =>   $data['AMOUNT_REFUND'],
                    'INVOICE_NUMBER'                =>   $data['INVOICE_NUMBER'],
                    'POSTAL_CODE'                   =>   $data['POSTAL_CODE'],
                    'UPDATED_AT'                    =>   $data['UPDATED_AT'],
                    'TRANSFER_STATUS'               =>   $data['TRANSFER_STATUS'],
                    'MERCHANT_ID'                   =>   $data['MERCHANT_ID'],
                    'MERCHANT'                      =>   $data['MERCHANT'],
                    'AQUERIER_TYPE'                  =>   $data['AQUERIER_TYPE'],
                    'MID'                              =>   $data['MID'],
                    'RRN_REFUND'                     =>   $data['RRN_REFUND'],
                    'BIT3_RESPONSE'                  =>   $data['BIT3_RESPONSE'],
                    'RC_FUND'                        =>   $data['RC_FUND'],
                    'ACQUIRING_INSTITUTION_NAME'    =>   $data['ACQUIRING_INSTITUTION_NAME'],
                    'ISSUING_INSTITUTION_NAME'      =>   $data['ISSUING_INSTITUTION_NAME'],
                    'ISSUING_CUSTOMER_NAME'         =>   $data['ISSUING_CUSTOMER_NAME'],
                    'CUSTOMER_PAN'                  =>   $data['CUSTOMER_PAN'],
                    'NNS'                  =>   $data['NNS'],
                    'bit_12'                  =>   $data['bit_12'],
                    'BIT_2'                  =>   $data['BIT_2'],
                    'CURRENT_AMOUNT_REFUND'                  =>   $data['CURRENT_AMOUNT_REFUND'],
                    'MPAN'                  =>   $data['MPAN'],
                    'AMOUNT_MDR'            => $data['AMOUNT_MDR'],
                    'PAID_AT'                  =>   $data['PAID_AT'],

                ]);
            }
        }
    }
}
