<?php

require_once('log.php');

class rand_output {
    
    public function __construct() {
	$this->doPArgs();
    }
    
    public function out($c) {
	if ($this->stdout) {
	    echo($c);
	    rand_log::out($c);
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
    
    public static function screenout($c, $to = false) {
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
	    else rand_log::outFinal($s);
	    $colc = 0;
	    $cs = '';
	    $cos = '';
	    $cc = 0;
	}
    }
    
}