<?php

namespace App\Services\ISO8583;

class ParserGeneric
{
    // input
    private $sStream                 = '';
    private $aBlockHeaderLength      = null;
    private $aBlockSingleLength      = null;
    private $aBlockRepeatLength      = null;
    private $aBlockTrackingLength    = null;
    private $aBlockHeaderAssocName   = null;
    private $aBlockSingleAssocName   = null;
    private $aBlockRepeatAssocName   = null;
    private $aBlockTrackingAssocName = null;
    private $sRepeatKey              = '';
    // output
    private $aBlockHeaderParsed   = null;
    private $aBlockSingleParsed   = null;
    private $aBlockRepeatParsed   = null;
    private $aBlockTrackingParsed = null;

    function __construct($sStream, $aBlockHeaderLength, $aBlockSingleLength,
                         $aBlockRepeatLength, $aBlockTrackingLength,
                         $aBlockHeaderAssocName, $aBlockSingleAssocName,
                         $aBlockRepeatAssocName, $aBlockTrackingAssocName,
                         $sRepeatKey)
    {
        $this->AssignVars($sStream, $aBlockHeaderLength, $aBlockSingleLength,
            $aBlockRepeatLength, $aBlockTrackingLength, $aBlockHeaderAssocName,
            $aBlockSingleAssocName, $aBlockRepeatAssocName,
            $aBlockTrackingAssocName, $sRepeatKey);
    }
// end of __construct

    public function InitVars()
    {
        $this->sStream = '';
        $n             = sizeof($this->aBlockHeaderLength);
        for ($i = 0; $i < $n; $i++) {
            array_shift($this->aBlockHeaderLength);
        }
        $this->aBlockHeaderLength = null;
        $n                        = sizeof($this->aBlockSingleLength);
        for ($i = 0; $i < $n; $i++) {
            array_shift($this->aBlockSingleLength);
        }
        $this->aBlockSingleLength = null;
        $n                        = sizeof($this->aBlockRepeatLength);
        for ($i = 0; $i < $n; $i++) {
            array_shift($this->aBlockRepeatLength);
        }
        $this->aBlockRepeatLength = null;
        $n                        = sizeof($this->aBlockHeaderAssocName);
        for ($i = 0; $i < $n; $i++) {
            array_shift($this->aBlockHeaderAssocName);
        }
        $this->aBlockHeaderAssocName = null;
        $n                           = sizeof($this->aBlockSingleAssocName);
        for ($i = 0; $i < $n; $i++) {
            array_shift($this->aBlockSingleAssocName);
        }
        $this->aBlockSingleAssocName = null;
        $n                           = sizeof($this->aBlockRepeatAssocName);
        for ($i = 0; $i < $n; $i++) {
            array_shift($this->aBlockRepeatAssocName);
        }
        $this->aBlockRepeatAssocName = null;
        $n                           = sizeof($this->aBlockHeaderParsed);
        for ($i = 0; $i < $n; $i++) {
            array_shift($this->aBlockHeaderParsed);
        }
        $this->aBlockHeaderParsed = null;
        $n                        = sizeof($this->aBlockSingleParsed);
        for ($i = 0; $i < $n; $i++) {
            array_shift($this->aBlockSingleParsed);
        }
        $this->aBlockSingleParsed = null;
        $n                        = sizeof($this->aBlockRepeatParsed);
        for ($i = 0; $i < $n; $i++) {
            array_shift($this->aBlockRepeatParsed);
        }
        $this->aBlockRepeatParsed = null;
        $n                        = sizeof($this->aBlockTrackingParsed);
        for ($i = 0; $i < $n; $i++) {
            array_shift($this->aBlockTrackingParsed);
        }
        $this->aBlockTrackingParsed = null;
    }
// end of InitVars

    public function AssignVars($sStream, $aBlockHeaderLength,
                               $aBlockSingleLength, $aBlockRepeatLength,
                               $aBlockTrackingLength, $aBlockHeaderAssocName,
                               $aBlockSingleAssocName, $aBlockRepeatAssocName,
                               $aBlockTrackingAssocName, $sRepeatKey)
    {
        $this->InitVars();

        $this->sStream                 = $sStream;
        $this->aBlockHeaderLength      = $aBlockHeaderLength;
        $this->aBlockSingleLength      = $aBlockSingleLength;
        $this->aBlockRepeatLength      = $aBlockRepeatLength;
        $this->aBlockTrackingLength    = $aBlockTrackingLength;
        $this->aBlockHeaderAssocName   = $aBlockHeaderAssocName;
        $this->aBlockSingleAssocName   = $aBlockSingleAssocName;
        $this->aBlockRepeatAssocName   = $aBlockRepeatAssocName;
        $this->aBlockTrackingAssocName = $aBlockTrackingAssocName;
        $this->sRepeatKey              = $sRepeatKey;
    }
// end of AssignVars

