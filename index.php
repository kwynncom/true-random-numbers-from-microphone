<?php

$kuf = '/opt/kwynn/kwutils.php';

if (file_exists($kuf)) require_once($kuf);

$cmd = 'arecord -f S32_LE -c 2 -d 1 -r 48000 --device="hw:0,0" ';
$r = popen($cmd, 'rb'); unset($cmd);
$allin = '';
$wavhsize = 44;
$ii = 50000 + $wavhsize;
$colc = 0;
$dout = '';

$procing = false;
while ($dat = fread($r, 20000000)) {
    $allin .= $dat;
    if (isset($allin[$ii + 8])) break;
}

while ($dat = fread($r, 20000000)) {
    $allin .= $dat;
    if (!isset($i)) for($j=0; $j < 8; $j++)  if (($ii + $j) % 8 === 5) { $i = $ii + $j; break; }
    
    $c = $allin[$i];
    echo sprintf('%02x ', ord($c));
    $dout .= $c;
    $colc++;
    if ($colc > 52) {
	echo "\n";
	$colc = 0;
    }
    
    $i += 8;
}

if (!isset($i)) for($j=0; $j < 8; $j++)  if (($ii + $j) % 8 === 5) { $i = $ii + $j; break; }

while (isset($allin[$i])) {
    
    $c = $allin[$i];
    echo sprintf('%02x ', ord($c));
    $dout .= $c;
    $colc++;
    if ($colc > 52) {
	echo "\n";
	$colc = 0;
    }
    
    $i += 8;
    
}

pclose($r); unset($r);


echo("\n" . number_format(strlen($allin)) . " bytes read\n");
fflush(STDOUT);

$cmd = 'rngtest';

$r = popen($cmd, 'wb');
fwrite($r, $dout);

// file_put_contents('/tmp/rd/out406.wav', $dout);
