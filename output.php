<?php

class rand_output {

    const outlog = '/tmp/michwr_log.txt';
    
    public function __construct() {
	// $this->clearlog();
	$this->doPArgs();
    }

    private function clearlog() { file_put_contents(self::outlog, ''); }
    
    public function out($c) {
	if ($this->stdout) {
	    echo($c);
	    $this->logout($c);
	}
	if ($this->odx) self::screenout($c);
    }
    
    public function doPArgs() {
	global $argc;
	global $argv;

	$this->stdout = false;
	$this->odx = true;
	
	if ($argc < 2) return;

	foreach($argv as $a) {
	    if ($a === '-raw')	{
		$this->stdout = true;
		$this->odx    = false;
	    }
	    if ($a === '-x')	$this->odx = true;
	}
    }
    
    private function logout($c) {
	static $cc = 0;
	static $oat = false;
	if (!$oat) $oat = time();
	$cc++;
	
	if ($cc < 100) self::screenout($c, self::outlog);
	else if ($cc === 100) file_put_contents(self::outlog, "first $cc random bytes written, moving to literal ... output\n", FILE_APPEND);
	else if ($cc < 1000 && $cc % 50 === 0) file_put_contents(self::outlog, '.', FILE_APPEND);
	else if ($cc % 10000 === 0) file_put_contents(self::outlog, '.', FILE_APPEND);
	
	
	
    }
    
    private static function screenout($c, $to = false) {
	static $cc   = 0;
	static $colc = 0;
	static $cs = '';
	static $cos = '';

	$cs .= $c;
	$cc++;
	$colc = $cc >> 1;
	$cos .= sprintf('%02x ', ord($c));

	if ($colc > 12) {
	    $s = $cos . "\n";
	    if (!$to) echo($s);
	    else file_put_contents($to, $s, FILE_APPEND);
	    $colc = 0;
	    $cs = '';
	    $cos = '';
	    $cc = 0;
	}
    }
    
}