<?php

namespace App\Http\Controllers;

use App\Models\Merchant;
use App\Models\Mcc;
use App\Models\MerchantDetails;
use App\Models\MerchantDomestic;
use App\Models\UserMerchant;
use App\Models\QrisMerchantActivity;
use App\Models\Cabang;
use App\Models\Criteria;
use App\Models\KabKota;
use App\Models\Wilayah;
use App\User;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use App\Traits\Common;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;


use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

use Carbon\Carbon;
use App\Exports\MerchantsExport;
use Exception;
use Maatwebsite\Excel\Facades\Excel;


class MerchantController extends Controller
{
    use Common;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
        $this->middleware('permission:merchant-list|merchant-create|merchant-edit|merchant-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:merchant-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:merchant-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:merchant-delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $getUserId = Auth::id();
        $user = Auth::user();
        $search = $request->input('search');

        $query = DB::table('QRIS_MERCHANT')
            ->whereIn('STATUS', [0, 1])
            ->select('QRIS_MERCHANT.*');

        if (!$user->hasRole(['Admin', 'Superadmin'])) {
            $query->join('user_has_merchant', 'QRIS_MERCHANT.ID', '=', 'user_has_merchant.MERCHANT_ID');
            $query->join('users', 'user_has_merchant.USER_ID', '=', 'users.id');
            $query->where('users.id', $user->id);
        }

        if ($search) {
            $query->where('QRIS_MERCHANT.MERCHANT_NAME', 'like', '%' . $search . '%');
        }

        $query->groupBy('QRIS_MERCHANT.ID');

        $merchants = $query->paginate(10);

        // Menambahkan imagePath dan imageExists ke setiap merchant
        $merchants->transform(function ($merchant) {
            $nmid = $merchant->NMID;
            $imagePath = public_path("data_pten/{$nmid}_A01.png");
            $merchant->imagePath = $imagePath;
            $merchant->imageExists = file_exists($imagePath);
            return $merchant;
        });

        return view('merchant.index', [
            'merchants' => $merchants,
            'user' => $user // Kirim $user ke view
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $mcc = Mcc::orderBy('DESC_MCC')->get()->toArray();
        $criteria = Criteria::orderBy('NO')->get()->toArray();
        // $provinsi = Wilayah::select('PROVINSI')->distinct()->get();
        $cabangs = Cabang::all();

        $kabKota = KabKota::select('KOTA_KABUPATEN')->distinct()->get();

        // $kota = Wilayah::where('PROVINSI', $provinsi)->get(['ID', 'DAERAH_TINGKAT']);

        return view('merchant.create', compact('mcc', 'criteria', 'kabKota', 'cabangs'));
    }

    public function getKecamatan(Request $request)
    {
        $kotaKabupaten = $request->input('city');
        $kecamatan = KabKota::where('KOTA_KABUPATEN', $kotaKabupaten)->select('KECAMATAN')->distinct()->get();
        return response()->json($kecamatan);
    }

    public function getKodePos(Request $request)
    {
        $kecamatan = $request->input('kecamatan');
        $kodePos = KabKota::where('KECAMATAN', $kecamatan)->select('KODEPOS')->distinct()->get();
        return response()->json($kodePos);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        //ADD MCC
        if ($request['merchantType']) {
            return $this->storeMcc($request);
        }

        $allData = $request->all();
        $timestamp = Carbon::now()->toDateTimeString();
        $userId = $request->user() ? $request->user()->id : 'Guest';
        // $method = $request->method();

        Log::channel('merchant')->info('');
        Log::channel('merchant')->info('CREATE ==============================================================================');
        Log::channel('merchant')->info("REQUEST DATA at $timestamp - User: $userId : " . json_encode($allData));
        Log::channel('merchant')->info('');


        $validatedData = $request->validate([
            'norek' => 'required|numeric',
            'merchant' => 'required',
            'mcc' => 'required',
            'criteria' => 'required',
            'prov' => 'nullable',
            'city' => 'required',
            'address' => 'required',
            'postalcode' => 'required',
            'fee' => 'nullable',
            'mid' => 'nullable',
            'mpan' => 'required',
            'ktp' => 'nullable',
            'npwp' => 'nullable',
            'idMobile' => 'nullable',
            'phone' => 'required',
            'email' => 'required|email|unique:users,email',
            'qrType' => 'required',
            'merchantTipe' => 'required',
        ]);


        switch (env('APP_ENV')) {
            case 'prod':
                $cek = $this->cekNorek($request->norek);
                if ($cek['rc'] != '0000') {
                    return back()->withErrors(['msg' => 'Merchant created failed. (Invalid Account Number [No Rekening])']);
                }
                break;
            case 'dev':
                // $cek = $this->cekNorek($request->norek);
                // if ($cek['rc'] != '0000') {
                //     return back()->withErrors(['msg' => 'Merchant created failed. (Invalid Account Number [No Rekening])']);
                // }
                break;
            case 'local':
                // dd('kesini');
                break;
        }




        DB::beginTransaction();
        try {

            Log::channel('merchant')->info('CREATE BEGIN');


            $date = date('Y-m-d H:i:s');
            $nmid = 'ID' . genID(13);
            $nns = '93600521';
            $domain = 'ID.CO.QRIS.WWW';
            $data_domestic = [
                'REVERSE_DOMAIN' => $domain,
                'NMID' => '',
                'MCC' => $validatedData['mcc'],
                'CRITERIA' => $validatedData['criteria'],
            ];
            Log::channel('merchant')->info('REQ MerchantDomestic : ' . json_encode($data_domestic));
            $merchantDomestic = MerchantDomestic::create($data_domestic);
            Log::channel('merchant')->info('RESP MerchantDomestic : ' . json_encode($merchantDomestic));



            $id_domestic = $merchantDomestic->ID;


            $data_merchant = [
                'CREATED_AT' => $date,
                'UPDATED_AT' => '',
                'TERMINAL_LABEL' => 'A01',
                'MERCHANT_COUNTRY' => 'ID',
                'QRIS_MERCHANT_DOMESTIC_ID' => $id_domestic,
                'TYPE_QR' => 'STATIS',
                'MERCHANT_NAME' => $validatedData['merchant'],
                'MERCHANT_CITY' => $validatedData['city'],
                'POSTAL_CODE' => $validatedData['postalcode'],
                'MERCHANT_CURRENCY_CODE' => '360',
                'MERCHANT_TYPE' => $validatedData['mcc'],
                'MERCHANT_EXP' => '900',
                'MERCHANT_CODE' => genID(9, true),
                'MERCHANT_ADDRESS' => $validatedData['address'],
                'STATUS' => '0',
                'NMID' => $request->nmid,
                'ACCOUNT_NUMBER' => $validatedData['norek'],
                'KTP' => $validatedData['ktp'],
                'NPWP' => $validatedData['npwp'],
                'USER_ID_MOBILE' => $validatedData['idMobile'],
                'PHONE_MOBILE' => $validatedData['phone'],
                'EMAIL_MOBILE' => $validatedData['email'],
                'QR_TYPE' => $validatedData['qrType'],
                'MERCHANT_TYPE_2' => $validatedData['merchantTipe'],

            ];

            Log::channel('merchant')->info('REQ merchants : ' . json_encode($data_merchant));
            $merchants = Merchant::create($data_merchant);
            Log::channel('merchant')->info('RESP merchants : ' . json_encode($merchants));


            // User Has Merchant
            $merchant_id = $merchants->ID;
            $data_user = [
                'name' => $request->email,
                'email' => $request->email,
                'password' => Hash::make('1234qwer'),
            ];
            Log::channel('merchant')->info('REQ USER : ' . json_encode($data_user));
            $user = User::create($data_user);
            $user->assignRole($request->roles);
            // dd($user);
            Log::channel('merchant')->info('RESP USER : ' . $user);

            $userNew = $user->id;

            $data_user_has_merchant_new = [
                'USER_ID' => $userNew,
                'MERCHANT_ID' => $merchant_id,
            ];
            $user_has_merchant_new = UserMerchant::create($data_user_has_merchant_new);

            $data_user_has_merchant_auth = [
                'USER_ID' => Auth::id(),
                'MERCHANT_ID' => $merchant_id,
            ];
            $user_has_merchant_auth = UserMerchant::create($data_user_has_merchant_auth);
            Log::channel('merchant')->info('RESP USER_HAS_MERCHANT : ' . $user_has_merchant_new . '&&' . $user_has_merchant_auth);
            $data_detail = [
                'MERCHANT_ID' => $merchant_id,
                'DOMAIN' => $domain,
                'TAG' => '26',
                'MPAN' => $request->mpan,
                'MID' => $request->mid,
                'CRITERIA' => $request->criteria,
            ];
            Log::channel('merchant')->info('REQ MerchantDetails : ' . json_encode($data_detail));
            $merchant_detail = MerchantDetails::create($data_detail);
            Log::channel('merchant')->info('RESP MerchantDetails : ' . $merchant_detail);


            // Log activity for REQUEST
            $raw_request = json_encode($request->toArray());
            $activity_type = 'REQUEST';
            $comment = 'ADD REQUEST';
            $action = 'ADD';
            $this->logMerchantActivityNew($raw_request, null, $comment, $action, $activity_type);

            // Log activity for response
            $raw_response = json_encode([
                'merchant' => $merchants,
                'merchant_details' => $merchant_detail,
                'merchant_domestic' => $merchantDomestic,
                'user_has_merchant' => $data_user_has_merchant_auth
            ]);
            $activity_type = 'RESPONSE';
            $comment = 'ADD RESPONSE';
            $action = 'ADD';
            $this->logMerchantActivityNew(null, $raw_response, $comment, $action, $activity_type);





            // $param = [
            //     'MERCHANT_DOMESTIC' => $data_domestic,
            //     'MERCHANT' => $data_merchant,
            //     'MERCHANT_DETAIL' => $data_detail,
            // ];
            // $res = Http::timeout(10)->withBasicAuth('username', 'password')->post('http://192.168.26.26:10002/merchant.php?cmd=add', $param);

            // Generate Excel if Merchant exists
            if (isset($merchants)) {
                $request['addMerchant'] = true;
                $spreadsheet = $this->generateMerchantExcel($request);
                $this->saveExcelFile($spreadsheet, $request);
            }


            // Commit the transaction
            Log::channel('merchant')->info('DONE ==============================================================================');
            Log::channel('merchant')->info('');
            DB::commit();
            return redirect()->route('merchant.index')->with('msg', 'Merchant created successfully.');
        } catch (\Exception $e) {
            Log::channel('merchant')->info('FAILED CREATE ==============================================================================');
            Log::channel('merchant')->info('');
            DB::rollback();
            return back()->withErrors('Merchant creation failed: ' . $e->getMessage());
        }
    }

    public function show(Merchant $merchant, $id)
    {
        $id = Crypt::decrypt($id);
        $merchant = Merchant::where('id', $id)->first();
        $mcc = Mcc::orderBy('DESC_MCC')
            ->get()
            ->toArray();
        // $criteria = getCriteria();
        $criteria = Criteria::orderBy('NO')->get()->toArray();
        $merchant_detail = MerchantDetails::where('MERCHANT_ID', $id)->first();
        $merchant_domestic = MerchantDomestic::where('ID', $merchant->QRIS_MERCHANT_DOMESTIC_ID)->first();

        // Bangun path file gambar berdasarkan NMID
        $nmid = $merchant->NMID;
        $imagePath = "data_pten/{$nmid}_A01.png";

        // dd($imagePath);

        // Periksa apakah file gambar ada
        $imageExists = file_exists($imagePath);

        return view('merchant.show', compact('merchant', 'merchant_detail', 'mcc', 'criteria', 'merchant_domestic', 'imageExists', 'imagePath'));
    }


    public function edit(Merchant $merchant, $id)
    {
        $id = Crypt::decrypt($id);
        $merchant = Merchant::where('id', $id)->first();
        $mcc = Mcc::orderBy('DESC_MCC')->get()->toArray();
        // $criteria = getCriteria();
        $criteria = Criteria::orderBy('NO')->get()->toArray();
        $merchant_detail = MerchantDetails::where('MERCHANT_ID', $id)->first();
        $merchant_domestic = MerchantDomestic::where('ID', $merchant->QRIS_MERCHANT_DOMESTIC_ID)->first();

        // Ambil semua Kota/Kabupaten untuk dropdown Kota/Kabupaten
        $kabKota = KabKota::select('KOTA_KABUPATEN', 'KOTA_KABUPATEN_MAX_15')->distinct()->get();

        // Ambil Kecamatan berdasarkan Kota/Kabupaten yang sudah dipilih merchant
        $kecamatan = KabKota::where('KOTA_KABUPATEN', $merchant->MERCHANT_CITY)
            ->select('KECAMATAN')->distinct()->get();

        // Ambil Kode Pos berdasarkan Kecamatan yang sudah dipilih merchant
        $kodePos = KabKota::where('KECAMATAN', $merchant->KECAMATAN)
            ->select('KODEPOS')->distinct()->get();

        return view('merchant.edit', compact('merchant', 'merchant_detail', 'mcc', 'criteria', 'merchant_domestic', 'kabKota', 'kecamatan', 'kodePos'));
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Merchant  $merchant
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Merchant $merchant, Mcc $mcc, MerchantDetails $MerchantDetails, MerchantDomestic $MerchantDomestic)
    {

        // dd($request->toArray());


        if ($request->has('merchantType')) {
            return $this->editMcc($request);
        }

        // Validasi dan mendapatkan entitas terkait
        $activity_type = 'REQUEST';
        $comment = 'UPDATE REQUEST';
        $action = 'UPDATE';

        // Mendapatkan data dari request untuk update merchant
        $merchant = Merchant::find($request->ID_MERCHANT);
        $merchant_detail = MerchantDetails::find($request->ID_MERCHANT_DETAILS);
        $merchantDomestic = MerchantDomestic::find($request->ID_MERCHANT_DOMESTIC);

        // Log aktivitas untuk REQUEST
        $raw_request = json_encode($request->toArray());
        // dd($raw_request);
        $this->logMerchantActivityNew($raw_request, null, $comment, $action, $activity_type);


        // $this->logMerchantActivity($merchant, null, null, null, $merchant_detail, $merchantDomestic, $comment, $action, $activity_type);

        if (!isset($request->merchant->NMID)) {

            Log::channel('merchant')->info('UPDATE ==============================================================================');

            $data_merchant = [
                'TERMINAL_LABEL' => 'sometimes|string|max:255',
                'MERCHANT_COUNTRY' => 'sometimes|string|max:255',
                'QRIS_MERCHANT_DOMESTIC_ID' => 'sometimes|numeric',
                'TYPE_QR' => 'sometimes|string|max:255',
                'MERCHANT_NAME' => 'sometimes|string|max:255',
                'MERCHANT_CITY' => 'sometimes|string|max:255',
                'POSTAL_CODE' => 'sometimes|string|max:10',
                'MERCHANT_CURRENCY_CODE' => 'sometimes|string|max:3',
                'MERCHANT_TYPE' => 'sometimes|string|max:255',
                'MERCHANT_EXP' => 'sometimes|date',
                'MERCHANT_ADDRESS' => 'sometimes|string|max:1024',
                'STATUS' => 'sometimes|integer',
                // 'NMID' => 'sometimes|string|max:255',
                'ACCOUNT_NUMBER' => 'sometimes|string|max:255',
                'KTP' => 'nullable|integer',
                'NPWP' => 'nullable|integer',
                'USER_ID_MOBILE' => 'sometimes|numeric',
                'PHONE_MOBILE' => 'sometimes|string|max:15',
                'EMAIL_MOBILE' => 'sometimes|email|max:255',
                'QR_TYPE' => 'sometimes|string|max:10',
                'MERCHANT_TYPE_2' => 'sometimes'
            ];

            $merchant = Merchant::find($request->ID_MERCHANT);
            if (!$merchant) {
                return back()->withErrors(['msg' => 'Merchant not found']);
            }

            $merchant->update([
                // 'TERMINAL_LABEL' => $request->TERMINAL_LABEL,
                // 'MERCHANT_COUNTRY' => $request->MERCHANT_COUNTRY,
                // 'QRIS_MERCHANT_DOMESTIC_ID' => $request->QRIS_MERCHANT_DOMESTIC_ID,
                // 'TYPE_QR' => $request->TYPE_QR,
                'MERCHANT_NAME' => $request->MERCHANT_NAME,
                'MERCHANT_CITY' => $request->MERCHANT_CITY,
                'POSTAL_CODE' => $request->POSTAL_CODE,
                // 'MERCHANT_CURRENCY_CODE' => $request->MERCHANT_CURRENCY_CODE,
                'MERCHANT_TYPE' => $request->MCC,
                // 'MERCHANT_EXP' => $request->MERCHANT_EXP,
                'MERCHANT_ADDRESS' => $request->MERCHANT_ADDRESS,
                // 'STATUS' => $request->STATUS,
                'ACCOUNT_NUMBER' => $request->ACCOUNT_NUMBER,
                'KTP' => $request->KTP,
                'NPWP' => $request->NPWP,
                'USER_ID_MOBILE' => $request->USER_ID_MOBILE,
                'PHONE_MOBILE' => $request->PHONE_MOBILE,
                'EMAIL_MOBILE' => $request->EMAIL_MOBILE,
                'QR_TYPE' => $request->QR_TYPE,
                'MERCHANT_TYPE_2' => $request->MERCHANT_TYPE_2
            ]);

            // dd($request);

            Log::channel('merchant')->info('REQUEST DATA : ' . json_encode($merchant));




            $merchantDetailsId = $request->ID_MERCHANT_DETAILS;
            $MerchantDetails = MerchantDetails::find($merchantDetailsId);
            if (!$MerchantDetails) {
                return back()->withErrors(['msg' => 'Merchant Details not found']);
            }
            $request->validate([
                'CRITERIA' => 'required',
            ]);
            $MerchantDetails->update([
                'CRITERIA' => $request->CRITERIA
            ]);


            $merchantDomesticId = $request->ID_MERCHANT_DOMESTIC;
            $MerchantDomestic = MerchantDomestic::find($merchantDomesticId);
            if (!$MerchantDomestic) {
                return back()->withErrors(['msg' => 'Merchant Domestic not found']);
            }
            $request->validate([
                'MCC' => 'required',
                'CRITERIA' => 'required',
            ]);
            $MerchantDomestic->update([
                'MCC' => $request->MCC,
                'CRITERIA' => $request->CRITERIA
            ]);




            $date = date('Y-m-d H:i:s');
            $nmid = 'ID' . genID(13);
            $nns = '93600521';
            $domain = 'ID.CO.QRIS.WWW';

            Log::channel('merchant')->info('DONE UPDATE TANPA NMID ==============================================================================');


            $activity_type = 'RESPONSE';
            $comment = 'UPDATE SUCCESS';
            $action = 'UPDATE';

            $raw_response = json_encode([
                'merchant' => $merchant,
                'merchant_details' => $merchant_detail,
                'merchant_domestic' => $merchantDomestic
            ]);

            $this->logMerchantActivityNew(null, $raw_response, $comment, $action, $activity_type);

            $request['editMerchantTanpaNmid'] = true;
            $spreadsheet = $this->generateMerchantExcel($request);
            $this->saveExcelFile($spreadsheet, $request, 'add');

            return redirect()
                ->route('merchant.index')
                ->with('success', 'Merchant updated successfully');
        } else {
            $request['editMerchant'] = true;
            Log::channel('merchant')->info('DONE UPDATE NMID ==============================================================================');
            $spreadsheet = $this->generateMerchantExcel($request);
            $this->saveExcelFile($spreadsheet, $request, 'update');
        }
        return redirect()
            ->route('merchant.index')
            ->with('success', 'Merchant updated successfully');
    }

    public function generateMerchantExcel($request)
    {
        // dd($request);

        if ($request->addMerchant === true) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Setup titles
            $sheet->setCellValue('A1', 'FORM PENDAFTARAN');
            $sheet->setCellValue('A2', 'NATIONAL MERCHANT REPOSITORY QRIS');
            $sheet->mergeCells('A1:O1');
            $sheet->mergeCells('A2:O2');

            $sheet->getStyle('A1:O2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A1:O2')->getFont()->setBold(true);

            $sheet->setCellValue('B3', 'Mandatory');
            $sheet->mergeCells('B3:O3');
            $sheet->getStyle('B3:O3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B3:O3')->getFont()->setBold(true);
            $sheet->getStyle('B3')->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFFF00'); // Yellow color for mandatory

            // Headers for columns
            $headers = [
                'A3' => 'No.',
                'B4' => 'NMID',
                'C4' => 'Nama Merchant (max 50)',
                'D4' => 'Nama Merchant (max 25)',
                'E4' => 'MPAN',
                'F4' => 'MID',
                'G4' => 'Kota',
                'H4' => 'Kodepos',
                'I4' => 'Kriteria',
                'J4' => 'MCC',
                'K4' => 'Jml Terminal',
                'L4' => 'Tipe Merchant',
                'M4' => 'NPWP',
                'N4' => 'KTP',
                'O4' => 'Tipe QR'
            ];

            foreach ($headers as $cell => $value) {
                $sheet->getStyle($cell, $value)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFFF00'); // Yellow color for mandatory
                $sheet->setCellValue($cell, $value);
                // Apply border and styling for all headers
                $sheet->getStyle($cell)->applyFromArray([
                    'borders' => [
                        'outline' => [
                            'borderStyle' => Border::BORDER_MEDIUM,
                            'color' => ['argb' => '000000'],
                        ],
                    ],
                ]);
            }

            $sheet->mergeCells('A3:A4');
            $sheet->setCellValue('A3', 'NO');


            $sheet->getColumnDimension('A')->setWidth(5);
            $sheet->getColumnDimension('B')->setWidth(30);
            $sheet->getColumnDimension('C')->setWidth(25);
            $sheet->getColumnDimension('D')->setWidth(20);
            $sheet->getColumnDimension('E')->setWidth(20);
            $sheet->getColumnDimension('F')->setWidth(20);
            $sheet->getColumnDimension('G')->setWidth(25);
            $sheet->getColumnDimension('H')->setWidth(15);
            $sheet->getColumnDimension('I')->setWidth(10);
            $sheet->getColumnDimension('J')->setWidth(15);
            $sheet->getColumnDimension('K')->setWidth(15);
            $sheet->getColumnDimension('L')->setWidth(20);
            $sheet->getColumnDimension('M')->setWidth(10);
            $sheet->getColumnDimension('N')->setWidth(10);
            $sheet->getColumnDimension('O')->setWidth(20);

            // Mengatur style untuk header (dengan border medium)
            $headerStyle = [
                'font' => [
                    'bold' => true,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => [
                        'argb' => 'FFFF00',
                    ],
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_MEDIUM,
                        'color' => ['argb' => '000000'],
                    ],
                ],
            ];

            // Menerapkan style header ke seluruh rentang header (A3:U4)
            $sheet->getStyle('A3:O4')->applyFromArray($headerStyle);

            // Mengatur style untuk data
            $dataStyle = [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['argb' => '000000'],
                    ],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => [
                        'argb' => 'DEEBF7', // Warna biru muda untuk baris data
                    ],
                ],
            ];

