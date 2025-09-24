<?php
namespace App\Services\ISO8583;

class CISO8583Parser
{

    // internal vars
    private $sISOMsgStream = "";
    private $sMTI = "";
    private $sPrimaryBitMap = "";
    private $sSecondaryBitMap = "";
    private $sTertiaryBitMap = "";
    private $sISOVersion = "2003";
    private $aISODataElements = NULL;
    // debug
    private $bDebug = false;
    private $sLogFileName = "";
    // last error
    private $iErrCode = 0;
    private $sErrMsg = "";

    function __construct($sISOMsgStream = "")
    {
        $this->AssignVars($sISOMsgStream);
    }
// end of construct

    function AssignVars($sISOMsgStream = "")
    {
        $this->sISOMsgStream = $sISOMsgStream;
    }
// end of AssignVars

    function Debug($bDebug = false, $sLogFileName = "")
    {
        $this->bDebug = $bDebug;
        $this->sLogFileName = $sLogFileName;
    }
// end of Debug

    function GetUsedDataElement($sBitMap = "", $iWhichBitMap = 0)
    {
        //echo "[INFO] >> here 1\n";
        $Converter = new CBaseConverter;
        $sBitMapBin = $Converter->_ascii_hexa_to_bin($sBitMap);
        //echo "[INFO] >> here 2 -> sBitMapBin [$sBitMapBin]\n";
        $n = strlen($sBitMapBin);
        $iIdx = 0;
        for ($i = 0; $i < $n; $i++) {
            if ($sBitMapBin[$i] == "1") {
                $aUsed[$iIdx++] = $i + 1 + ($iWhichBitMap * 64);
            }
        }
        //echo "[INFO] >> here 3 -> aUsed [".print_r($aUsed, true)."]\n";

        return $aUsed;
    }
// end of GetUsedDataElement

