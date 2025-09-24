<?php

namespace App\Traits;

use App\Services\Common\CekSaldoRequest;
use App\Services\Common\CekSaldoResponse;
use App\Services\Common\CekMutasiRequest;
use App\Services\Common\CekMutasiResponse;

trait Common
{

    private $ip     = '192.168.26.26';
    private $port   = '23791';
    private $to     = '30';
    

    public function cekNorek($norek)
    {
        traceLog("[checkBalanceRequest] start with account_number {$norek}");

        //ConstructRequest
        $req = new CekSaldoRequest();
        $req->SetComponentTmp('norek', $norek);
        $req->SetComponentTmp('dt', date('YmdHis'));

        $req->ConstructStream();
        $requestStream = $req->GetConstructedStream();

        //Send Stream
        $responseStream = sendSocket($this->ip, $this->port, $requestStream);
        $response = false;
        if (!$responseStream) {
            \Log::debug('Balance request is failed. No Response : ');
        } else {
            //ParseResponse
            $result = new CekSaldoResponse($responseStream);
            $result->ExtractDataElement();
            $response = $result->dataElement;
            if ($response['rc'] != '0000') {
                \Log::debug('Balance request is failed. Got RC : ' . $response['rc']);
            }
        }
        traceLog("[sendBalanceRequest] end with account_number {$norek}");
        return $response;
    }

    public function cekMutasi($norek)
    {
        traceLog("[checkMutasiRequest] start with account_number {$norek}");

        //ConstructRequest
        $req = new CekMutasiRequest();
        $req->SetComponentTmp('norek', $norek);
        $req->SetComponentTmp('dt', date('YmdHis'));
        $priv['start_date'] = date('Ymd');
        $priv['end_date'] = date('Ymd');
        $priv['start_idx'] = '1';
        $priv['total_trx'] = '0';
        $priv['mode'] = '0';
        $req->SetComponentTmp('priv', $priv);

        $req->ConstructStream();
        $requestStream = $req->GetConstructedStream();
        
        //Send Stream
        $responseStream = sendSocket($this->ip, $this->port, $requestStream);
        $response = false;
        if (!$responseStream) {
            \Log::debug('Mutasi request is failed. No Response : ');
        } else {
            //ParseResponse
            $result = new CekMutasiResponse($responseStream);
            $result->ExtractDataElement();
            $response = $result->dataElement;
            if ($response['rc'] != '0000') {
                \Log::debug('Mutasi request is failed. Got RC : ' . $response['rc']);
            }
        }
        traceLog("[sendMutasiRequest] end with account_number {$norek}");
        return $response;
    }
}
