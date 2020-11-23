<?php

require_once('/opt/kwynn/kwutils.php');

class rand_mic {
    const baseCmd = 'arecord -f S32_LE -c 2 -r 48000 --device="hw:0,0" ';
    const byteInterval = 4;
    const alignByte = self::byteInterval - 3; // the general calculation is more complicated, but I make it a separate const as a start towards general
    const discardFirstBytes = 51000;
    const wavHeaderLen = 44;
    const maxReadBuf    = self::discardFirstBytes + self::wavHeaderLen + (1 << 19);
    const fifo = '/tmp/ns_kwynn_com_2020_11_1_hwrand';
    
    private function __construct() {
	$this->initIO();
	$this->readLoop();
    }
    
    private function initIO() {
	if (!file_exists(self::fifo)) posix_mkfifo(self::fifo, 0644);
	$this->outh = fopen(self::fifo, 'w');
	$this->inh  = popen(self::baseCmd, 'rb');
    }
    
    private function __destruct() {
	pclose($this->inh);
	fclose($this->outh);
	echo('destructor ran' . "\n");
    }
    
    private function calcInitPtr() {
	$bi = self::byteInterval;
	$ab = self::alignByte;
	$ptr = self::discardFirstBytes + self::wavHeaderLen;
	$m   = $ptr % $bi;
	if ($m !== $ab) $ptr += $bi + $ab - $m;
	kwas($ptr % $bi === $ab, 'first read length does not align');
	return $ptr;
    }
    
    private function discardThenInit($batchin) {
	static $initBuf = '';

	$initBuf .= $batchin; unset($batchin);
	if   (!isset($initBuf[$this->randptr])) return true;

	$this->obbuf = $initBuf; unset($initBuf);
	$this->oboffset = 0;
	return false;
    }
    
    private function readLoop() {

	$this->tdlen = 0;
	$this->randptr = self::calcInitPtr();
	$discarding = true;
	
	while ($batchin = fread($this->inh, self::maxReadBuf)) {
	    
	    $batchlen = strlen($batchin);
	    $this->tdlen += $batchlen;
	    
	    if  ($discarding && $this->discardThenInit($batchin)) continue;
	    else $discarding = false; 
	
	    $this->oboffset += strlen($this->obbuf);
	    $this->obbuf = $batchin; unset($batchin);
	    $this->writeLoop();
	}
    }
    
    private function writeLoop() {
	
	while (isset($this->obbuf[$this->randptr - $this->oboffset])) {
	    $c =     $this->obbuf[$this->randptr - $this->oboffset];
	    fwrite($this->outh, $c);
	    $this->randptr += self::byteInterval;
	}
    }
    
    public static function doit() { new self(); }
}

rand_mic::doit();


