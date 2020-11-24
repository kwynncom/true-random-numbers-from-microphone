<?php

class rand_mic {
    const baseCmd = 'arecord -f S32_LE -c 2 -r 48000 --device="hw:0,0" ';
    const byteInterval = 4;
    const alignByte = self::byteInterval - 3; // the general calculation is more complicated, but I make it a separate const as a start towards general
    const discardFirstBytes = 51000;
    const wavHeaderLen = 44;
    const maxReadBuf    = self::discardFirstBytes + self::wavHeaderLen + (1 << 19);
    
    private function __construct($ocb) {
	$this->ocb = $ocb;
	$this->doPArgs();
	$this->initInput();
	$this->readLoop();
    }
    
    private function initInput() {
	$cmd = self::baseCmd;
	if ($this->duration) $cmd .= ' -d ' . $this->duration;
	$this->inh  = popen($cmd, 'rb');
    }
    
    public function __destruct() {
	pclose($this->inh);
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
	    call_user_func($this->ocb, $c);
	    $this->randptr += self::byteInterval;
	}
    }
    
    public function doPArgs() {
	global $argc;
	global $argv;

	$this->duration = false;
	
	if ($argc < 2) return;

	$key = '-d';
	$dattempt = false;
	foreach($argv as $a) {
	    if (substr(trim($a), 0, strlen($key)) === $key) $dattempt = true;
	    if (preg_match('/-d=?(\d+)/', $a, $m)) { $this->duration = $m[1]; }
	}
	
	kwas(!$dattempt || $this->duration, 'duration switch requires a positive integer with no space or an = such as -d=1 or -d1' . "\n");
	
    }
    
    public static function doit($ocb) { new self($ocb); }
}
