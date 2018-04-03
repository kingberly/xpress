#Validated on Feb-2,2018,
# This file will run every 2 minutes
## Detect Mysql
## send Email Alarm /restart stream server while Error occurred
## fix external connection error subject
workingFolder="/home/ivedasuper/stream"
#stream server will keep trying mysql
lastfile="/var/log/evostreamms/stream_server_control.log"
if [ ! -f "$lastfile" ]; then
  echo "stream log file $lastfile not exist."
  exit
fi
config=$(tail $lastfile | grep "Can't connect to MySQL server")
if [ ! -z "$config" ]; then
  echo "`date '+%Y/%m/%d %H:%M:%S'`: mysql connection broken on Stream"
  #sh restartstr.sh
  #echo "restart stream service"
fi

getOEMID(){
  if [ "$1" = "111.235.240.69" ]; then
    echo "T04"
  elif  [ "$1" = "60.249.130.50" ]; then
    echo "K01"
  elif  [ "$1" = "211.75.70.35" ]; then
    echo "T05"
  elif  [ "$1" = "61.216.50.175" ]; then
    echo "C13"
  elif  [ "$1" = "61.216.61.162" -o "$1" = "125.227.139.173" ]; then
    echo "X02"
  elif  [ "$1" = "59.124.70.86" ]; then
    echo "X01"
  fi
}
#usage: SendAlarm "xx Server" "alarm" "my log"
SendAlarm(){
#check if email was send one hours ago
config60=$(find /var/tmp/streamservice.log -type f -mmin +60 -ls)
configemail=$(tail -n 50 /var/tmp/streamservice.log |grep 'Postfix')
#today sent Postfix
configemailtoday=$(grep -B6 'Postfix' /var/tmp/streamservice.log | grep "`date '+%Y/%m/%d'`")                  

if [ ! -z "$configemail" ]; then #last 50line has sent email
  if [ ! -z "$configemailtoday" ]; then  #last sent by today
    if [ -z "$config60" ]; then #NOT over 60 minutes ago will skip
      return 1
    fi
  fi
fi

lanipall=$(ifconfig | awk '/inet addr/{print substr($2,6)}')
LocalIP=$(echo $lanipall | awk '{print $1}')
#awk seperate -F by . and print out OFS with .
#SUBNET3=$(echo $LocalIP | awk -F '.' 'BEGIN { OFS = ".";}{print $1,$2,$3}')
for IP in $LocalIP
  do
    SUBNET3=$(echo $IP | awk -F '.' 'BEGIN { OFS = ".";}{print $1,$2,$3}')
    if [ "$SUBNET3" = "127.0.0" ]; then
      continue
    else
      break
    fi
  done
adminIP_PORT="$SUBNET3.200 587"
#output 0/open, 1/close
#confignc=$(nc -zv $adminIP_PORT &> /dev/null; echo $?)
confignc=$(nc -zv $adminIP_PORT 2>&1| grep "succeeded")
#echo "=$confignc="
#if [ "$confignc" -eq 1 ]; then #close, status code error???
if [ -z "$confignc" ]; then
#PLDT use 201
#echo "subIP 200 is closed"
adminIP_PORT="$SUBNET3.201 587"
  configncLocal=$(nc -zv $adminIP_PORT 2>&1| grep "succeeded")
  if [ -z "$configncLocal" ]; then #onePM
    adminIP_PORT="127.0.0.1 587"
  fi
fi
oem=$(dig +short myip.opendns.com @resolver1.opendns.com)
if [ -z "${oem##*";"*}" ] ;then #if oem contains ;
  oem="External Connection FAIL!"
else
  tmp=$(getOEMID $oem)
  if [ ! -z "$tmp" ]; then
    oem=$(echo "$oem($tmp)")
  fi
fi
if [ "$2" = "diskusage" ]; then
echo "`date '+%Y/%m/%d %H:%M:%S'`:$1 Disk usage $3 %, send email to xpress."
elif [ "$2" = "ioerror" ]; then
echo "`date '+%Y/%m/%d %H:%M:%S'`:$1 IOError $3 times, send email to xpress."
elif [ "$2" = "ERROR" ]; then
echo "`date '+%Y/%m/%d %H:%M:%S'`:$1 ERROR $3 times, send email to xpress."
elif [ "$2" = "alarm" ]; then
echo "`date '+%Y/%m/%d %H:%M:%S'`:$1 Detect Alarm $3, send email to xpress."
else
  return 0
fi
{
echo "MAIL FROM: stream@safecity.com.tw"
sleep 1
echo "RCPT TO: alarm@tdi-megasys.com"
sleep 1
echo "DATA"
sleep 1
echo "To: XpressMIS <alarm@tdi-megasys.com>\nFrom: ${oem} <stream@safecity.com.tw>"
if [ "$2" = "diskusage" ]; then
echo "Subject:$oem $1 Disk Alarm\n($oem)$1 @$LocalIP Disk Usage $3 %\n."
elif [ "$2" = "ioerror" ]; then
echo "Subject:$oem $1 IOError\n($oem)$1 @$LocalIP \n$3 times\n."
elif [ "$2" = "ERROR" ]; then
echo "Subject:$oem $1 ERROR\n($oem)$1 @$LocalIP \n$3 times\n."
elif [ "$2" = "alarm" ]; then
echo "Subject:$oem $1 Detect Alarm\n($oem)$1 @$LocalIP \n$3\n."
fi
echo QUIT
} | telnet $adminIP_PORT  
}

