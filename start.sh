#! /bin/bash

FIFO=/tmp/michwr_fifo
INPID=/tmp/michwr_input.pid
RNGDPID=/tmp/michwr_rngd.pid

sudo echo "sudo is set"

if [ "$1" != "stop" ]
then
    if [ ! -p $FIFO ]
    then
            mkfifo $FIFO
    fi
    (/usr/bin/nohup /usr/bin/php ./index.php -raw > $FIFO) &
    echo $! > $INPID
    sudo /usr/sbin/rngd -p $RNGDPID  -r $FIFO &
    echo "starting"
    tail -F /tmp/michwr_log.txt
else
             pkill -F $INPID
        sudo pkill --signal SIGKILL -F $RNGDPID
        echo "stopping"
fi
