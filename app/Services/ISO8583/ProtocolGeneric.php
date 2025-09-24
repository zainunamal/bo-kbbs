<?php

namespace App\Services\ISO8583;

class ProtocolGeneric
{

  // input
  private $sStream = '';
  private $aBlockHeaderLength = null;
  private $aBlockSingleLength = null;
  private $aBlockRepeatLength = null;
  private $aBlockTrackingLength = null;
  private $aBlockHeaderAssocName = null;
  private $aBlockSingleAssocName = null;
  private $aBlockRepeatAssocName = null;
  private $aBlockTrackingAssocName = null;
  private $sRepeatKey = '';

  private $aComponentTmp = null;

  // output
  private $aComponentHeader = null;
  private $aComponentSingle = null;
  private $aComponentRepeat = null;
  private $aComponentTracking = null;
  private $aComponentHeaderNormal = null;
  private $aComponentSingleNormal = null;
  private $aComponentRepeatNormal = null;
  private $aComponentTrackingNormal = null;

  function __construct()
  {
  } // end of __construct

  public function _InitArrayVar(&$aVar)
  {
    $n = sizeof($aVar);
    for ($i = 0; $i < $n; $i++) {
      if (is_array($aVar[0])) {
        $n2 = sizeof($aVar[0]);
        for ($j = 0; $j < $n2; $j++) {
          array_shift($aVar[0]);
        }
      }
      array_shift($aVar);
    }
    $aVar = array();
  } // end of _InitVar

  public function InitVars()
  {
    $this->sStream = '';
    _InitArrayVar($this->aComponentHeader);
    $this->aComponentHeader = null;
    _InitArrayVar($this->aComponentSingle);
    $this->aComponentSingle = null;
    _InitArrayVar($this->aComponentRepeat);
    $this->aComponentRepeat = null;
    _InitArrayVar($this->aComponentTracking);
    $this->aComponentTracking = null;
    $this->sRepeatKey = '';
    _InitArrayVar($this->aComponentTmp);
    $this->aComponentTmp = null;
    _InitArrayVar($this->aComponentHeaderNormal);
    $this->aComponentHeaderNormal = null;
    _InitArrayVar($this->aComponentSingleNormal);
    $this->aComponentSingleNormal = null;
    _InitArrayVar($this->aComponentTrackingNormal);
    $this->aComponentTrackingNormal = null;
  } // end of InitVars

  public function SetStream($sStream = '')
  {
    $this->sStream = $sStream;
  } // end of SetStream

  public function GetStream()
  {
    return $this->sStream;
  } // end of GetStream

  public function SetBlockHeaderLengthArray($aNewBlockHeaderLength)
  {
    $this->aBlockHeaderLength = $aNewBlockHeaderLength;
  } // end of SetBlockHeaderLength

  public function GetBlockHeaderLengthArray()
  {
    return $this->aBlockHeaderLength;
  } // end of GetBlockHeaderLength

  public function SetBlockSingleLengthArray($aNewBlockSingleLength)
  {
    $this->aBlockSingleLength = $aNewBlockSingleLength;
  } // end of SetBlockSingleLength

  public function GetBlockSingleLengthArray()
  {
    return $this->aBlockSingleLength;
  } // end of GetBlockSingleLength

  public function SetBlockRepeatLengthArray($aNewBlockRepeatLength)
  {
    $this->aBlockRepeatLength = $aNewBlockRepeatLength;
  } // end of SetBlockRepeatLength

  public function GetBlockRepeatLengthArray()
  {
    return $this->aBlockRepeatLength;
  } // end of GetBlockRepeatLength

  public function SetBlockTrackingLengthArray($aNewBlockTrackingLength)
  {
    $this->aBlockTrackingLength = $aNewBlockTrackingLength;
  } // end of SetBlockTrackingLength

  public function GetBlockTrackingLengthArray()
  {
    return $this->aBlockTrackingLength;
  } // end of GetBlockTrackingLength

  public function SetBlockHeaderAssocNameArray($aNewBlockHeaderAssocName)
  {
    $this->aBlockHeaderAssocName = $aNewBlockHeaderAssocName;
  } // end of SetBlockHeaderAssocNameArray

  public function GetBlockHeaderAssocNameArray()
  {
    return $this->aBlockHeaderAssocName;
  } // end of GetBlockHeaderAssocNameArray

  public function SetBlockSingleAssocNameArray($aNewBlockSingleAssocName)
  {
    $this->aBlockSingleAssocName = $aNewBlockSingleAssocName;
  } // end of SetBlockSingleAssocNameArray

