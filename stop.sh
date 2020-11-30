#! /bin/bash

RNGDPIDF=/tmp/michwr_rngd.pid
INPID=/tmp/michwr_input.pid

sudo echo "sudo authorized for this shell"

pkill -F $INPID
sudo pkill --signal SIGKILL -F $RNGDPIDF
sudo rm -f $INPID
sudo rm -f $RNGDPIDF
echo "if all you see is this and the sudo authorized message, stop probably worked"
