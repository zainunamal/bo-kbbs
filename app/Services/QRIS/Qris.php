<?php

namespace App\Services\QRIS;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Carbon\Carbon;

class Qris
{

    public function generateQris(Request $request)
    {
        Log::channel('apiclient')->info('==============================');
        Log::channel('apiclient')->info('HEADER PAYLOAD : ', $request->headers->all());
        Log::channel('apiclient')->info('REQ PAYLOAD : ' . json_encode($request->all()));

        $merchant = auth('snap')->user();
        $dataRequest = $request->all();
        $expiredDate = Carbon::parse($dataRequest['validityPeriod']);

        $qrisPayload = [
            'MPI' => [
                "MERCHANT_ID" => $merchant->MERCHANT_ID,
                "AMOUNT" => intval($dataRequest['amount']['value']),
                // "TIP_INDICATOR" => null,
                "FEE_AMOUNT" => intval($dataRequest['feeAmount']['value']),
                // "FEE_AMOUNT_PERCENTAGE" => null,
                "TYPE" => "DINAMIS",
                "EXPIRE_DATE_TIME" => $expiredDate->format('Y-m-d H:i:s')
            ]
        ];

        $qrisGeneratorUrl = env('API_URL') . '/v1/api/aquerier/create/qr';
        Log::channel('apiclient')->info("GET QRIS REQUEST [$qrisGeneratorUrl] :" . json_encode($qrisPayload));
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post(
                    $qrisGeneratorUrl,
                    $qrisPayload
                );

            Log::channel('apiclient')->info("GET QRIS RESPONSE [$qrisGeneratorUrl] : " . $response->body());

            $result = $response->json();
        } catch (\Throwable $th) {
            Log::error('Exception occurred', [
                'message' => $th->getMessage(),
                'file' => $th->getFile(),
                'line' => $th->getLine(),
                'trace' => $th->getTraceAsString(),
            ]);

            Log::channel('apiclient')->info('GET QRIS RESPONSE ERROR : ' . $th->getMessage());
            $result = [
                'RC' => '0005',
                'RM' => $th->getMessage(),
            ];
            Log::channel('apiclient')->info('GET QRIS RESPONSE : ' . json_encode($result));
        }

        $responseMessage = $this->constructResponseMessage($request, $result, 'generate');

