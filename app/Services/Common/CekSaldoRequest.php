<?php

namespace App\Services\Common;

use App\Services\ISO8583\CISO8583Message;

class CekSaldoRequest extends CISO8583Message
{
    private $mti = '2200';
    private $pc = '310000';

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
        elseif ($sKey == 'balance')
            $iKey = 48;
        elseif ($sKey == 'name')
            $iKey = 60;
        elseif ($sKey == 'userid')
            $iKey = 61;
        elseif ($sKey == 'currency')
            $iKey = 62;
        return $iKey;
    }

    private function ConstructPrivateData($aPriv)
    {
        $sPriv = '';
        $sPriv .= str_pad($aPriv['sid'], 7, "0", STR_PAD_LEFT);
        $sPriv .= str_pad($aPriv['area_code'], 6, "0", STR_PAD_LEFT);
        $sPriv .= str_pad($aPriv['tax_type'], 4, "0", STR_PAD_LEFT);
        $sPriv .= $aPriv['flag'];
        $sPriv .= str_pad($aPriv['nop_npwp'], 32, " ", STR_PAD_RIGHT);

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
