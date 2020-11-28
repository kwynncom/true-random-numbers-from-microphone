#! /bin/bash

nohup ./start05.sh > /tmp/log 2>&1 &
php test.php