            // Menerapkan style data ke seluruh rentang data (A5:U5)
            $sheet->getStyle('A5:O5')->applyFromArray($dataStyle);

            $data = [
                'A5' => '1',
                'B5' => $request->nmid,
                'C5' => $request->merchant,
                'D5' => strlen($request->merchant) > 25 ? substr($request->merchant, 0, 25) : $request->merchant,
                'E5' => "'" . $request->mpan,
                'F5' => $request->mid,
                'G5' => $request->city,
                'H5' => $request->postalcode,
                'I5' => $request->criteria,
                'J5' => $request->mcc,
                'K5' => '1',
                'L5' => $request->merchantTipe,
                'M5' => "'" . $request->npwp,
                'N5' => "'" . $request->ktp,
                'O5' => $request->qrType
            ];

            foreach ($data as $cell => $value) {
                $sheet->setCellValue($cell, $value);
            }

            // $appEnv = getenv('APP_ENV');
            // $dateNow = date('Ymd');
            // $baseDir = '/home/share/test/KBBS_OUT/';
            // $baseDir2 = '/home/share/test/KBBS_OUT/';
            // $folderName = $dateNow;
            // $storagePathProd = $baseDir . $folderName;
            // $storagePathDev = $baseDir2 . $folderName;

            // switch ($appEnv) {
            //     case 'prod':
            //         $storagePath = $storagePathProd;
            //         break;
            //     case 'dev':
            //         $storagePath = $storagePathDev;
            //         break;
            //     case 'local':
            //         $storagePath = null;
            //         break;
            //     default:
            //         die('Invalid environment.');
            // }

