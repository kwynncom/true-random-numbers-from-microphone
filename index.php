<?php

require_once('/opt/kwynn/kwutils.php');
require_once('screenOut.php');

class rand_mic {
    const baseCmd = 'arecord -f S32_LE -c 2 -r 48000 --device="hw:0,0" -d ';
    const magicModV10 = 4;
    const alignByte = self::magicModV10 - 3;
    const defaultDurationS = 1;
    const discardFirstBytes = 51000;
    const wavHeaderLen = 44;
    const maxInputBytes = PHP_INT_MAX;
    const maxOuputBytes = 1 << 30;
    
    private function __construct() { 
	$this->p10();    
	$this->p40();
    }
    
    private function getDuration() {
	if (ispkwd()) return 20;
	return self::defaultDurationS;
    }

    private function p10() {
	$mm = self::magicModV10;
	$ab = self::alignByte;
	$frl = self::discardFirstBytes + self::wavHeaderLen;
	$m   = $frl % $mm;
	if ($m !== $ab) $frl += $mm + $ab - $m;
	kwas($frl % $mm === $ab, 'first read length does not align');
	$this->p20($frl);
    }
    
    private function p20($firstPtr) {
	
	$cmd = self::baseCmd . self::getDuration();
	$resource10 = popen($cmd, 'rb');
	
	$discarding = true;
	$this->allin = '';
	
	while ($dat = fread($resource10, self::maxOuputBytes)) {
	    $this->allin .= $dat;
	    if   ($discarding && !isset($this->allin[$firstPtr])) continue;
	    else $discarding = false;
	    $this->p30($firstPtr);
	}
	
	pclose($resource10);
    }
    
    private function p30($firstPtr) {
	static $ptr = false;
	if (!$ptr) {
	    $ptr = $firstPtr;
	    $this->allout = '';
	}
	
	while (isset($this->allin[$ptr])) {
	    $c = $this->allin[$ptr];
	    $this->allout .= $c;
	    $ptr += self::magicModV10;
	    trandMicScreenOut($c);
	}
    }
    
    public static function doit() { new self(); }
    
    function p40() {
	echo("\n" . number_format(strlen($this->allin)) . " bytes read\n");
	fflush(STDOUT);
	$cmd = 'rngtest';
	$r = popen($cmd, 'wb');
	fwrite($r, $this->allout);
	// file_put_contents('/tmp/rd/out406.wav', $dout);
    }
}

rand_mic::doit();