    public function GetBlockHeader($sStreamSrc, &$nSum = 0,
                                   &$sStreamHeader = '', &$sStreamUnproc = '')
    {
        $nSum = 0;
        $n    = sizeof($this->aBlockHeaderLength);

        for ($i = 0; $i < $n; $i++) {
            $nSum += $this->aBlockHeaderLength[$i];
        }

        if ($nSum > 0) {
            $sStreamHeader = substr($sStreamSrc, 0, $nSum);
        }

        $sStreamUnproc = substr($sStreamSrc, $nSum);
    }
// end of GetBlockHeader

    public function GetBlockSingle($sStreamSrc, &$nSum = 0,
                                   &$sStreamSingle = '', &$sStreamUnproc = '')
    {
        $nSum = 0;
        $n    = sizeof($this->aBlockSingleLength);

        for ($i = 0; $i < $n; $i++) {
            $nSum += $this->aBlockSingleLength[$i];
        }

        if ($nSum > 0) {
            $sStreamSingle = substr($sStreamSrc, 0, $nSum);
        }

        $sStreamUnproc = substr($sStreamSrc, $nSum);
    }
// end of GetBlockSingle

    public function GetBlockRepeat($sStreamSrc, &$nSum = 0,
                                   &$sStreamRepeat = '', &$sStreamUnproc = '')
    {
        $nSum = 0;
        $n    = sizeof($this->aBlockRepeatLength);

        for ($i = 0; $i < $n; $i++) {
            $nSum += $this->aBlockRepeatLength[$i];
        }

        if ($nSum > 0) {
            $sStreamRepeat = substr($sStreamSrc, 0, $nSum);
        }

        $sStreamUnproc = substr($sStreamSrc, $nSum);
    }
// end of GetBlockRepeat

    public function GetBlockTracking($sStreamSrc, &$nSum = 0,
                                     &$sStreamTracking = '',
                                     &$sStreamUnproc = '')
    {
        $nSum = 0;
        $n    = sizeof($this->aBlockTrackingLength);

        for ($i = 0; $i < $n; $i++) {
            $nSum += $this->aBlockTrackingLength[$i];
        }

        if ($nSum > 0) {
            $sStreamTracking = substr($sStreamSrc, 0, $nSum);
        }

        $sStreamUnproc = substr($sStreamSrc, $nSum);
    }
// end of GetBlockTracking

    public function GetRepeatKey()
    {
        return $this->sRepeatKey;
    }
// end of GetRepeatKey

    public function ParseBlockHeader($sBlockHeader)
    {
        $sProcStream           = $sBlockHeader;
        $nBlockHeaderLength    = sizeof($this->aBlockHeaderLength);
        $nBlockHeaderAssocName = sizeof($this->aBlockHeaderAssocName);
        for ($i = 0; $i < $nBlockHeaderLength; $i++) {
            // get the substring
            $sVal                                                                                                     = substr($sProcStream,
                0, $this->aBlockHeaderLength[$i]);
            // save the substring
            $nParsed                                                                                                  = sizeof($this->aBlockHeaderParsed);
            $this->aBlockHeaderParsed[$nBlockHeaderAssocName > 0 ? $this->aBlockHeaderAssocName[$nParsed]
                    : $nParsed] = $sVal;
            // remove the processed substring
            $sProcStream                                                                                              = substr($sProcStream,
                $this->aBlockHeaderLength[$i]);
        }
    }
// end of ParseBlockHeader

    public function ParseBlockSingle($sBlockSingle)
    {
        $sProcStream           = $sBlockSingle;
        $nBlockSingleLength    = sizeof($this->aBlockSingleLength);
        $nBlockSingleAssocName = sizeof($this->aBlockSingleAssocName);
        for ($i = 0; $i < $nBlockSingleLength; $i++) {
            // get the substring
            $sVal                                                                                                     = substr($sProcStream,
                0, $this->aBlockSingleLength[$i]);
            // save the substring
            $nParsed                                                                                                  = sizeof($this->aBlockSingleParsed);
            $this->aBlockSingleParsed[$nBlockSingleAssocName > 0 ? $this->aBlockSingleAssocName[$nParsed]
                    : $nParsed] = $sVal;
            // remove the processed substring
            $sProcStream                                                                                              = substr($sProcStream,
                $this->aBlockSingleLength[$i]);
        }
    }
// end of ParseBlockSingle

    public function ParseBlockRepeat($sBlockRepeat)
    {
        $sProcStream           = $sBlockRepeat;
        //echo "sProcStream [$sProcStream]\n";
        $nBlockRepeatLength    = sizeof($this->aBlockRepeatLength);
        $nBlockRepeatAssocName = sizeof($this->aBlockRepeatAssocName);

        for ($i = 0; $i < $nBlockRepeatLength; $i++) {
            // get the substring
            $sVal                                                                 = substr($sProcStream,
                0, $this->aBlockRepeatLength[$i]);
            // save the substring
            $KeyIdx                                                               = ($nBlockRepeatAssocName
                > 0 ? $this->aBlockRepeatAssocName[$i] : $i);
            $nParsed                                                              = 0;
            if (isset($this->aBlockRepeatParsed[$KeyIdx]))
                    $nParsed                                                              = sizeof($this->aBlockRepeatParsed[$KeyIdx]);
            //echo ">> in loop [$i] [".$this->aBlockRepeatAssocName[$i]."] [$nParsed]\n";
            if ($nBlockRepeatAssocName > 0)
                    $this->aBlockRepeatParsed[$this->aBlockRepeatAssocName[$i]][$nParsed]
                    = $sVal;
            // remove the processed substring
            $sProcStream                                                          = substr($sProcStream,
                $this->aBlockRepeatLength[$i]);
        }
    }
// end of ParseBlockRepeat

