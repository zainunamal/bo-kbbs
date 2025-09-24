<?php

namespace App\Services\Common;

use App\Services\ISO8583\CISO8583Parser;
use App\Services\ISO8583\ProtocolGeneric;

class CekMutasiResponse extends CISO8583Parser
{
    public $dataElement = array();
    public $privateData = array();
    public $privateDataSingle = array();
    public $detailData = array();
    public $trxData = array();

    function __construct($isoStream)
    {
        parent::__construct($isoStream);
    }

    private function GetMappingValue($idx)
    {
        switch ($idx) {
            case 0:
                $sKey = 'mti';
                break;
            case 1:
                $sKey = 'bitmap';
                break;
            case 2:
                $sKey = 'norek';
                break;
            case 3:
                $sKey = 'pc';
                break;
            case 12:
                $sKey = 'dt';
                break;
            case 39:
                $sKey = 'rc';
                break;
            case 48:
                $sKey = 'priv';
                break;
            case 72:
                $sKey = 'json';
                break;
        }
        return $sKey;
    }

    public function ExtractDataElement()
    {
        if ($this->Parse()) {
            $rDataElmt = $this->GetParsedDataElement();
            foreach ($rDataElmt as $iKey => $value) {
                $sKey = $this->GetMappingValue($iKey);
                if ($sKey == 'balance') {
                    $value = intval($value);
                }
                $this->dataElement[$sKey] = $value;
            }
        }
    }
}
