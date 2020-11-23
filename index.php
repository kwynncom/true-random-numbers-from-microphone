<?php

require_once('/opt/kwynn/kwutils.php');

class rand_mic {
    const baseCmd = 'arecord -f S32_LE -c 2 -r 48000 --device="hw:0,0" ';
    const magicModV10 = 4;
    const alignByte = self::magicModV10 - 3;
    const defaultDurationS = 1;
    const discardFirstBytes = 51000;
    const wavHeaderLen = 44;
    const maxOuputBytes = PHP_INT_MAX;
    const maxReadBuf    = self::discardFirstBytes + self::wavHeaderLen + (1 << 18);
    const fifo = '/tmp/ns_kwynn_com_2020_11_1_hwrand';
    
    private function __construct() {
	
	$this->p05();
	$this->p10();    
    }
    
    private function p05() {
	if (!file_exists(self::fifo)) posix_mkfifo(self::fifo, 0644);
	$this->outh = fopen(self::fifo, 'w');
    }
    
    private function p10() {
	$mm = self::magicModV10;
	$ab = self::alignByte;
	$frl = self::discardFirstBytes + self::wavHeaderLen;
	$m   = $frl % $mm;
	if ($m !== $ab) $frl += $mm + $ab - $m;
	kwas($frl % $mm === $ab, 'first read length does not align');
	$this->randptr = $frl;
	$this->p20();
    }
    
    private function p20() {
	
	$cmd = self::baseCmd;
	$resource10 = popen($cmd, 'rb');
	
	$discarding = true;
	$initBuf = '';
	$this->tdlen = 0;
	
	while ($batchin = fread($resource10, self::maxReadBuf)) {
	    
	    $batchlen = strlen($batchin);
	    
	    $this->tdlen += $batchlen;
	    
	    if ($discarding) {
		$initBuf .= $batchin; unset($batchin);
		$c1 = !isset($initBuf[$this->randptr]);
		if   ($discarding && $c1) continue;
		else {
		    unset($c1);
		    $this->obbuf = $initBuf; unset($initBuf);
		    $this->oboffset = 0;
		    $discarding = false;
		}
	    } else {
		$this->oboffset += strlen($this->obbuf);
		$this->obbuf = $batchin; unset($batchin);
	    }

	    $this->p30();
	}
	
	pclose($resource10);
    }
    
    private function p30() {
	
	while (isset($this->obbuf[$this->randptr - $this->oboffset])) {
	    $c =     $this->obbuf[$this->randptr - $this->oboffset];
	    $this->randptr += self::magicModV10;
	    fwrite($this->outh, $c);
	}
    }
    
    public static function doit() { new self(); }
}

rand_mic::doit();


