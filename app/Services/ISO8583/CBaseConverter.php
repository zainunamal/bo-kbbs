<?php
namespace App\Services\ISO8583;

class CBaseConverter
{

    static function _ascii_hexa_to_bin($sHexa = "")
    {
        $sBin = "";
        $n = strlen($sHexa);
        $nVarLen = 2;
        if ($n % $nVarLen == 0) { // length must be even
            $iStart = 0;
            $i = 0;
            while ($iStart + $nVarLen <= $n) {
                $sBin .= sprintf("%08s", decbin(hexdec(substr($sHexa, $iStart, $nVarLen))));
                $iStart += $nVarLen;
            }
        }

        return $sBin;
    }
// end of _ascii_hexa_to_bin

    static function _ascii_bin_to_hexa($sBin = "")
    {
        $sBin = str_replace(" ", "", $sBin); // delete some probably spaces
        $inhex = "";

        // BINARY to HEX list
        $binary['0000'] = "0";
        $binary['0001'] = "1";
        $binary['0010'] = "2";
        $binary['0011'] = "3";
        $binary['0100'] = "4";
        $binary['0101'] = "5";
        $binary['0110'] = "6";
        $binary['0111'] = "7";
        $binary['1000'] = "8";
        $binary['1001'] = "9";
        $binary['1010'] = "A";
        $binary['1011'] = "B";
        $binary['1100'] = "C";
        $binary['1101'] = "D";
        $binary['1110'] = "E";
        $binary['1111'] = "F";

        //make sets of 4
        for (;;) {
            $calc = strlen($sBin) / 4;
            if (is_numeric($calc) && (intval($calc) == floatval($calc))) {
                break;
            } else {
                $sBin .= 0;
            }
        }

        // translate binary to hex
        for ($i = 0; $i < strlen($sBin); $i = $i + 4) {
            $set = substr($sBin, $i, 4);
            $inhex .= $binary[$set];
        }

        return $inhex;
    }
// end of _ascii_bin_to_hexa
}

// end of CBaseConverter

?>