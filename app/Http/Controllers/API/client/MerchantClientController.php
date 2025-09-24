<?php

namespace App\Http\Controllers\API\client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use App\Models\Merchant;
use App\Models\Mcc;
use App\Models\MerchantDetails;
use App\Models\MerchantDomestic;
use App\Models\UserMerchant;
use Illuminate\Support\Facades\Http;

class MerchantClientController extends Controller
{
    public function apiIndex(Request $request)
    {
        $getUserId = Auth::id();
        $userId = $getUserId;


        $merchant = Merchant::latest();

        $merchants = $merchant->get();

        return response()->json(['merchants' => $merchants]);

    }

    public function apiStore(Request $request)
    {

        try {
            $request->validate([
                'norek' => 'required|numeric',
                'merchant' => 'required',
                'mcc' => 'required',
                'criteria' => 'required',
                'prov' => 'required',
                'city' => 'required',
                'address' => 'required',
                'postalcode' => 'required',
                'fee' => 'required',
            ]);
            $date = date('Y-m-d H:i:s');
            $nmid = 'ID' . genID(13);
            $nns = '93600521';
            $domain = 'ID.CO.QRIS.WWW';
            $data_domestic = [
                'REVERSE_DOMAIN' => $domain,
                'NMID' => $nmid,
                'MCC' => $request->mcc,
                'CRITERIA' => $request->criteria,
            ];

            DB::beginTransaction();
            $id_domestic = MerchantDomestic::create($data_domestic)->ID;

            $data_merchant = [
                'CREATED_AT' => $date,
                'UPDATED_AT' => '',
                'TERMINAL_LABEL' => 'K19',
                'MERCHANT_COUNTRY' => 'ID',
                'QRIS_MERCHANT_DOMESTIC_ID' => $id_domestic,
                'TYPE_QR' => 'STATIS',
                'MERCHANT_NAME' => $request->merchant,
                'MERCHANT_CITY' => $request->city,
                'POSTAL_CODE' => $request->postalcode,
                'MERCHANT_CURRENCY_CODE' => '360',
                'MERCHANT_TYPE' => $request->mcc,
                'MERCHANT_EXP' => '900',
                'MERCHANT_CODE' => genID(5, true),
                'MERCHANT_ADDRESS' => $request->address,
                'STATUS' => '1',
                'NMID' => $nmid,
                'ACCOUNT_NUMBER' => $request->norek,
            ];

            $merchants = Merchant::create($data_merchant);

            $merchant_id = $merchants->ID;

            $data_user_has_merchant = [
                'USER_ID' => Auth::id(),
                'MERCHANT_ID' => $merchant_id,
            ];

            UserMerchant::create($data_user_has_merchant);

            $data_detail = [
                'MERCHANT_ID' => $merchant_id,
                'DOMAIN' => $domain,
                'TAG' => '26',
                'MPAN' => $nns . $request->norek,
                'MID' => $nmid,
                'CRITERIA' => $request->criteria,
            ];
            MerchantDetails::create($data_detail);

            DB::commit();

            $param = [
                'MERCHANT_DOMESTIC' => $data_domestic,
                'MERCHANT' => $data_merchant,
                'MERCHANT_DETAIL' => $data_detail,
            ];

            $res = Http::timeout(10)->withBasicAuth('username', 'password')->post('http://192.168.26.26:10002/merchant.php?cmd=add', $param);

            return response()->json(['message' => 'Merchant created successfully']);
        } catch (\Throwable $th) {
            DB::rollBack();
            \Log::error('Merchant creation failed', [
                'message' => $th->getMessage(),
                'file' => $th->getFile(),
                'line' => $th->getLine()
            ]);

            return response()->json(['error' => 'Merchant creation failed: ' . $th->getMessage()], 500);
        }
    }
}