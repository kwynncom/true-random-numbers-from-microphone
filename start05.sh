#! /bin/bash

RNGDPIDF=/tmp/michwr_rngd.pid

php ./index.php -raw | sudo rngd -p $RNGDPIDF -r /dev/stdin
