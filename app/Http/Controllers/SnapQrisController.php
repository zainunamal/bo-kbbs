<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\MerchantSnapCredentials;
use App\Services\QRIS\Qris;

class SnapQrisController extends Controller
{
    protected $qrisService;

    public function __construct(Qris $qrisService)
    {
        $this->qrisService = $qrisService;
    }

    public function generateQr(Request $request)
    {
        try {
            $filterField = [
                "partnerReferenceNo",
                "feeAmount",
                "merchantId",
                "storeId",
                "amount"
            ];
            $numericField = ["partnerReferenceNo"];
            $dataReq = $request->all();

            $merchantId = trim($request->merchantId);
            $merchant = MerchantSnapCredentials::where('CLIENT_ID', $merchantId)->first();
            if (!$merchant) {
                Log::error('API error [Unauthorized Merchant]');
                throw new \Exception('Unauthorized. [Unknown merchant]', '4014700');
            }

            // mandatory Error
            foreach ($filterField as $value) {
                if (!isset($dataReq[$value]) || empty($dataReq[$value])) {
                    throw new \Exception('Invalid Mandatory Field {' . $value . '}', '4004702');
                }
            }
            // format error
            foreach ($numericField as $value) {
                if (!is_numeric($dataReq[$value])) {
                    throw new \Exception('Invalid Field Format {' . $value . '}', '4004701');
                }
            }
        } catch (\Exception $ex) {
            Log::Error('Generate Qris Error   [' . $ex->getTraceAsString());
            $code = $ex->getCode() != 0 ? $ex->getCode() : '5004700';
            $httpStatus = substr($code, 0, 3);
            $message = (strstr($ex->getMessage(), 'error')) ? 'General Error' : $ex->getMessage();

            Log::Error('Response   [' . json_encode(['responseCode' => $code, 'responseMessage' => $message]) . ']');
            Log::Error('Catch Error ' . $ex->getTraceAsString());

            return response()->json(
                [
                    'responseCode' => (string) $code,
                    'responseMessage' => $message,
                ],
                $httpStatus
            );
        }

        $qris = $this->qrisService->generateQris($request);
        $now = Carbon::now();
        $code = $qris['responseCode'] == '2004700' ? 200 : 400;
        return response()->json($qris, $code)->header('X-TIMESTAMP', $now->format("Y-m-d\TH:i:sP"));
    }

    public function decodeQr(Request $request)
    {
        try {
            $filterField = [
                "partnerReferenceNo",
                "amount",
                "merchantId",
                "scanTime",
                "qrContent"
            ];
            $numericField = ["partnerReferenceNo"];
            $dataReq = $request->all();

            $merchantId = trim($request->merchantId);
            $merchant = MerchantSnapCredentials::where('CLIENT_ID', $merchantId)->first();
            if (!$merchant) {
                Log::error('API error [Unauthorized Merchant]');
                throw new \Exception('Unauthorized. [Unknown merchant]', '4014700');
            }

            // mandatory Error
            foreach ($filterField as $value) {
                if (!isset($dataReq[$value]) || empty($dataReq[$value])) {
                    throw new \Exception('Invalid Mandatory Field {' . $value . '}', '4004702');
                }
            }
            // format error
            foreach ($numericField as $value) {
                if (!is_numeric($dataReq[$value])) {
                    throw new \Exception('Invalid Field Format {' . $value . '}', '4004701');
                }
            }

            echo "lanjutkan";
        } catch (\Exception $ex) {
            Log::Error('Generate Qris Error   [' . $ex->getTraceAsString());
            $code = $ex->getCode() != 0 ? $ex->getCode() : '5004700';
            $httpStatus = substr($code, 0, 3);
            $message = (strstr($ex->getMessage(), 'error')) ? 'General Error' : $ex->getMessage();

            Log::Error('Response   [' . json_encode(['responseCode' => $code, 'responseMessage' => $message]) . ']');
            Log::Error('Catch Error ' . $ex->getTraceAsString());

            return response()->json(
                [
                    'responseCode' => (string) $code,
                    'responseMessage' => $message,
                ],
                $httpStatus
            );
        }
    }

    public function payment(Request $request)
    {

        try {
            $filterField = [
                "partnerReferenceNo",
                "amount",
                "merchantId",
                "subMerchantId",
                "otp",
                "verificationId"
            ];
            $numericField = ["partnerReferenceNo"];
            $dataReq = $request->all();

            $merchantId = trim($request->merchantId);
            $merchant = MerchantSnapCredentials::where('CLIENT_ID', $merchantId)->first();
            if (!$merchant) {
                Log::error('API error [Unauthorized Merchant]');
                throw new \Exception('Unauthorized. [Unknown merchant]', '4014700');
            }

            // mandatory Error
            foreach ($filterField as $value) {
                if (!isset($dataReq[$value]) || empty($dataReq[$value])) {
                    throw new \Exception('Invalid Mandatory Field {' . $value . '}', '4004702');
                }
            }
            // format error
            foreach ($numericField as $value) {
                if (!is_numeric($dataReq[$value])) {
                    throw new \Exception('Invalid Field Format {' . $value . '}', '4004701');
                }
            }

            echo "lanjutkan";
        } catch (\Exception $ex) {
            Log::Error('Generate Qris Error   [' . $ex->getTraceAsString());
            $code = $ex->getCode() != 0 ? $ex->getCode() : '5004700';
            $httpStatus = substr($code, 0, 3);
            $message = (strstr($ex->getMessage(), 'error')) ? 'General Error' : $ex->getMessage();

            Log::Error('Response   [' . json_encode(['responseCode' => $code, 'responseMessage' => $message]) . ']');
            Log::Error('Catch Error ' . $ex->getTraceAsString());

            return response()->json(
                [
                    'responseCode' => (string) $code,
                    'responseMessage' => $message,
                ],
                $httpStatus
            );
        }
    }

    public function notifyQr(Request $request)
    {

    }

    public function queryQr(Request $request)
    {

    }
}
