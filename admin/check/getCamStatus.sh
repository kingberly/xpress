stream_server="192.168.1.135"
web_server="192.168.1.100"
if [ ! -z "$1" ]; then
#NOWDAY=$(date +"%d")
#NOWTIME=$(date +"%H:%M")
#UPDATEDAY=$(ls -l /var/tmp/online_list.csv | awk '{printf $7}') #May 19 <-19
#UPDATETIME=$(ls -l /var/tmp/online_list.csv | awk '{printf $8}') #12:35
file_ts=$(ls -lc --time-style="+%Y%m%d%H%M%S" /var/tmp/online_list.csv | awk '{printf $6}')
secondsDiff=$(( `date '+%Y%m%d%H%M%S'` - $file_ts ))
if [ $secondsDiff -gt 300 ]; then
  wget --quiet http://ivedaManageUser:ivedaManagePassword@$web_server/manage/fetch_online_clients.php -O /var/tmp/online_list.csv 
fi
tunnel_status=$(grep $1 /var/tmp/online_list.csv)
  if [ ! -z "$tunnel_status" ]; then
    #echo $1 " Connected."
    echo $1
    python /home/ivedasuper/admin/check/getStreamingStatus.py $stream_server $1
  else
    echo $1 " Not Connected."
  fi
else
  echo "sh getCamStatus.sh <MAC>"
fi  