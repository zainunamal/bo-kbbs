<?php

namespace App\Services\Common;

use App\Services\ISO8583\CISO8583Message;

class CekMutasiRequest extends CISO8583Message
{
    private $mti = '2200';
    private $pc = '340001';

    function __construct()
    {
        parent::__construct();
        $this->SetVersion("2003");

        $this->SetValueForDataElement(0, $this->mti);
        $this->SetValueForDataElement(3, $this->pc);
    }

    private function GetMappingKeyIdx($sKey)
    {
        if ($sKey == 'norek')
            $iKey = 2;
        elseif ($sKey == 'pc')
            $iKey = 3;
        elseif ($sKey == 'dt')
            $iKey = 12;
        elseif ($sKey == 'priv')
            $iKey = 48;
        elseif ($sKey == 'json')
            $iKey = 72;
        return $iKey;
    }

    private function ConstructPrivateData($aPriv)
    {
        $sPriv = '';
        $sPriv .= isset($aPriv['start_date']) ? $aPriv['start_date'] : date('Ymd');
        $sPriv .= isset($aPriv['end_date']) ? $aPriv['end_date'] : date('Ymd');
        $sPriv .= str_pad($aPriv['start_idx'], 4, "0", STR_PAD_LEFT);
        $sPriv .= str_pad($aPriv['total_trx'], 4, "0", STR_PAD_LEFT);
        $sPriv .= str_pad($aPriv['mode'], 1, "0", STR_PAD_LEFT);

        return $sPriv;
    }

    public function SetComponentTmp($sKey, $value)
    {
        $keyIdx = $this->GetMappingKeyIdx($sKey);
        if ($sKey == 'priv') {
            $value = $this->ConstructPrivateData($value);
        } elseif ($sKey == 'stan') {
            $value = str_pad($value, 12, "0", STR_PAD_LEFT);
        } elseif ($sKey == 'central_id') {
            $value = str_pad($value, 7, "0", STR_PAD_LEFT);
        }

        $this->SetValueForDataElement($keyIdx, $value);
    }
}