  public function GetBlockSingleAssocNameArray()
  {
    return $this->aBlockSingleAssocName;
  } // end of GetBlockSingleAssocNameArray

  public function SetBlockRepeatAssocNameArray($aNewBlockRepeatAssocName)
  {
    $this->aBlockRepeatAssocName = $aNewBlockRepeatAssocName;
  } // end of SetBlockRepeatAssocNameArray

  public function GetBlockRepeatAssocNameArray()
  {
    return $this->aBlockRepeatAssocName;
  } // end of GetBlockRepeatAssocNameArray

  public function SetBlockTrackingAssocNameArray($aNewBlockTrackingAssocName)
  {
    $this->aBlockTrackingAssocName = $aNewBlockTrackingAssocName;
  } // end of SetBlockTrackingAssocNameArray

  public function GetBlockTrackingAssocNameArray()
  {
    return $this->aBlockTrackingAssocName;
  } // end of GetBlockTrackingAssocNameArray

  public function SetComponentHeaderArray($aNewComponentHeader)
  {
    $this->aComponentHeader = $aNewComponentHeader;
  } // end of SetComponentHeaderArray

  public function GetComponentHeaderArray()
  {
    return $this->aComponentHeader;
  } // end of GetComponentHeaderArray

  public function SetComponentSingleArray($aNewComponentSingle)
  {
    $this->aComponentSingle = $aNewComponentSingle;
  } // end of SetComponentSingleArray

  public function GetComponentSingleArray()
  {
    return $this->aComponentSingle;
  } // end of GetComponentSingleArray

  public function SetComponentRepeatArray($aNewComponentRepeat)
  {
    $this->aComponentRepeat = $aNewComponentRepeat;
  } // end of SetComponentRepeatArray

  public function GetComponentRepeatArray()
  {
    return $this->aComponentRepeat;
  } // end of GetComponentRepeat

  public function SetComponentTrackingArray($aNewComponentTracking)
  {
    $this->aComponentTracking = $aNewComponentTracking;
  } // end of SetComponentTrackingArray

  public function GetComponentTrackingArray()
  {
    return $this->aComponentTracking;
  } // end of GetComponentTracking

  public function SetComponentTmpArray($aNewComponentTmp)
  {
    $this->aComponentTmp = $aNewComponentTmp;
  } // end of SetComponentTmpArray

  public function GetComponentTmpArray()
  {
    return $this->aComponentTmp;
  } // end of GetComponentTmp

  public function SetRepeatKey($sNewRepeatKey)
  {
    $this->sRepeatKey = $sNewRepeatKey;
  } // end of SetRepeatKey

  public function GetRepeatKey()
  {
    return $this->sRepeatKey;
  } // end of GetRepeatKey

  // $Val = mixed
  public function SetComponentHeader($sKey, $Val)
  {
    $this->aComponentHeader[$sKey] = $Val;
  } // end of SetComponentHeader

  public function GetComponentHeader($sKey)
  {
    return (isset($this->aComponentHeader[$sKey]) ? $this->aComponentHeader[$sKey] : null);
  } // end of GetComponentHeader

  // $Val = mixed
  public function SetComponentSingle($sKey, $Val)
  {
    $this->aComponentSingle[$sKey] = $Val;
  } // end of SetComponentSingle

  public function GetComponentSingle($sKey)
  {
    return (isset($this->aComponentSingle[$sKey]) ? $this->aComponentSingle[$sKey] : null);
  } // end of GetComponentSingle

  // $Val = mixed
  public function SetComponentRepeat($sKey, $Val)
  {
    $this->aComponentRepeat[$sKey] = $Val;
  } // end of SetComponentRepeat

  public function GetComponentRepeat($sKey)
  {
    return (isset($this->aComponentRepeat[$sKey]) ? $this->aComponentRepeat[$sKey] : null);
  } // end of GetComponentRepeat

  // $Val = mixed
  public function SetComponentTracking($sKey, $Val)
  {
    $this->aComponentTracking[$sKey] = $Val;
  } // end of SetComponentTracking

  public function GetComponentTracking($sKey)
  {
    return (isset($this->aComponentTracking[$sKey]) ? $this->aComponentTracking[$sKey] : null);
  } // end of GetComponentTracking

  // $Val = mixed
  public function SetComponentTmp($sKey, $Val)
  {
    $this->aComponentTmp[$sKey] = $Val;
  } // end of SetComponentTmp

  public function GetComponentTmp($sKey)
  {
    return (isset($this->aComponentTmp[$sKey]) ? $this->aComponentTmp[$sKey] : null);
  } // end of GetComponentTmp

