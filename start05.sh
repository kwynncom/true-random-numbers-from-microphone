#! /bin/bash

php ./index.php -raw | sudo rngd -r /dev/stdin
