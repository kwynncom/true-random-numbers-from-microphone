OUTLOG=/tmp/michwr_log.txt
echo "" > $OUTLOG
nohup ./start20.sh "$@" &
disown -a
tail -F /tmp/michwr_log.txt
