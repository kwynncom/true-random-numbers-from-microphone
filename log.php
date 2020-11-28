<?php

class rand_log {
    
    const logFile = '/tmp/michwr_log.txt';
    
    public static function out($c) {
	static $cc = 0;
	
	// if ($cc === 0) self::test10();
	// if ($cc === 0) self::doPArgs();
	// if ($cc === 0) self::outFinal('initial entropy: ' . self::getEntropy() . "\n");
	
	$cc++;
	
	if ($cc < 100) rand_output::screenout($c, self::logFile);
	else if ($cc === 100) {
	    self::outFinal("first $cc random bytes written, moving to literal ... output\n");
	   // self::outFinal('entropy: ' . self::getEntropy() . "\n");
	}
	else if (($cc < 1000 && $cc % 50 === 0) || $cc % 10000 === 0) self::outFinal('.');
    }
    
    public static function outFinal($s) {
	
	static $first = true;
	
	if ($first) {
	    file_put_contents(self::logFile, '');
	    $first = false;
	}
	
	if (0) echo $s;
	else file_put_contents(self::logFile, $s, FILE_APPEND);
    }
    
    public static function getEntropy() {
	return intval(trim(shell_exec('cat /proc/sys/kernel/random/entropy_avail')));
    }
    
    private static function doPArgs() {
	global $argc;
	global $argv;
	
	if ($argc < 2) return;

	foreach($argv as $a) {
	    // if ($a === '-test') self::test10();
	}
    }
    
    public static function test10() {
	
	static $len = 5000;
	static $file = '/dev/random';
	
	$h = fopen($file, 'r');
	$b = microtime(1);
	$r = fread($h, $len);
	$e = microtime(1);
	fclose($h);
	if (strlen($r) !== $len) return;
	
	$d = $e - $b;
	$ms = $d * 1000;
	$s = sprintf('%0.2f', $ms);
	
	$ls = number_format($len);
	$os = "OK / SUCCESS: $ls bytes read from $file in " . $s . ' milliseconds' . "\n";
	echo($os);
	
	
    }

}