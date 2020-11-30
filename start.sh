#! /bin/bash
sudo echo "sudo authorized for this shell"
sudo rm -f /tmp/michwr_input.pid
echo "starting..."
nohup ./start_inner.sh > /dev/null 2>&1 &
echo "testing..."
php ./test.php
