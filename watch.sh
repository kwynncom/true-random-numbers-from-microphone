LOGF=/tmp/michwr_log.txt

nohup php watch.php >> $LOGF 2>&1 &
tail -F $LOGF