  // sizeof($this->aBlockHeaderLength) == sizeof($this->aBlockHeaderAssocName)
  // $this->sStream != '';
  // $sVal = scalar & string, strlen($sVal) must be equal to block size represented by the $sKey
  public function SetComponentHeaderInStream($sKey, $sVal)
  {
    $sNewVal = '';

    //echo "[$sKey] [$sVal]\n";
    if ($this->GetStream() != '') {
      $bFound = false;
      $n = sizeof($this->aBlockHeaderLength);
      $i = 0;
      $iFound = 0;
      $iStartIdx = 0;
      while ((!$bFound) && ($i < $n)) {
        //echo ">> [".$this->aBlockHeaderAssocName[$i]."]\n";
        if ($this->aBlockHeaderAssocName[$i] == $sKey)
          $bFound = true;
        else {
          $iStartIdx += $this->aBlockHeaderLength[$i];
          $i++;
        }
      }
      if ($bFound) {
        // replace old value with the new one
        $s = substr($this->sStream, 0, $iStartIdx);
        $sFormat = sprintf("%%0%ds", $this->aBlockHeaderLength[$i]);
        $sNewVal = sprintf($sFormat, substr($sVal, 0, $this->aBlockHeaderLength[$i])); // ensure that the new value has allowed number of digits/characters
        //echo "sID [$sID]\n";
        $s .= $sNewVal . substr($this->sStream, $iStartIdx + $this->aBlockHeaderLength[$i]);
        // set $this->sStream with new stream
        $this->SetStream($s);
      } else {
        //echo "not found\n";
      }
    }

    // if OK, $sNewVal contains the substitution value. Otherwise, return empty string
    return $sNewVal;
  } // end of SetComponentHeaderInStream

  public function GetComponentHeaderInStream($sKey)
  {
    $sVal = '';

    if ($this->GetStream() != '') {
      $bFound = false;
      $n = sizeof($this->aBlockHeaderLength);
      $i = 0;
      $iFound = 0;
      $iStartIdx = 0;
      while ((!$bFound) && ($i < $n)) {
        if ($this->aBlockHeaderAssocName[$i] == $sKey)
          $bFound = true;
        else {
          $iStartIdx += $this->aBlockHeaderLength[$i];
          $i++;
        }
      }
      if ($bFound) {
        $sVal = substr($this->sStream, $iStartIdx, $this->aBlockHeaderLength[$i]);
      }
    }

    // if OK, $sVal contains the value. Otherwise, return empty string
    return $sVal;
  } // end of GetComponentHeaderInStream

  // sizeof($this->aBlockSingleLength) == sizeof($this->aBlockSingleAssocName)
  // $this->sStream != '';
  // $sVal = scalar & string, strlen($sVal) must be equal to block size represented by the $sKey
  public function SetComponentSingleInStream($sKey, $sVal)
  {
    $sNewVal = '';

    //echo "[$sKey] [$sVal]\n";
    if ($this->GetStream() != '') {
      $bFound = false;
      $n = sizeof($this->aBlockSingleLength);
      $i = 0;
      $iFound = 0;
      $iStartIdx = array_sum($this->aBlockHeaderLength); // Single block start after Header block
      while ((!$bFound) && ($i < $n)) {
        //echo ">> [".$this->aBlockSingleAssocName[$i]."]\n";
        if ($this->aBlockSingleAssocName[$i] == $sKey)
          $bFound = true;
        else {
          $iStartIdx += $this->aBlockSingleLength[$i];
          $i++;
        }
      }
      if ($bFound) {
        // replace old value with the new one
        $s = substr($this->sStream, 0, $iStartIdx);
        $sFormat = sprintf("%%0%ds", $this->aBlockSingleLength[$i]);
        $sNewVal = sprintf($sFormat, substr($sVal, 0, $this->aBlockSingleLength[$i])); // ensure that the new value has allowed number of digits/characters
        //echo "sID [$sID]\n";
        $s .= $sNewVal . substr($this->sStream, $iStartIdx + $this->aBlockSingleLength[$i]);
        // set $this->sStream with new stream
        $this->SetStream($s);
      } else {
        //echo "not found\n";
      }
    }

    // if OK, $sNewVal contains the substitution value. Otherwise, return empty string
    return $sNewVal;
  } // end of SetComponentSingleInStream

