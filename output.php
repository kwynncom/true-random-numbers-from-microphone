<?php

class rand_output {
    
    const fifo = '/tmp/ns_kwynn_com_2020_11_1_hwrand';

    public function __construct() {
	if (!file_exists(self::fifo)) posix_mkfifo(self::fifo, 0644);
	$this->outh = fopen(self::fifo, 'w');
    }
    
    public function __destruct() {
	fclose($this->outh);
	echo("\n" . 'output destructor ran' . "\n");
    }
    
    public function out($c) {
	fwrite($this->outh, $c);	
    }
    
}