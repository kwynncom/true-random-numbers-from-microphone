<?php

require_once('/opt/kwynn/kwutils.php');

class rand_test {

    const inputFile = '/dev/random';
    
    // UPDATE: 2021/05 - It appears that the Linux kernel 5.8 /dev/random no longer blocks, so /dev/random is no longer a test of random input from my 
    // script or any other.  See new test.sh
    
    private function __construct() {
	$this->totn = 0;
	$this->inh = fopen(self::inputFile, 'r');
    }
    
    public function __destruct() {
	fclose($this->inh);
    }

    private function read($getn = 1) {

	kwas(is_integer($getn) && $getn > 0, 'bad number of bytes = ' . $getn);
	$b = microtime(1);
	$r = fread($this->inh, $getn);
	$e = microtime(1);
	kwas(strlen($r) === $getn, 'byte read failure - did not get n bytes = ' . $getn);
	
	$this->totn += $getn;

	$s = $e - $b;
	$ms = $s * 1000;
	$mss = sprintf('%0.3f', $ms);
	
	$ls = number_format($getn);
	$os = "$ls bytes read in $mss milliseconds\n";
	echo($os);
    }
    
    public static function getEntropy() {
	return intval(trim(shell_exec('cat /proc/sys/kernel/random/entropy_avail')));
    }
    
    private function test10() {
	
	self::echoEntropy();
	echo("Reading from " . self::inputFile . "  ...\n");
	
	$a = [1, 100, 500, 1000, 2000, 5000];
	foreach($a as $n) $this->read($n);
	echo(number_format($this->totn) . ' total bytes read' . "\n");
	self::echoEntropy();
	usleep(300000);
	self::echoEntropy();	
    }
    
    public static function echoEntropy() {
	echo(number_format(self::getEntropy())  . ' bits of entropy available' . "\n");	
    }
    
    public static function doit() {
	$o = new rand_test();
	$o->test10();
    }
}

rand_test::doit();