            // function getNextBatchNumbers($storagePath, $dateNow)
            // {
            //     $batchNumber = 0;
            //     $firstFileExists = file_exists($storagePath . '/QRIS_NMR_93600521_' . $dateNow . '.xlsx');

            //     if ($firstFileExists) {
            //         $batchNumber = 2;
            //     }

            //     while (file_exists($storagePath . '/QRIS_NMR_93600521_' . $dateNow . '_batch' . $batchNumber . '.xlsx')) {
            //         $batchNumber++;
            //     }

            //     return $batchNumber;
            // }


            // if ($appEnv === 'local') {
            //     $batchNumber = getNextBatchNumbers($storagePath, $dateNow);
            //     $filename = 'QRIS_NMR_93600521_' . $dateNow . ($batchNumber ? '_batch' . $batchNumber : '') . '.xlsx';
            //     header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            //     header('Content-Disposition: attachment; filename="' . $filename . '"');
            //     $writer = new Xlsx($spreadsheet);
            //     $writer->save('php://output');
            //     exit;
            // } else {
            //     if (!file_exists($storagePath)) {
            //         if (!mkdir($storagePath, 0775, true)) {
            //             // Jika pembuatan direktori gagal, catat error dan kirim response error
            //             error_log("Failed to create directory at $storagePath");
            //             return response()->json(['error' => 'Failed to create directory'], 500);
            //         }
            //     }

