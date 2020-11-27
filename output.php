<?php

class rand_output {

    public function __construct() {
	$this->ocnt = 0;
	$this->doPArgs();
    }

    public function out($c) {
	if ($this->stdout) {
	    echo($c);
	    if (++$this->ocnt) file_put_contents('/tmp/michwr_log.txt', $this->ocnt . " byte written\n", FILE_APPEND);
	    
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
    
    private static function screenout($c) {
	static $cc   = 0;
	static $colc = 0;
	static $cs = '';
	static $cos = '';

	$cs .= $c;
	$cc++;
	$colc = $cc >> 1;
	$cos .= sprintf('%02x ', ord($c));

	if ($colc > 12) {
	    echo($cos . "\n");
	    $colc = 0;
	    $cs = '';
	    $cos = '';
	    $cc = 0;
	}
    }
    
}