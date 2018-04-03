#Validated on Jan-24,2018,
# This file will run every 2 minutes
## Detect Mysql error and auto restart tunnel server
## add KickOne notification 
#Writer: JinHo, Chang
workingFolder="/home/ivedasuper/tunnel"
#lastfile=$(ls -Art /var/log/tunnel_server/tunnel_server.log* | tail -n 1 )
lastfile=$(ls -lc /var/log/tunnel_server/tunnel_server.log* |tail -n 1| awk '{print$9}')
config=$(grep "Can't connect to MySQL server" $lastfile)
if [ ! -z "$config" ]; then
  echo "`date '+%Y/%m/%d %H:%M:%S'`: mysql connection broken on Tunnel $lastfile"
  /etc/init.d/tunnel_server restart
  echo "restart tunnel service"
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
#usage: SendAlarm "xx Server" "message"
SendAlarm(){
#check if email was send one hours ago
config60=$(find /var/tmp/tunservice.log -type f -mmin +60 -ls)
configemail=$(tail -n 50 /var/tmp/tunservice.log |grep 'Postfix')
#today sent Postfix
configemailtoday=$(grep -B6 'Postfix' /var/tmp/tunservice.log | grep "`date '+%Y/%m/%d'`")                  
#last line log has today's error
#todayerror=$(grep "`date '+%Y/%m/%d'`" /var/tmp/tunservice.log | tail -1 | grep 'Error')
#configEvent=$(tail -n 50 /var/tmp/tunservice.log |grep "`date '+%Y/%m/%d'`" | wc -l)
if [ ! -z "$configemail" ]; then #last 50line has sent email
  if [ ! -z "$configemailtoday" ]; then  #last sent by today
    if [ -z "$config60" ]; then #NOT over 60 minutes will skip
    return 1
    fi
  fi
fi

lanipall=$(ifconfig | awk '/inet addr/{print substr($2,6)}')
LocalIP=$(echo $lanipall | awk '{print $1}')
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
confignc=$(nc -zv $adminIP_PORT 2>&1| grep "succeeded")
if [ -z "$confignc" ]; then
adminIP_PORT="$SUBNET3.201 587"
  configncLocal=$(nc -zv $adminIP_PORT 2>&1| grep "succeeded")
  if [ -z "$configncLocal" ]; then #onePM
    adminIP_PORT="127.0.0.1 587"
  fi
fi
#echo "Send Email via $adminIP_PORT"
oem=$(dig +short myip.opendns.com @resolver1.opendns.com)
if [ -z "${oem##*";"*}" ] ;then #if oem contains ;
  oem="External Connection FAIL!"
else
  tmp=$(getOEMID $oem)
  if [ ! -z "$tmp" ]; then
    oem=$(echo "$oem($tmp)")
  fi
fi
echo "`date '+%Y/%m/%d %H:%M:%S'`: $2, send email to sysadmin."
{
echo "MAIL FROM: tunnel@safecity.com.tw"
sleep 1
echo "RCPT TO: alarm@tdi-megasys.com"
sleep 1
echo "DATA"
sleep 1
echo "To: XpressMIS <alarm@tdi-megasys.com>\nFrom: ${oem} <tunnel@safecity.com.tw>"
echo "Subject:$oem $1\n($oem)$2 @$LocalIP\n."
echo QUIT
} | telnet $adminIP_PORT  
}

config=$(tail -n 2000 $lastfile |grep KickOne)
if [ ! -z "$config" ]; then
  echo "`date '+%Y/%m/%d %H:%M:%S'`: Tunnel KickOne Error @$lastfile"
  config=$(grep KickOne $lastfile | tail -n 1)
  SendAlarm "Tunnel Server KickOne Error" "`date '+%Y/%m/%d %H:%M:%S'`: Tunnel KickOne Error\n$config \n in $lastfile" 
fi

oem=$(dig +short myip.opendns.com @resolver1.opendns.com)
if [ ! -z "${oem##*";"*}" ] ;then #if NOT oem contains ; (timeout)

if [ -f "$workingFolder/checkIP.py" ]; then 
#cmd="mysql -u isatRoot -pisatPassword -e 'select uid from isat.tunnel_server where external_address IS NULL OR external_address=\"\";'"
  #run hourly
  if [ "`date '+%M'`" = "00" ]; then
  config=$(sudo python $workingFolder/checkIP.py)
  if [ "$config" = "EXTERNAL_ADDRESS ERROR" ]; then
    SendAlarm "Tunnel Server Database External Address Error" "`date '+%Y/%m/%d %H:%M:%S'`: Tunnel Service Need Restart!!"
    echo "`date '+%Y/%m/%d %H:%M:%S'`: Tunnel Server Database External Address is Empty ($config)\n" 
    #/etc/init.d/tunnel_server restart
    #echo "restart tunnel service"
  #else
  #  echo $config
  fi
  fi
fi
else
  #empty public IP  #oem="External Connection FAIL!"
  SendAlarm "Tunnel Server External Address Error" "`date '+%Y/%m/%d %H:%M:%S'`: Tunnel External Address is Empty "
  echo "`date '+%Y/%m/%d %H:%M:%S'`: Tunnel Server External Address is Empty\n"
fi
  