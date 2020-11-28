#! /bin/bash

RNGDPIDF=/tmp/michwr_rngd.pid
INPID=/tmp/michwr_input.pid

pkill -F $INPID
sudo pkill --signal SIGKILL -F $RNGDPIDF
echo "if all you see is this, stop probably worked"
