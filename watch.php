<?php

require_once('log.php');

class rand_watch {

    public function __construct() {
	$this->p10();
    }
    
    function p10() {
	static $c = 0;
	
	
    
	do {
	    if ($c < 300) {
		echo("entropy: " . rand_log::getEntropy() . "\n");
	    } else {
		echo('watch exiting');
		break;
	    }
		
		
	    sleep(1);
	    $c++;
	} while (1);
    }
   

}

new rand_watch();