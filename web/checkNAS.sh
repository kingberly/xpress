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
SendAlarm(){ #1title 2 percentage value
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
echo "`date '+%Y/%m/%d %H:%M:%S'`:$1 Disk usage $2 %, send email to xpress."
{
echo "MAIL FROM: web.checkNAS@safecity.com.tw"
sleep 1
echo "RCPT TO: alarm@tdi-megasys.com"
sleep 1
echo "DATA"
sleep 1
echo "To: Xpress <alarm@tdi-megasys.com>\nFrom: Xpress4.1 Server <web.checkNAS@safecity.com.tw>"
echo "Subject:$oem $1 Disk Alarm\n$1 @$LocalIP Disk Usage $2 %\n."
echo QUIT
} | telnet $adminIP_PORT  
}

nas="/media/videos"
#nas="/var/evostreamms/media"
#check mount point first
config_info=$(cat /proc/mounts | grep $nas)
if [ -z "$config_info" ]; then
  SendAlarm "Mount Point" "NA!"
else
#disk use > 80 alert, mailserver admin postfix
  config=$(df -h $nas | grep / | awk '{print $5}')
  config=$(echo "${config%?}") #remove last character
  configN=$(expr $config)
  if [ $configN -ge 80 ]; then
    SendAlarm "NAS" $config
  fi
fi
