#! /bin/bash

FIFO=/tmp/michwr_fifo_2002
INPID=/tmp/michwr_input.pid
RNGDPID=/tmp/michwr_rngd.pid
OUTLOG=/tmp/michwr_log.txt

sudo echo "sudo is set"

if [ "$1" != "stop" ]
then
    echo "" > $OUTLOG

    if [ ! -p $FIFO ]
    then
            mkfifo $FIFO
    fi
    php ./index.php -raw -fifo=$FIFO "$@" &

#  </dev/null &>/dev/null &
    echo $! > $INPID
    sudo /usr/sbin/rngd -p $RNGDPID -r $FIFO
    disown -a
# echo "testing /dev/random"
# /usr/bin/php ./test.php
#    echo "starting"
# tail -F $OUTLOG
else
             pkill -F $INPID
        sudo pkill --signal SIGKILL -F $RNGDPID
        echo "stopping"
fi
