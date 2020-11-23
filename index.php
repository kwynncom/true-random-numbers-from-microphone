<?php

require_once('/opt/kwynn/kwutils.php');
require_once('input.php');
require_once('output.php');

$oo = new rand_output();
rand_mic::doit([$oo, 'out']);
