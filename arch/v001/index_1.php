<?php

require_once('/opt/kwynn/kwutils.php');

$fin = file_get_contents('/tmp/rd/in1146.wav');
echo number_format(strlen($fin)) . "\n";
$fo = '';
$i = 50044;

$colc = 0;

while(isset($fin[$i+100])) {

    $c = $fin[$i + 1];
    echo sprintf('%02x ', ord($c));
    $colc++;
    $fo .= $c;
    if ($colc > 52) {
	echo "\n";
	$colc = 0;
    }
    $i += 8;
}

file_put_contents('/tmp/rd/out1146.wav', $fo);
