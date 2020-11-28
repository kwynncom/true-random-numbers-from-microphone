#! /bin/bash

(/usr/bin/nohup /usr/bin/php ./index.php -raw | sudo /usr/sbin/rngd -r /dev/stdin) &