        return $responseMessage;
    }

    public function constructResponseMessage($request, $data, $step)
    {
        $merchant = auth('snap')->user()->load("merchant");
        $responseMessage = [];
        switch ($data['RC']) {
            case '0000':
                switch ($step) {
                    case 'generate':
                        $responseMessage['responseCode'] = "2004700";
                        $responseMessage['responseMessage'] = "Successful";
                        $responseMessage['referenceNo'] = $data['MPO']['UUID'];
                        $responseMessage['partnerReferenceNo'] = $data['MPO']['TRX_ID'];
                        $responseMessage['qrContent'] = null;
                        $responseMessage['qrUrl'] = null;
                        $responseMessage['qrImage'] = $data['MPO']['QRIS'];
                        $responseMessage['redirectUrl'] = null;
                        $responseMessage['merchantName'] = $merchant->merchant->MERCHANT_NAME;
                        $responseMessage['storeId'] = $request['storeId'];
                        $responseMessage['terminalId'] = $request['terminalId'];
                        $responseMessage['additionalInfo'] = $request['additionalInfo'];
                        break;
                    case 'decode':
                        $responseMessage['responseCode'] = "2004800";
                        $responseMessage['responseMessage'] = "Successful";
                        $responseMessage['referenceNo'] = $data['MPO']['UUID'];
                        $responseMessage['partnerReferenceNo'] = $data['MPO']['TRX_ID'];
                        $responseMessage['redirectUrl'] = null;
                        $responseMessage['merchantName'] = $merchant->merchant->MERCHANT_NAME;
                        $responseMessage['merchantCategory'] = null;
                        $responseMessage['merchantLocation'] = null;
                        $responseMessage['merchantInfos'] = null;
                        $responseMessage['transactionAmount'] = [
                            'value' => $data['MPO']['AMOUNT'],
                            'currency' => 'IDR'
                        ];
                        $responseMessage['feeAmount'] = [
                            'value' => $data['MPO']['FEE_AMOUNT'],
                            'currency' => 'IDR'
                        ];
                        $responseMessage['additionalInfo'] = $request['additionalInfo'];
                        break;
                    case 'payment':
                        $responseMessage['responseCode'] = "2005000";
                        $responseMessage['responseMessage'] = "Successful";
                        $responseMessage['referenceNo'] = $data['MPO']['UUID'];
                        $responseMessage['partnerReferenceNo'] = $data['MPO']['TRX_ID'];
                        $responseMessage['transactionDate'] = null;
                        $responseMessage['amount'] = [
                            'value' => $data['MPO']['AMOUNT'],
                            'currency' => 'IDR'
                        ];
                        $responseMessage['feeAmount'] = [
                            'value' => $data['MPO']['FEE_AMOUNT'],
                            'currency' => 'IDR'
                        ];
                        $responseMessage['verificationId'] = null;
                        $responseMessage['additionalInfo'] = $request['additionalInfo'];
                        break;
                    case 'query':
                        $responseMessage['responseCode'] = "2005100";
                        $responseMessage['responseMessage'] = "Successful";
                        $responseMessage['originalReferenceNo'] = $data['MPO']['UUID'];
                        $responseMessage['originalPartnerReferenceNo'] = $data['MPO']['TRX_ID'];
                        $responseMessage['originalExternalId'] = null;
                        $responseMessage['serviceCode'] = null;
                        $responseMessage['latestTransactionStatus'] = $data['MPO']['STATUS'];
                        $responseMessage['transactionStatusDesc'] = null;
                        $responseMessage['paidTime'] = null;
                        $responseMessage['amount'] = [
                            'value' => $data['MPO']['AMOUNT'],
                            'currency' => 'IDR'
                        ];
                        $responseMessage['feeAmount'] = [
                            'value' => $data['MPO']['FEE_AMOUNT'],
                            'currency' => 'IDR'
                        ];
                        $responseMessage['terminalId'] = null;
                        $responseMessage['additionalInfo'] = [
                            'deviceId' => null,
                            'channel' => null
                        ];
                        break;
                    case 'notify':
                        $responseMessage['responseCode'] = "2005200";
                        $responseMessage['responseMessage'] = "Successful";
                        $responseMessage['additionalInfo'] = [
                            'deviceId' => null,
                            'channel' => null
                        ];
                        break;
                }
            default:
                $responseMessage['responseCode'] = "5004701";
                $responseMessage['responseMessage'] = "Internal Server Error";
                break;
        }

        return $responseMessage;
    }

    public function paymentQris(Request $request)
    {
        Log::channel('apiclient')->info('==============================');
        Log::channel('apiclient')->info('HEADER PAYLOAD : ', $request->headers->all());
        Log::channel('apiclient')->info('REQ PAYLOAD : ' . json_encode($request->all()));

        $merchant = auth('snap')->user();
        $dataRequest = $request->all();
        $expiredDate = Carbon::parse($dataRequest['validityPeriod']);

        $qrisPayload = [
            'MPI' => [
                "ACCOUNTS" => [
                    "CROSS_BORDER" => "",
                    "ACCOUNT_NUMBER" => "",
                    "CUSTOMER_DATA" => "",
                    "AMOUNT_FEE_CHARGE" => "",
                    "AMOUNT" => "",
                    "PROCESSING_CODE" => ""
                ],
                "QRIS" => $dataRequest['qrContent'],
                "MPO" => [
                ]
            ]
        ];

        $qrisGeneratorUrl = env('API_URL') . '/v1/api/qris/payment';
        Log::channel('apiclient')->info("QRIS PAYMENT REQUEST [$qrisGeneratorUrl] :" . json_encode($qrisPayload));
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post(
                    $qrisGeneratorUrl,
                    $qrisPayload
                );

            Log::channel('apiclient')->info("QRIS PAYMENT RESPONSE [$qrisGeneratorUrl] : " . $response->body());

            $result = $response->json();
        } catch (\Throwable $th) {
            Log::error('Exception occurred', [
                'message' => $th->getMessage(),
                'file' => $th->getFile(),
                'line' => $th->getLine(),
                'trace' => $th->getTraceAsString(),
            ]);

            Log::channel('apiclient')->info('QRIS PAYMENT RESPONSE ERROR : ' . $th->getMessage());
            $result = [
                'RC' => '0005',
                'RM' => $th->getMessage(),
            ];
            Log::channel('apiclient')->info('QRIS PAYMENT RESPONSE : ' . json_encode($result));
        }

        $responseMessage = $this->constructResponseMessage($request, $result, 'payment');

        return $responseMessage;
    }
}