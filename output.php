<?php

class rand_output {

    public function __construct() {
	$this->doPArgs();
    }

    public function out($c) {
	if ($this->stdout) echo $c;
	if ($this->odx) self::screenout($c);
    }
    
    public function doPArgs() {
	global $argc;
	global $argv;

	$this->stdout = false;
	$this->odx = false;
	
	if ($argc < 2) return;

	foreach($argv as $a) {
	    if ($a === '-raw')	$this->stdout = true;
	    if ($a === '-x')	$this->odx = true;
	}
    }
    
    private static function screenout($c) {
	static $cols = 52;
	static $cc   = 0;
	static $colc = 0;
	static $cs = '';
	static $cos = '';

	$cs .= $c;
	$cc++;
	$colc = $cc >> 1;
	$cos .= sprintf('%02x ', ord($c));

	if ($colc > 27) {
	    echo($cos . "\n");
	    $colc = 0;
	    $cs = '';
	    $cos = '';
	    $cc = 0;
	}
    }
    
}