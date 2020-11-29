#! /bin/bash
sudo echo "sudo authorized for this shell"
nohup ./start_inner.sh > /dev/null 2>&1 &
