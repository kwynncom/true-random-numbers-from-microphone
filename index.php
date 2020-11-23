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
    const maxReadBuf    = self::discardFirstBytes + self::wavHeaderLen + (1 << 20);
    const fifo = '/tmp/ns_kwynn_com_2020_11_1_hwrand';
    
    private function __construct() {
	$this->init10();
	$this->init20();    
	$this->doit10();
    }
    
    private function init10() {
	if (!file_exists(self::fifo)) posix_mkfifo(self::fifo, 0644);
	$this->outh = fopen(self::fifo, 'w');
	$this->inh  = popen(self::baseCmd, 'rb');
    }
    
    private function __destruct() {
	pclose($this->inh);
	fclose($this->outh);
	echo('destructor ran' . "\n");
    }
    
    private function init20() {
	$mm = self::magicModV10;
	$ab = self::alignByte;
	$frl = self::discardFirstBytes + self::wavHeaderLen;
	$m   = $frl % $mm;
	if ($m !== $ab) $frl += $mm + $ab - $m;
	kwas($frl % $mm === $ab, 'first read length does not align');
	$this->randptr = $frl;
	$this->doit10();
    }
    
    private function doit10() {

	$discarding = true;
	$initBuf = '';
	$this->tdlen = 0;
	
	while ($batchin = fread($this->inh, self::maxReadBuf)) {
	    
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

	    $this->doit20();
	}
    }
    
    private function doit20() {
	
	while (isset($this->obbuf[$this->randptr - $this->oboffset])) {
	    $c =     $this->obbuf[$this->randptr - $this->oboffset];
	    fwrite($this->outh, $c);
	    $this->randptr += self::magicModV10;
	}
    }
    
    public static function doit() { new self(); }
}

rand_mic::doit();