  public function GetComponentSingleInStream($sKey)
  {
    $sVal = '';

    if ($this->GetStream() != '') {
      $bFound = false;
      $n = sizeof($this->aBlockSingleLength);
      $i = 0;
      $iFound = 0;
      $iStartIdx = array_sum($this->aBlockHeaderLength); // Single block start after Header block
      while ((!$bFound) && ($i < $n)) {
        if ($this->aBlockSingleAssocName[$i] == $sKey)
          $bFound = true;
        else {
          $iStartIdx += $this->aBlockSingleLength[$i];
          $i++;
        }
      }
      if ($bFound) {
        $sVal = substr($this->sStream, $iStartIdx, $this->aBlockSingleLength[$i]);
      }
    }

    // if OK, $sVal contains the value. Otherwise, return empty string
    return $sVal;
  } // end of GetComponentSingleInStream

  public function Extract()
  {
    if ($this->GetStream() != '') {
      $Parser = new ParserGeneric($this->GetStream(), $this->GetBlockHeaderLengthArray(), $this->GetBlockSingleLengthArray(), $this->GetBlockRepeatLengthArray(), $this->GetBlockTrackingLengthArray(), $this->GetBlockHeaderAssocNameArray(), $this->GetBlockSingleAssocNameArray(), $this->GetBlockRepeatAssocNameArray(), $this->GetBlockTrackingAssocNameArray(), $this->GetRepeatKey());
      $Parser->Parse();
      $this->SetComponentHeaderArray($Parser->GetParsedHeaderArray());
      $this->SetComponentSingleArray($Parser->GetParsedSingleArray());
      $this->SetComponentRepeatArray($Parser->GetParsedRepeatArray());
      $this->SetComponentTrackingArray($Parser->GetParsedTrackingArray());
    }
  } // end of Extract

  public function _NormalizeComponentArray($aComponent, &$aComponentNormal)
  {
    if (is_array($aComponent)) {
      foreach ($aComponent as $Key => $Val) {
        if (is_array($Val)) {
          $aComponentNormal[$Key] = array();
          $n = sizeof($Val);
          for ($i = 0; $i < $n; $i++) {
            //$this->aComponent[$Key][$i] = ltrim(ltrim($Val[$i]), "0");
            $aComponentNormal[$Key][$i] = $Val[$i];
          }
        } else {
          $aComponentNormal[$Key] = $Val;
        }
      }
    }
  } // end of _NormalizeComponentArray

  public function Normalize()
  {
    $this->_NormalizeComponentArray($this->aComponentHeader, $this->aComponentHeaderNormal);
    $this->_NormalizeComponentArray($this->aComponentSingle, $this->aComponentSingleNormal);
    $this->_NormalizeComponentArray($this->aComponentRepeat, $this->aComponentRepeatNormal);
    $this->_NormalizeComponentArray($this->aComponentTracking, $this->aComponentTrackingNormal);
  } // end of NormalizeComponentArray

  public function GetNormalComponentHeaderArray()
  {
    return $this->aComponentHeaderNormal;
  } // end of GetNormalComponentArray

  public function GetNormalComponentSingleArray()
  {
    return $this->aComponentSingleNormal;
  } // end of GetNormalComponentArray

  public function GetNormalComponentRepeatArray()
  {
    return $this->aComponentRepeatNormal;
  } // end of GetNormalComponentArray

  public function GetNormalComponentTrackingArray()
  {
    return $this->aComponentTrackingNormal;
  } // end of GetNormalComponentArray
} // end of ProtocolGeneric


// echo "*** [DRIVER] ProtocolInquiryRequest ***\n";
// $sBlockReq = '40233038100200000001000011234567890123499101';
// $oReq = new ProtocolGeneric();
// $oReq->SetStream($sBlockReq);
// $oReq->SetBlockHeaderLengthArray(array(2, 6, 4, 7, 6, 14, 2));
// $oReq->SetBlockHeaderAssocNameArray(array('type', 'time', 'date', 'partner_id', 'partner_ref_id', 'pp_id', 'pn'));
// $oReq->Extract();
//$oReq->SetBlockRepeatAssocNameArray(array('type', 'time', 'date', 'partner_id', 'partner_ref_id', 'pp_id', 'pn'));
//$oReq->SetComponentHeaderInStream("time", strftime("%H%M%S", $iTS));
//$oReq->SetComponentHeaderInStream("date", strftime("%m%d", $iTS));
// $oReq->SetComponentHeaderInStream("partner_id", '5555555');
// $sNewBlockReq = $oReq->GetStream();
// echo "old [$sBlockReq]\n";
// echo "new [$sNewBlockReq]\n";
// echo "*** end of [DRIVER] ProtocolInquiryRequest ***\n";