    public function ParseBlockTracking($sBlockTracking)
    {
        $sProcStream             = $sBlockTracking;
        $nBlockTrackingLength    = sizeof($this->aBlockTrackingLength);
        $nBlockTrackingAssocName = sizeof($this->aBlockTrackingAssocName);
        for ($i = 0; $i < $nBlockTrackingLength; $i++) {
            // get the substring
            $sVal                                                                     = substr($sProcStream,
                0, $this->aBlockTrackingLength[$i]);
            // save the substring
            $KeyIdx                                                                   = ($nBlockTrackingAssocName
                > 0 ? $this->aBlockTrackingAssocName[$i] : $i);
            $nParsed                                                                  = 0;
            if (isset($this->aBlockTrackingParsed[$KeyIdx]))
                    $nParsed                                                                  = sizeof($this->aBlockTrackingParsed[$KeyIdx]);
            if ($nBlockTrackingAssocName > 0)
                    $this->aBlockTrackingParsed[$this->aBlockTrackingAssocName[$i]][$nParsed]
                    = $sVal;
            // remove the processed substring
            $sProcStream                                                              = substr($sProcStream,
                $this->aBlockTrackingLength[$i]);
        }
    }
// end of ParseBlockTracking

    public function Parse()
    {

        $sStreamUnproc = $this->sStream;
        $nSum          = 0;
        //echo "stream [$sStreamUnproc]\n";
        $this->GetBlockHeader($sStreamUnproc, $nSumHeaderLength, $sStreamHeader,
            $sStreamUnproc);
        $this->ParseBlockHeader($sStreamHeader);
        //echo "Header [$sStreamHeader]->".(strlen($sStreamHeader))." [$sStreamUnproc]->".(strlen($sStreamUnproc))."\n";
        $this->GetBlockSingle($sStreamUnproc, $nSumSingleLength, $sStreamSingle,
            $sStreamUnproc);
        $this->ParseBlockSingle($sStreamSingle);
        //echo "Single [$sStreamSingle]->".(strlen($sStreamSingle))." [$sStreamUnproc]->".(strlen($sStreamUnproc))."\n";
        $aArr          = $this->GetParsedSingleArray();
        $nRepeat       = 0;
        if (is_array($aArr)) {
            if (is_array($this->aBlockSingleParsed)) {
                if (($this->GetRepeatKey()) && ($this->GetRepeatKey() != '')) {
                    $nRepeat = intval($this->aBlockSingleParsed[$this->GetRepeatKey()]);
                    //$nRepeat = intval($this->nRepeat);
                    //if (($nRepeat < 1) || ($nRepeat > 4)) $nRepeat = 0; // valid values must be {1..4}
                }
            }
        }
        $sBlockRepeatAllUnproc = $sStreamUnproc;
        //while ($sBlockRepeatAllUnproc != '')
        //echo "nRepeat->".$nRepeat;
        for ($i = 0; $i < $nRepeat; $i++) {
            $sStreamRepeat = '';
            $this->GetBlockRepeat($sBlockRepeatAllUnproc, $nSumRepeatLength,
                $sStreamRepeat, $sBlockRepeatAllUnproc);
            if ($sStreamRepeat != '') $this->ParseBlockRepeat($sStreamRepeat);
            // echo "Repeat [$sStreamRepeat]->".(strlen($sStreamRepeat))." [$sStreamUnproc]->".(strlen($sStreamUnproc))."\n";
        }
        $sStreamUnproc = $sBlockRepeatAllUnproc;
        $this->GetBlockTracking($sStreamUnproc, $nSumTrackingLength,
            $sStreamTracking, $sStreamUnproc);
        $this->ParseBlockTracking($sStreamTracking);
        //echo "Tracking [$sStreamTracking]->".(strlen($sStreamTracking))." [$sStreamUnproc]->".(strlen($sStreamUnproc))."\n";
    }
// end of Parse

    public function GetParsedHeaderArray()
    {
        return $this->aBlockHeaderParsed;
    }
// end of GetParsedHeaderArray

    public function GetParsedSingleArray()
    {
        return $this->aBlockSingleParsed;
    }
// end of GetParsedSingleArray

    public function GetParsedRepeatArray()
    {
        return $this->aBlockRepeatParsed;
    }
// end of GetParsedRepeatArray

    public function GetParsedTrackingArray()
    {
        return $this->aBlockTrackingParsed;
    }
// end of GetParsedTrackingArray
}
// end of ParserGeneric