            //     // Simpan file ke disk untuk environment prod dan dev
            //     $batchNumber = getNextBatchNumbers($storagePath, $dateNow);
            //     $filename = 'QRIS_NMR_93600521_' . $dateNow . ($batchNumber ? '_batch' . $batchNumber : '') . '.xlsx';
            //     $path = $storagePath . '/' . $filename;
            //     $writer = new Xlsx($spreadsheet);
            //     $writer->save($path);
            //     Log::channel('merchant')->info('FILE EXCEL : ' . $path);
            // }
            return $spreadsheet;
        } else if ($request->editMerchant === true) {

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Setup titles
            $sheet->setCellValue('A1', 'FORM UPDATE');
            $sheet->setCellValue('A2', 'NATIONAL MERCHANT REPOSITORY QRIS');
            $sheet->mergeCells('A1:P1'); // Merging title
            $sheet->mergeCells('A2:P2'); // Merging subtitle

            // Applying styles to the merged headers
            $sheet->getStyle('A1:P2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A1:P2')->getFont()->setBold(true);

            // Header row for "Mandatory"
            $sheet->setCellValue('B3', 'Mandatory');
            $sheet->mergeCells('B3:N3');
            $sheet->getStyle('B3:N3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B3:N3')->getFont()->setBold(true);
            $sheet->getStyle('B3')->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFFF00'); // Yellow color for mandatory

            // Headers for columns
            $headers = [
                'A3' => 'No.',
                'B4' => 'Nama Merchant (max 50)',
                'C4' => 'Nama Merchant (max 25)',
                'D4' => 'MPAN',
                'E4' => 'MID',
                'F4' => 'Kota',
                'G4' => 'Kodepos',
                'H4' => 'Kriteria',
                'I4' => 'MCC',
                'J4' => 'Jml Terminal',
                'K4' => 'Tipe Merchant',
                'L4' => 'NPWP',
                'M4' => 'KTP',
                'N4' => 'Tipe QR',
                'O3' => 'NMID',
                'P3' => 'Keterangan UPDATE'
            ];

            foreach ($headers as $cell => $value) {
                $sheet->getStyle($cell)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFFF00'); // Yellow color for mandatory
                $sheet->setCellValue($cell, $value);
                // Apply border and styling for all headers
                $sheet->getStyle($cell)->applyFromArray([
                    'borders' => [
                        'outline' => [
                            'borderStyle' => Border::BORDER_MEDIUM,
                            'color' => ['argb' => '000000'],
                        ],
                    ],
                ]);
            }

            // Merge cells for headers
            $sheet->mergeCells('A3:A4');
            $sheet->mergeCells('O3:O4');
            $sheet->mergeCells('P3:P4');

            $sheet->getColumnDimension('A')->setWidth(5);
            $sheet->getColumnDimension('B')->setWidth(30);
            $sheet->getColumnDimension('C')->setWidth(25);
            $sheet->getColumnDimension('D')->setWidth(20);
            $sheet->getColumnDimension('E')->setWidth(20);
            $sheet->getColumnDimension('F')->setWidth(20);
            $sheet->getColumnDimension('G')->setWidth(10);
            $sheet->getColumnDimension('H')->setWidth(15);
            $sheet->getColumnDimension('I')->setWidth(10);
            $sheet->getColumnDimension('J')->setWidth(15);
            $sheet->getColumnDimension('K')->setWidth(15);
            $sheet->getColumnDimension('L')->setWidth(20);
            $sheet->getColumnDimension('M')->setWidth(10);
            $sheet->getColumnDimension('N')->setWidth(10);
            $sheet->getColumnDimension('O')->setWidth(20);
            $sheet->getColumnDimension('P')->setWidth(20);
            $sheet->getColumnDimension('Q')->setWidth(15);
            $sheet->getColumnDimension('R')->setWidth(15);
            $sheet->getColumnDimension('S')->setWidth(15);
            $sheet->getColumnDimension('T')->setWidth(20);
            $sheet->getColumnDimension('U')->setWidth(20);

            // Mengatur style untuk header (dengan border medium)
            $headerStyle = [
                'font' => [
                    'bold' => true,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => [
                        'argb' => 'FFFF00',
                    ],
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_MEDIUM,
                        'color' => ['argb' => '000000'],
                    ],
                ],
            ];

            // Menerapkan style header ke seluruh rentang header (A3:U4)
            $sheet->getStyle('A3:P4')->applyFromArray($headerStyle);

            // Mengatur style untuk data
            $dataStyle = [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['argb' => '000000'],
                    ],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => [
                        'argb' => 'DEEBF7', // Warna biru muda untuk baris data
                    ],
                ],
            ];

            // Menerapkan style data ke seluruh rentang data (A5:U5)
            $sheet->getStyle('A5:P5')->applyFromArray($dataStyle);


            // Adding actual data at row 5
            $data = [
                'A5' => '1',
                'B5' => $request->MERCHANT_NAME,
                'C5' => strlen($request->MERCHANT_NAME) > 25 ? substr($request->MERCHANT_NAME, 0, 25) : $request->MERCHANT_NAME,
                'D5' => "'" . $request->MPAN,
                'E5' => $request->MID,
                'F5' => $request->MERCHANT_CITY,
                'G5' => $request->POSTAL_CODE,
                'H5' => $request->CRITERIA,
                'I5' => $request->MCC,
                'J5' => '1',
                'K5' => $request->MERCHANT_TYPE_2,
                'L5' => "'" . $request->NPWP,
                'M5' => "'" . $request->KTP,
                'N5' => $request->QR_TYPE,
                'O5' => $request->NMID,
                'P5' => $request->KETERANGAN_UPDATE,
            ];

            foreach ($data as $cell => $value) {
                $sheet->setCellValue($cell, $value);
            }

            return $spreadsheet;
        } else if ($request->editMerchantTanpaNmid === true) {


            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Setup titles
            $sheet->setCellValue('A1', 'FORM PENDAFTARAN');
            $sheet->setCellValue('A2', 'NATIONAL MERCHANT REPOSITORY QRIS');
            $sheet->mergeCells('A1:O1'); // Merging title
            $sheet->mergeCells('A2:O2'); // Merging subtitle

            // Applying styles to the merged headers
            $sheet->getStyle('A1:O2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A1:O2')->getFont()->setBold(true);

            // Header row for "Mandatory"
            $sheet->setCellValue('B3', 'Mandatory');
            $sheet->mergeCells('B3:O3');
            $sheet->getStyle('B3:O3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B3:O3')->getFont()->setBold(true);
            $sheet->getStyle('B3')->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFFF00'); // Yellow color for mandatory

            // Headers for columns
            $headers = [
                'A3' => 'No.',
                'B4' => 'NMID',
                'C4' => 'Nama Merchant (max 50)',
                'D4' => 'Nama Merchant (max 25)',
                'E4' => 'MPAN',
                'F4' => 'MID',
                'G4' => 'Kota',
                'H4' => 'Kodepos',
                'I4' => 'Kriteria',
                'J4' => 'MCC',
                'K4' => 'Jml Terminal',
                'L4' => 'Tipe Merchant',
                'M4' => 'NPWP',
                'N4' => 'KTP',
                'O4' => 'Tipe QR'
            ];

            foreach ($headers as $cell => $value) {
                $sheet->getStyle($cell, $value)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFFF00'); // Yellow color for mandatory
                $sheet->setCellValue($cell, $value);
                // Apply border and styling for all headers
                $sheet->getStyle($cell)->applyFromArray([
                    'borders' => [
                        'outline' => [
                            'borderStyle' => Border::BORDER_MEDIUM,
                            'color' => ['argb' => '000000'],
                        ],
                    ],
                ]);
            }

            // Adding number data spanning rows 3-4
            $sheet->mergeCells('A3:A4');
            $sheet->setCellValue('A3', 'NO');

            $sheet->getColumnDimension('A')->setWidth(5);
            $sheet->getColumnDimension('B')->setWidth(30);
            $sheet->getColumnDimension('C')->setWidth(25);
            $sheet->getColumnDimension('D')->setWidth(20);
            $sheet->getColumnDimension('E')->setWidth(20);
            $sheet->getColumnDimension('F')->setWidth(20);
            $sheet->getColumnDimension('G')->setWidth(10);
            $sheet->getColumnDimension('H')->setWidth(15);
            $sheet->getColumnDimension('I')->setWidth(10);
            $sheet->getColumnDimension('J')->setWidth(15);
            $sheet->getColumnDimension('K')->setWidth(15);
            $sheet->getColumnDimension('L')->setWidth(20);
            $sheet->getColumnDimension('M')->setWidth(10);
            $sheet->getColumnDimension('N')->setWidth(10);
            $sheet->getColumnDimension('O')->setWidth(20);
            $sheet->getColumnDimension('P')->setWidth(20);
            $sheet->getColumnDimension('Q')->setWidth(15);
            $sheet->getColumnDimension('R')->setWidth(15);
            $sheet->getColumnDimension('S')->setWidth(15);
            $sheet->getColumnDimension('T')->setWidth(20);
            $sheet->getColumnDimension('U')->setWidth(20);

            // Mengatur style untuk header (dengan border medium)
            $headerStyle = [
                'font' => [
                    'bold' => true,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => [
                        'argb' => 'FFFF00',
                    ],
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_MEDIUM,
                        'color' => ['argb' => '000000'],
                    ],
                ],
            ];

            // Menerapkan style header ke seluruh rentang header (A3:U4)
            $sheet->getStyle('A3:O4')->applyFromArray($headerStyle);

            // Mengatur style untuk data
            $dataStyle = [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['argb' => '000000'],
                    ],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => [
                        'argb' => 'DEEBF7', // Warna biru muda untuk baris data
                    ],
                ],
            ];

            // Menerapkan style data ke seluruh rentang data (A5:U5)
            $sheet->getStyle('A5:O5')->applyFromArray($dataStyle);

            // Adding actual data at row 5
            $data = [
                'A5' => '1',
                'B5' => $request->NMID,
                'C5' => $request->MERCHANT_NAME,
                'D5' => strlen($request->MERCHANT_NAME) > 25 ? substr($request->MERCHANT_NAME, 0, 25) : $request->MERCHANT_NAME,
                'E5' => "'" . $request->MPAN,  // Menambahkan tanda kutip pada MPAN
                'F5' => $request->MID,
                'G5' => $request->MERCHANT_CITY,
                'H5' => $request->POSTAL_CODE,
                'I5' => $request->CRITERIA,
                'J5' => $request->MCC,
                'K5' => '1',
                'L5' => $request->MERCHANT_TYPE_2,
                'M5' => "'" . $request->NPWP,  // Menambahkan tanda kutip pada NPWP
                'N5' => "'" . $request->KTP,   // Menambahkan tanda kutip pada KTP
                'O5' => $request->QR_TYPE
            ];

            foreach ($data as $cell => $value) {
                $sheet->setCellValue($cell, $value);
            }

            return $spreadsheet;
        } else if ($request->deleteMerchant === true) {

            // dd($request);

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Setup titles
            $sheet->setCellValue('A1', 'FORM PENGHAPUSAN');
            $sheet->setCellValue('A2', 'NATIONAL MERCHANT REPOSITORY QRIS');
            $sheet->mergeCells('A1:P1'); // Merging title
            $sheet->mergeCells('A2:P2'); // Merging subtitle

            // Applying styles to the merged headers
            $sheet->getStyle('A1:P2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A1:P2')->getFont()->setBold(true);

            // Header row for "Mandatory"
            $sheet->setCellValue('B3', 'Mandatory');
            $sheet->mergeCells('B3:N3');
            $sheet->getStyle('B3:N3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B3:N3')->getFont()->setBold(true);
            $sheet->getStyle('B3')->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFFF00'); // Yellow color for mandatory

            // Headers for columns
            $headers = [
                'A3' => 'No.',
                'B4' => 'Nama Merchant (max 50)',
                'C4' => 'Nama Merchant (max 25)',
                'D4' => 'MPAN',
                'E4' => 'MID',
                'F4' => 'Kota',
                'G4' => 'Kodepos',
                'H4' => 'Kriteria',
                'I4' => 'MCC',
                'J4' => 'Jml Terminal',
                'K4' => 'Tipe Merchant',
                'L4' => 'NPWP',
                'M4' => 'KTP',
                'N4' => 'Tipe QR',
                'O3' => 'NMID',
                'P3' => 'Keterangan Delete',
                'Q3' => 'Keterangan Fraud',
                'Q4' => 'Pemilik',
                'R4' => 'HP Pemilik',
                'S4' => 'Alamat Toko',
                'T4' => 'Fraud Detail',
            ];

            foreach ($headers as $cell => $value) {
                $sheet->getStyle($cell)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFFF00'); // Yellow color for mandatory
                $sheet->setCellValue($cell, $value);
                // Apply border and styling for all headers
                $sheet->getStyle($cell)->applyFromArray([
                    'borders' => [
                        'outline' => [
                            'borderStyle' => Border::BORDER_MEDIUM,
                            'color' => ['argb' => '000000'],
                        ],
                    ],
                ]);
            }

            // Merge cells for headers
            $sheet->mergeCells('A3:A4');
            $sheet->mergeCells('O3:O4');
            $sheet->mergeCells('P3:P4');

            $sheet->getColumnDimension('A')->setWidth(5);
            $sheet->getColumnDimension('B')->setWidth(30);
            $sheet->getColumnDimension('C')->setWidth(25);
            $sheet->getColumnDimension('D')->setWidth(20);
            $sheet->getColumnDimension('E')->setWidth(20);
            $sheet->getColumnDimension('F')->setWidth(20);
            $sheet->getColumnDimension('G')->setWidth(10);
            $sheet->getColumnDimension('H')->setWidth(15);
            $sheet->getColumnDimension('I')->setWidth(10);
            $sheet->getColumnDimension('J')->setWidth(15);
            $sheet->getColumnDimension('K')->setWidth(15);
            $sheet->getColumnDimension('L')->setWidth(20);
            $sheet->getColumnDimension('M')->setWidth(10);
            $sheet->getColumnDimension('N')->setWidth(10);
            $sheet->getColumnDimension('O')->setWidth(20);
            $sheet->getColumnDimension('P')->setWidth(20);
            $sheet->getColumnDimension('Q')->setWidth(15);
            $sheet->getColumnDimension('R')->setWidth(15);
            $sheet->getColumnDimension('S')->setWidth(15);
            $sheet->getColumnDimension('T')->setWidth(20);
            $sheet->getColumnDimension('U')->setWidth(20);

            // Mengatur style untuk header (dengan border medium)
            $headerStyle = [
                'font' => [
                    'bold' => true,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => [
                        'argb' => 'FFFF00',
                    ],
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_MEDIUM,
                        'color' => ['argb' => '000000'],
                    ],
                ],
            ];

            // Menerapkan style header ke seluruh rentang header (A3:U4)
            $sheet->getStyle('A3:T4')->applyFromArray($headerStyle);

            // Mengatur style untuk data
            $dataStyle = [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['argb' => '000000'],
                    ],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => [
                        'argb' => 'DEEBF7', // Warna biru muda untuk baris data
                    ],
                ],
            ];

            // Menerapkan style data ke seluruh rentang data (A5:U5)
            $sheet->getStyle('A5:T5')->applyFromArray($dataStyle);

            // Adding actual data at row 5
            $data = [
                'A5' => '1',
                'B5' => $request->MERCHANT_NAME,
                'C5' => strlen($request->MERCHANT_NAME) > 25 ? substr($request->MERCHANT_NAME, 0, 25) : $request->MERCHANT_NAME,
                'D5' => "'" . $request->MPAN,
                'E5' => $request->MID,
                'F5' => $request->MERCHANT_CITY,
                'G5' => $request->POSTAL_CODE,
                'H5' => $request->CRITERIA,
                'I5' => $request->MCC,
                'J5' => '1',
                'K5' => $request->MERCHANT_TYPE_2,
                'L5' => "'" . $request->NPWP,
                'M5' => "'" . $request->KTP,
                'N5' => $request->QR_TYPE,
                'O5' => $request->NMID,
                'P5' => $request->KETERANGAN_UPDATE,
            ];

            foreach ($data as $cell => $value) {
                $sheet->setCellValue($cell, $value);
            }

            return $spreadsheet;
        }
    }

    public function saveExcelFile($spreadsheet, $request, $operationType = 'add')
    {
        // 1. Konfigurasi penyimpanan file berdasarkan environment
        $appEnv = app()->environment();
        $dateNow = now()->format('Ymd');
        $baseDir = '/home/share/test/KBBS_OUT/';
        $folderName = $dateNow;
        $storagePath = $baseDir . $folderName;

        // 2. Fungsi untuk mendapatkan nomor batch berikutnya
        function getNextBatchNumber($storagePath, $dateNow, $operationType)
        {
            $batchNumber = 0;

            // Ganti match expression dengan switch-case
            switch ($operationType) {
                case 'update':
                    $filePrefix = 'UPDATE_QRIS_NMR_';
                    break;
                case 'delete':
                    $filePrefix = 'DELETE_QRIS_NMR_';
                    break;
                default:
                    $filePrefix = 'QRIS_NMR_';
                    break;
            }

            $firstFileExists = file_exists($storagePath . '/' . $filePrefix . '93600521_' . $dateNow . '.xlsx');

            if ($firstFileExists) {
                $batchNumber = 2;
            }

            while (file_exists($storagePath . '/' . $filePrefix . '93600521_' . $dateNow . '_batch' . $batchNumber . '.xlsx')) {
                $batchNumber++;
            }

            return $batchNumber;
        }

        // 3. Logika penyimpanan file berdasarkan environment
        if ($appEnv === 'local') {
            // Download langsung untuk environment local
            $batchNumber = getNextBatchNumber($storagePath, $dateNow, $operationType);

            // Tentukan prefix file berdasarkan operationType (ganti match dengan switch)
            switch ($operationType) {
                case 'update':
                    $filePrefix = 'UPDATE_QRIS_NMR_';
                    break;
                case 'delete':
                    $filePrefix = 'DELETE_QRIS_NMR_';
                    break;
                default:
                    $filePrefix = 'QRIS_NMR_';
                    break;
            }

            $filename = $filePrefix . '93600521_' . $dateNow . ($batchNumber ? '_batch' . $batchNumber : '') . '.xlsx';

            // Set header untuk download
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');

            // Simpan file ke output
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');

            exit; // Hentikan eksekusi script setelah download
        } else {
            // Simpan ke disk untuk environment production atau development

            // Pastikan direktori tujuan ada
            if (!file_exists($storagePath) && !mkdir($storagePath, 0775, true) && !is_dir($storagePath)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $storagePath));
            }

            $batchNumber = getNextBatchNumber($storagePath, $dateNow, $operationType);

            // Ganti match expression dengan switch-case
            switch ($operationType) {
                case 'update':
                    $filePrefix = 'UPDATE_QRIS_NMR_';
                    break;
                case 'delete':
                    $filePrefix = 'DELETE_QRIS_NMR_';
                    break;
                default:
                    $filePrefix = 'QRIS_NMR_';
                    break;
            }

            $filename = $filePrefix . '93600521_' . $dateNow . ($batchNumber ? '_batch' . $batchNumber : '') . '.xlsx';
            $path = $storagePath . '/' . $filename;

            $writer = new Xlsx($spreadsheet);
            $writer->save($path);

            Log::channel('merchant')->info('UPDATED FILE EXCEL : ' . $path);
        }
    }


    public function generateMid(Request $request)
    {
        $nns = '93600521';
        $kodeCabang = str_pad($request->kodeCabang, 2, '0', STR_PAD_LEFT);
        $kodeLokasi = str_pad($request->kodeLokasi, 2, '0', STR_PAD_LEFT);

        // Mengambil ID terakhir dan menambahkannya dengan 1 untuk sequence baru
        $lastMerchant = Merchant::orderBy('ID', 'desc')->first();
        $sequence = $lastMerchant ? $lastMerchant['ID'] + 1 : 1;

        $mid = $kodeCabang . $kodeLokasi . str_pad($sequence, 5, '0', STR_PAD_LEFT);

        return response()->json([
            'success' => true,
            'mid' => $mid
        ]);
    }

    public function categories()
    {

        $mcc = Mcc::orderBy('DESC_MCC')
            ->get();
        // ->toArray();



        // $merchants = $mcc->paginate(5); // Specify the number of items per page (e.g., 5)

        return view('merchant.categories', ['mcc' => $mcc]);
    }

    public function categoriesCreate()
    {
        // $mcc = Mcc::orderBy('DESC_MCC')
        //     ->get()
        //     ->toArray();
        // $criteria = getCriteria();
        // $prov = getWilayah();

        return view('merchant.categoriesCreate');
    }

    public function categoriesEdit($ID)
    {

        // dd($ID);
        $id = Crypt::decrypt($ID);
        $mcc = Mcc::where('NO', $id)->first();

        // dd($mcc);
        return view('merchant.categoriesEdit', compact('mcc'));
    }

    public function exportExcel()
    {
        return Excel::download(new MerchantsExport, 'merchants.xlsx');
    }

    private function storeMcc($request)
    {
        try {
            $data_merchant = [
                'CODE_MCC' => $request->code,
                'DESC_MCC' => $request->desc,
            ];
            Mcc::create($data_merchant);
            return redirect()->route('merchant.categories')->with(['msg' => 'MCC created successfully.']);
        } catch (\Throwable $th) {
            return back()->withErrors(['msg' => 'Merchant created failed. (' . $th->getMessage() . ')']);
        }
    }

    public function editMcc(Request $request)
    {
        if ($request->has('merchantType')) {
            try {
                $validatedData = $request->validate([
                    'ID' => 'required|integer',
                    'CODE_MCC' => 'required|string|max:4',
                    'DESC_MCC' => 'required|string|max:255',
                ]);

                $mcc = Mcc::find($validatedData['ID']);

                if ($mcc) {
                    $mcc->update($validatedData);

                    return redirect()
                        ->route('merchant.categories')
                        ->with('msg', 'MCC updated successfully.');
                } else {
                    return back()->withErrors(['msg' => 'MCC not found.']);
                }
            } catch (\Throwable $th) {
                // Tangani exception dan kembalikan dengan pesan error
                return back()->withErrors(['msg' => 'MCC update failed. (' . $th->getMessage() . ')']);
            }
        }
    }

    public function rekening(Request $request)
    {
        $norek = $request->input('norek');
        try {
            // Asumsikan fungsi cekNorek mengembalikan array dengan 'rc' dan mungkin lebih
            $hasilCek = $this->cekNorek($norek);

            // dd($hasilCek);

            if ($hasilCek['rc'] != '0000') {
                return response()->json([
                    'error' => 'Nomor Rekening tidak valid',
                ]);
            }

            return response()->json([
                'norek' => $hasilCek['norek'],
                'name' => $hasilCek['name'],
                'balance' => number_format($hasilCek['balance'], 0, ',', '.'),
                'rc' => $hasilCek['rc']
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => 'Terjadi kesalahan dalam verifikasi nomor rekening',
            ]);
        }
    }

    public function saldo(Request $request)
    {
        $id = Crypt::decrypt($request->id);
        $merchant = Merchant::where('id', $id)->first();
        try {
            $cek = $this->cekNorek($merchant->ACCOUNT_NUMBER);
            if ($cek['rc'] != '0000') {
                return response()->json([
                    'error' => ' (Invalid Account Number [No Rekening]) ',
                ]);
            } else {
                return response()->json([
                    'norek' => $cek['norek'],
                    'name' => $cek['name'],
                    'balance' => number_format($cek['balance'], 0, ',', '.'),
                ]);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'error' => ' (Invalid Account Number [No Rekening]) ',
            ]);
        }
    }

    public function mutasi(Request $request)
    {
        $id = Crypt::decrypt($request->id);
        $merchant = Merchant::where('id', $id)->first();
        try {
            $cek = $this->cekMutasi($merchant->ACCOUNT_NUMBER);
            if ($cek['rc'] != '0000') {
                return response()->json([
                    'error' => ' (Invalid Account Number [No Rekening]) ',
                ]);
            } else {
                return response()->json(json_decode($cek['json'], true));
            }
        } catch (\Throwable $th) {
            return response()->json([
                'error' => ' (Invalid Account Number) ',
            ]);
        }
    }

    private function logMerchantActivityNew($raw_request, $raw_response, $comment, $action, $activity_type)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                throw new \Exception("User not authenticated");
            }

            $ip_address = request()->ip();

            $merchant_id = null;
            $nmid = null;

            if ($raw_request) {
                $raw_request_data = json_decode($raw_request);

                $merchant_id = isset($raw_request_data->ID_MERCHANT) ? $raw_request_data->ID_MERCHANT : null;
                $nmid = isset($raw_request_data->NMID) ? $raw_request_data->NMID : null;
            }

            if ($raw_response) {
                $raw_response_data = json_decode($raw_response);

                $merchant_id = isset($raw_response_data->merchant->ID) ? $raw_response_data->merchant->ID : $merchant_id;
                $nmid = isset($raw_response_data->merchant->NMID) ? $raw_response_data->merchant->NMID : $nmid;
            }

            $raw_data = $raw_request ? $raw_request : $raw_response;

            QrisMerchantActivity::create([
                'merchant_id' => $merchant_id,
                'nmid' => $nmid,
                'raw' => $raw_data, // Simpan raw_request atau raw_response ke field RAW
                'user_id' => $user->id,
                'action' => $action,
                'ip_address' => $ip_address,
                'comment' => $comment,
                'activity_type' => $activity_type,
            ]);
        } catch (\Exception $e) {
            Log::error('Error logging merchant activity: ' . $e->getMessage());
        }
    }

    private function logMerchantActivity($data_merchant, $data_detail, $data_domestic, $merchants, $merchant_detail, $merchantDomestic, $comment, $action, $activity_type)
    {
        // dd($data_merchant, $data_detail, $data_domestic, json_encode($merchants), $merchant_detail, $merchantDomestic, $comment, $action, $activity_type);
        try {

            // if (!$merchant) {
            //     throw new \Exception("Merchant data not found");
            // }

            // Mendapatkan data mentah dari masing-masing entitas
            $raw_merchant = $merchants ? json_encode($merchants) : $data_merchant;
            $raw_merchant_details = $merchant_detail ? json_encode($merchant_detail) : $data_detail;
            $raw_merchant_dom = $merchantDomestic ? json_encode($merchantDomestic) : $data_domestic;


            // Menggabungkan data request untuk dimasukkan ke dalam RAW_REQUEST
            $raw_request = json_encode([
                'merchant' => $data_merchant,
                'merchant_details' => $data_detail,
                'merchant_domestic' => $data_domestic
            ]);

            // dd($raw_request);

            // Mendapatkan user dan data mentahnya
            $user = Auth::user();
            if (!$user) {
                throw new \Exception("User not authenticated");
            }

            $raw_user = $user->toJson();

            // Simpan aktivitas ke tabel QrisMerchantActivity
            QrisMerchantActivity::create([
                'merchant_id' => $merchants ? $merchants->ID : null,
                'nmid' => $merchants ? $merchants->NMID : null,
                // 'raw_merchant' => $raw_merchant,
                'raw_merchant_details' => $raw_merchant_details,
                'raw_merchant_dom' => $raw_merchant_dom,
                'raw_user' => $raw_user,
                'user_id' => $user->id,
                'action' => $action,
                'ip_address' => request()->ip(),
                'old_value' => '',
                'new_value' => '',
                'comment' => $comment,
                'activity_type' => $activity_type,
                'raw_request' => $raw_request,
            ]);
        } catch (\Exception $e) {
            // dd($e->getMessage());
            Log::error('Error logging merchant activity: ' . $e->getMessage());
        }
    }

    public function delete(Merchant $merchant, $id)
    {

        $id = Crypt::decrypt($id);
        $merchant = Merchant::where('id', $id)->first();
        $mcc = Mcc::orderBy('DESC_MCC')
            ->get()
            ->toArray();
        $criteria = getCriteria();
        $merchant_detail = MerchantDetails::where('MERCHANT_ID', $id)->first();
        $merchant_domestic = MerchantDomestic::where('ID', $merchant->QRIS_MERCHANT_DOMESTIC_ID)->first();
        return view('merchant.delete', compact('merchant', 'merchant_detail', 'mcc', 'criteria', 'merchant_domestic'));
    }

    public function destroy(Request $request)
    {
        try {
            $request['deleteMerchant'] = true;
            Log::channel('merchant')->info('REQ DELETE MERCHANT ==============================================================================');

            if ($request->NMID == null) {

                // Log activity for REQUEST
                $raw_request = json_encode($request->toArray());
                $activity_type = 'REQUEST';
                $comment = 'DELETE REQUEST';
                $action = 'DELETE';
                $this->logMerchantActivityNew($raw_request, null, $comment, $action, $activity_type);
                Log::channel('merchant')->info('DELETE MERCHANT ==============================================================================');

                $merchant = Merchant::find($request->ID_MERCHANT);
                if (!$merchant) {
                    return back()->withErrors(['msg' => 'Merchant not found']);
                }

                $merchant->update([
                    'STATUS' => 2
                ]);

                // Log response
                $raw_response = json_encode([
                    'merchant' => $merchant,
                ]);

                $activity_type = 'RESPONSE';
                $comment = 'DELETE RESPONSE';
                $action = 'DELETE';
                $this->logMerchantActivityNew(null, $raw_response, $comment, $action, $activity_type);
            } else {
                $spreadsheet = $this->generateMerchantExcel($request);
                $this->saveExcelFile($spreadsheet, $request, 'delete');
            }

            return redirect()
                ->route('merchant.index')
                ->with('success', 'Merchant deleted successfully.');
        } catch (\Exception $e) {
            // Log error
            Log::channel('merchant')->error('Error deleting merchant: ' . $e->getMessage());

            // Redirect back with error message
            return back()->withErrors(['msg' => 'An error occurred while deleting the merchant.']);
        }
    }

    public function resendShow(Request $request)
    {
        $getUserId = Auth::id();
        $user = Auth::user();
        $search = $request->input('search');

        $query = DB::table('QRIS_MERCHANT')
            ->whereIn('STATUS', [0, 1])
            ->select('QRIS_MERCHANT.*');

        if (!$user->hasRole(['Admin', 'Superadmin'])) {
            $query->join('user_has_merchant', 'QRIS_MERCHANT.ID', '=', 'user_has_merchant.MERCHANT_ID');
            $query->join('users', 'user_has_merchant.USER_ID', '=', 'users.id');
            $query->where('users.id', $user->id);
        }

        if ($search) {
            $query->where('QRIS_MERCHANT.MERCHANT_NAME', 'like', '%' . $search . '%')->orWhere('QRIS_MERCHANT.NMID', '=', $search);
        }

        $query->groupBy('QRIS_MERCHANT.ID');

        $merchants = $query->paginate(10);

        return view('merchant.resend', ['merchants' => $merchants]);
    }

    public function resendEmail(Request $request)
    {
        try {
            $nmid = Crypt::decrypt($request->nmid);
            Log::channel('merchant')->info('RESEND EMAIL ==============================================================================');

            if (!empty($nmid)) {
                $merchant = DB::table('QRIS_MERCHANT')
                    ->join('user_has_merchant', 'QRIS_MERCHANT.ID', '=', 'user_has_merchant.MERCHANT_ID')
                    ->join('users', 'users.id', '=', 'user_has_merchant.USER_ID')
                    ->where('NMID', $nmid)
                    // ->whereIn('STATUS', [0, 1])
                    ->whereNotIn('user_has_merchant.USER_ID', [1, 2, 67])
                    ->select('QRIS_MERCHANT.*', 'users.email')->first();

                if (empty($merchant)) {
                    throw new Exception("Merchant not found", 1);
                }

                $req = [
                    'merchant_id' => $nmid
                ];

                $customApiBaseUrl = env('API_URL_BE');

                $url = $customApiBaseUrl . '/qris/resend/email';

                $resp = sendAPI($url, $req);

                // Log response
                $raw_response = $resp;

                $activity_type = 'RESPONSE';
                $comment = 'RESEND EMAIL';
                $action = 'RESEND';
                $this->logMerchantActivityNew(null, $raw_response, $comment, $action, $activity_type);
                if ($resp['RC'] != '0000') {
                    throw new Exception($resp['RCM'], 1);
                }

                return redirect()
                    ->route('merchant.resend')
                    ->with('success', 'Resend (' . $merchant->MERCHANT_NAME . ' [' . $nmid . '] email to  ' . $merchant->email . ' successfully.');
            } else {
                throw new Exception("NMID not found", 1);
            }

        } catch (\Exception $e) {
            // Log error
            Log::channel('merchant')->error('Error : ' . $e->getMessage());

            // Redirect back with error message
            return back()->withErrors(['msg' => 'Error : ' . $e->getMessage()]);
        }
    }
}