config=$(tail $lastfile | grep "ERROR: error list index out of range")
if [ ! -z "$config" ]; then
  #avoid idle server keep restart
  if [ -z "$(tail $lastfile |grep 'kill_process kill child with SIGKILL')" ]; then
  echo "`date '+%Y/%m/%d %H:%M:%S'`: Error error list index on Stream"
  SendAlarm "Stream Server" "alarm" $config
  sh /usr/local/lib/stream_server_control/restartstr.sh
  echo "restart stream service"
  fi
fi
#ReadTimeoutError: HTTPConnectionPool
#ERROR: daemon_main HTTPConnectionPool
#ERROR: run HTTPConnectionPool
#MaxRetryError: HTTPConnectionPool
config=$(tail $lastfile |grep "HTTPConnectionPool" )
if [ ! -z "$config" ]; then
  #avoid idle server keep restart
  if [ -z "$(tail $lastfile |grep 'kill_process kill child with SIGKILL')" ]; then
  echo "`date '+%Y/%m/%d %H:%M:%S'`: Error HTTPConnectionPool occurred on Stream server"
	SendAlarm "Stream Server" "alarm" $config 
    sh /usr/local/lib/stream_server_control/restartstr.sh
    echo "restart stream service"
  fi
fi
#disk use > 50 alert, mailserver admin postfix
config=$(df -h / | grep / | awk '{print $5}')
config=$(echo "${config%?}") #remove last character
configN=$(expr $config)
if [ $configN -ge 50 ]; then
  configsize=$(find /var/evostreamms/temp/ -name '*.ts' -exec ls -l --block-size=M {} \; | awk '{ Total += $5} END { print Total }')
  echo "temp folder ts video size: $configsize MB (${config}%)"
  SendAlarm "Stream Server" "diskusage" $config
fi

config=$(tail $lastfile |grep "IOError" )
if [ ! -z "$config" ]; then
  config=$(tail $lastfile |grep -c "IOError")
  configN=$(expr $config)
  echo "`date '+%Y/%m/%d %H:%M:%S'`: IOError occurred $configN times on Stream" 
  SendAlarm "Stream Server" "ioerror" $config
  find  /var/evostreamms/temp/  -type f -size -5120c -mmin +2 -ls >> /var/tmp/tempdel.log
  find  /var/evostreamms/temp/  -type f -size -5120c -mmin +2 -exec rm {} +
fi
#ERROR: daemon_main no element found (16)
#ParseError: no element found
#skip ERROR: recycleRecording
config=$(tail -n 50 $lastfile |grep "ERROR:" )
if [ ! -z "$config" ]; then
  configF=$(tail -n 50 $lastfile |grep -c "ERROR: recycleRecording")
  configFN=$(expr $configF)
  config=$(tail -n 50 $lastfile |grep -c "ERROR:")
  configN=$(expr $config)
  adiff=$(expr $configN - $configFN)
  if [ $adiff -gt 0 ]; then
  echo "`date '+%Y/%m/%d %H:%M:%S'`: ERROR occurred $adiff times on Stream"
  if [ $adiff -ge 3 ]; then
    #avoid idle server keep restart
    if [ -z "$(tail $lastfile |grep 'kill_process kill child with SIGKILL')" ]; then
    SendAlarm "Stream Server" "ERROR" $config
    sh /usr/local/lib/stream_server_control/restartstr.sh
    echo "ERROR 3 times, restart stream service"
    fi
  fi
  fi
fi

config=$(find  /var/evostreamms/temp/  -type f -size +10M -mmin +90 -ls)
if [ ! -z "$config" ]; then
  echo "`date '+%Y/%m/%d %H:%M:%S'`: temp folder video files fail to transcode over 90minutes"
  #config=$(echo $config | awk '{print $11}')
  echo $config
  if [ -d "$workingFolder" ]; then
    #auto transcode
    cd $workingFolder
    sh ts2mp4batch.sh camera auto
    sh ts2mp4batch.sh ivedamobile auto 
    #SendAlarm "Stream Server" "alarm" "temp folder video files auto-transcoded"
  else
  SendAlarm "Stream Server" "alarm" "temp folder video files fail to transcode over 90minutes"
  fi
  
fi