<?php

function trandMicScreenOut($c) {
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