    function Parse()
    {
        $isoFormat = new ISO8583Format;
        $aISODataElement = $isoFormat->getFormat();
        // var_dump($aISODataElement);

        $bParsed = false;
        $nStreamLen = strlen($this->sISOMsgStream);
        if ($nStreamLen >= 20) { // minimal length of shortest ISO8583 message stream (MTI + PRIMARY BITMAP)
            // determine ISO8583 version
            $sMTIVersion = substr($this->sISOMsgStream, 0, 1);
            switch ($sMTIVersion) {
                case "0": $this->sISOVersion = "1987";
                    break;
                case "1": $this->sISOVersion = "1993";
                    break;
                case "2": $this->sISOVersion = "2003";
                    break;
            }
            //echo "[INFO] sISOVersion [".$this->sISOVersion."]\n";

            if (@isset($aISODataElement[$this->sISOVersion])) { // there is data element definition of parsed ISO8583 message stream
                //echo "[INFO] here\n";
                $this->aISODataElements = array();
                // get MTI
                $this->sMTI = substr($this->sISOMsgStream, 0, 4);
                $this->aISODataElements[0] = $this->sMTI;
                // get bit map
                $this->sPrimaryBitMap = substr($this->sISOMsgStream, 4, 16);
                $this->aISODataElements[1] = $this->sPrimaryBitMap;

                //echo "[INFO] this->aISODataElements [".print_r($this->aISODataElements, true)."]\n";
                $aUsedPrimaryDataElement = array();
                $aUsedPrimaryDataElement = $this->GetUsedDataElement($this->sPrimaryBitMap, 0); // 0 = check primary bitmap
                $nPrimaryDELen = sizeof($aUsedPrimaryDataElement);
                $nSecondaryDELen = 0;
                $nTertiaryDELen = 0;

                //echo "[INFO] aUsedPrimaryDataElement [".print_r($aUsedPrimaryDataElement, true)."]\n";

                if ($nPrimaryDELen > 0) {
                    $iPrimaryStart = 0;
                    $iSecondaryStart = 0;
                    $iTertiaryStart = 0;

                    $iDEStartIdx = 4 + 16;

                    // update bitmap if there is secondary bitmap is defined
                    if ($aUsedPrimaryDataElement[0] == 1) { // de #1 (first bit of primary bitmap) = 1 => also use secondary bitmap
                        $iPrimaryStart = 1;

                        $this->sSecondaryBitMap = substr($this->sISOMsgStream, 20, 16);
                        $this->aISODataElements[1] .= $this->sSecondaryBitMap;

                        $aUsedSecondaryDataElement = array();
                        $aUsedSecondaryDataElement = $this->GetUsedDataElement($this->sSecondaryBitMap, 1); // 1 = check secondary bitmap

                        $nSecondaryDELen = sizeof($aUsedSecondaryDataElement);
                        if ($nSecondaryDELen > 0) {
                            $iDEStartIdx += 16;

                            // update bitmap if there is tertiary bitmap is defined
                            if ($aUsedSecondaryDataElement[0] == 65) { // de #1 (first bit of secondary bitmap) = 1 => also use tertiary bitmap
                                $iSecondaryStart = 1;

                                /* $this->sTertiaryBitMap = substr($this->sISOMsgStream, 36, 16);
                                  $this->aISODataElements[1] .= $this->sTertiaryBitMap;

                                  $aUsedTertiaryDataElement = array();
                                  $aUsedTertiaryDataElement = $this->GetUsedDataElement($this->sTertiaryBitMap, 2, $aUsedTertiaryDataElement); // 2 = check tertiary bitmap

                                  $nTertiaryDELen = sizeof($aUsedTertiaryDataElement);
                                  if ($nTertiaryDELen > 0)
                                  {
                                  $iDEStartIdx += 16;
                                  } */
                            }
                        }
                    }

                    // process primary data element
                    if ($nPrimaryDELen > 0) {
                        for ($i = $iPrimaryStart; $i < $nPrimaryDELen; $i++) {
                            $sFieldType = $aISODataElement[$this->sISOVersion][$aUsedPrimaryDataElement[$i]]["format"];
                            $nFieldLen = 0;
                            if ($sFieldType == "LLVAR") { // LLVAR
                                $nVarLen = 2;
                                $nFieldLen = intval(substr($this->sISOMsgStream, $iDEStartIdx, $nVarLen));
                                $iDEStartIdx += $nVarLen;
                            } else if ($sFieldType == "LLLVAR") { // LLLVAR
                                $nVarLen = 3;
                                $nFieldLen = intval(substr($this->sISOMsgStream, $iDEStartIdx, $nVarLen));
                                $iDEStartIdx += $nVarLen;
                            } else if ($sFieldType == "LLLLVAR") { // LLLLVAR
                                $nVarLen = 4;
                                $nFieldLen = intval(substr($this->sISOMsgStream, $iDEStartIdx, $nVarLen));
                                $iDEStartIdx += $nVarLen;
                            } else { // fixed length
                                $nFieldLen = $aISODataElement[$this->sISOVersion][$aUsedPrimaryDataElement[$i]]["len"];
                            }

                            //echo "[INFO] >> i [$i] aUsedPrimaryDataElement[i] [".$aUsedPrimaryDataElement[$i]."] iDEStartIdx [$iDEStartIdx] nFieldLen [$nFieldLen]\n";
                            $this->aISODataElements[$aUsedPrimaryDataElement[$i]] = substr($this->sISOMsgStream, $iDEStartIdx, $nFieldLen);
                            $iDEStartIdx += $nFieldLen; // next element index
                        }
                    }

                    // process secondary data element
                    if ($nSecondaryDELen > 0) {
                        for ($i = $iSecondaryStart; $i < $nSecondaryDELen; $i++) {
                            $sFieldType = $aISODataElement[$this->sISOVersion][$aUsedSecondaryDataElement[$i]]["format"];
                            $nFieldLen = 0;
                            if ($sFieldType == "LLVAR") { // LLVAR
                                $nVarLen = 2;
                                $nFieldLen = intval(substr($this->sISOMsgStream, $iDEStartIdx, $nVarLen));
                                $iDEStartIdx += $nVarLen;
                            } else if ($sFieldType == "LLLVAR") { // LLLVAR
                                $nVarLen = 3;
                                $nFieldLen = intval(substr($this->sISOMsgStream, $iDEStartIdx, $nVarLen));
                                $iDEStartIdx += $nVarLen;
                            } else if ($sFieldType == "LLLLVAR") { // LLLLVAR
                                $nVarLen = 4;
                                $nFieldLen = intval(substr($this->sISOMsgStream, $iDEStartIdx, $nVarLen));
                                $iDEStartIdx += $nVarLen;
                            } else { // fixed length
                                $nFieldLen = $aISODataElement[$this->sISOVersion][$aUsedSecondaryDataElement[$i]]["len"];
                            }

                            //echo "[INFO] >> i [$i] aUsedSecondaryDataElement[i] [".$aUsedSecondaryDataElement[$i]."] iDEStartIdx [$iDEStartIdx] nFieldLen [$nFieldLen]\n";
                            $this->aISODataElements[$aUsedSecondaryDataElement[$i]] = substr($this->sISOMsgStream, $iDEStartIdx, $nFieldLen);
                            $iDEStartIdx += $nFieldLen; // next element index
                        }
                    }

                    // process primary data element
                    if ($nTertiaryDELen > 0) {
                        for ($i = $iTertiaryStart; $i < $nTertiaryDELen; $i++) {
                            $sFieldType = $aISODataElement[$this->sISOVersion][$aUsedTertiaryDataElement[$i]]["format"];
                            $nFieldLen = 0;
                            if ($sFieldType == "LLVAR") { // LLVAR
                                $nVarLen = 2;
                                $nFieldLen = intval(substr($this->sISOMsgStream, $iDEStartIdx, $nVarLen));
                                $iDEStartIdx += $nVarLen;
                            } else if ($sFieldType == "LLLVAR") { // LLLVAR
                                $nVarLen = 3;
                                $nFieldLen = intval(substr($this->sISOMsgStream, $iDEStartIdx, $nVarLen));
                                $iDEStartIdx += $nVarLen;
                            } else if ($sFieldType == "LLLLVAR") { // LLLLVAR
                                $nVarLen = 4;
                                $nFieldLen = intval(substr($this->sISOMsgStream, $iDEStartIdx, $nVarLen));
                                $iDEStartIdx += $nVarLen;
                            } else { // fixed length
                                $nFieldLen = $aISODataElement[$this->sISOVersion][$aUsedTertiaryDataElement[$i]]["len"];
                            }

                            //echo "[INFO] >> i [$i] aUsedTertiaryDataElement[i] [".$aUsedTertiaryDataElement[$i]."] iDEStartIdx [$iDEStartIdx] nFieldLen [$nFieldLen]\n";
                            $this->aISODataElements[$aUsedTertiaryDataElement[$i]] = substr($this->sISOMsgStream, $iDEStartIdx, $nFieldLen);
                            $iDEStartIdx += $nFieldLen; // next element index
                        }
                    }

                    $bParsed = true;
                } else {
                    // no data elements are defined -> shortest ISO8583 message (MTI + PRIMARY BITMAP ONLY)
                    // so, NOTHING TO DO
                    $bParsed = true;
                }
            } else {
                $this->iErrCode = -101;
                $this->sErrMsg = "Undefined ISO8583:" . $this->sISOVersion . " data element definition";
                //echo "[ERROR] ".$this->sErrMsg."\n";
            }
        } else {
            $this->iErrCode = -102;
            $this->sErrMsg = "length is less than minimal (20)";
            //echo "[ERROR] ".$this->sErrMsg."\n";
        }

        return $bParsed;
    }
// end of Parse

    function GetParsedDataElement()
    {
        return $this->aISODataElements;
    }
// end of GetParsedDataElement

    // LAST ERROR
    function GetLastErrorCode()
    {
        return $this->iErrCode;
    }
// end of GetLastErrorCode

    function GetLastErrorMsg()
    {
        return $this->sErrMsg;
    }
// end of GetLastErrorMsg
}

// end of CISO8583Parser