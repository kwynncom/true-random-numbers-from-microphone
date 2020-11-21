<?php

require_once('/opt/kwynn/kwutils.php');

$fin = file_get_contents('/tmp/test.wav');
echo number_format(strlen($fin)) . "\n";
$fo = '';
$i = 1000000 + 44;

while(isset($fin[$i+100])) {

      // $c = $fin[$i + 1]; // relatively close to random, but some long runs
     // $c = $fin[$i + 3]; // seems to be high order byte - loudest, based on listening, or medium?
//      $c = $fin[$i + 4]; // all 0s
    // $c = $fin[$i + 5]; // long runs, perhaps same as +1
  // $c = $fin[$i + 0];  // all zeros
    // $c = $fin[$i + 6];  // runs
    echo sprintf('%02x ', ord($c));
    $fo .= $c;
    if ($i % 10 === 0) echo "\n";
    $i += 8;
    if ($i > 2000000) break;
}

file_put_contents('/tmp/rd/ordip1.wav', $fo);

// one byte seems silent, 2 bytes has harmonic of 60 Hz or something loud
//     $t1   = (0xff & ord($fin[$i + 3])); //  + (0xf & ord($fin[$i+4])); - definite tone
// play and record have to have the same settings