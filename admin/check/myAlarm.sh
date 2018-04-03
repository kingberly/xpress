#Validated on Feb-6,2018,
# This file will run every hour
## add minerd detection
##sed -i -e "\$a* */1 * * * root  sh /home/ivedasuper/admin/check/myAlarm.sh" /etc/crontab 
#Writer: JinHo, Chang

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
myEmail="admin@safecity.com.tw"
alarmEmail="alarm@tdi-megasys.com"
lanipall=$(ifconfig | awk '/inet addr/{print substr($2,6)}')
LocalIP=$(echo $lanipall | awk '{print $1}')
adminIP_PORT="127.0.0.1 587"
confignc=$(nc -zv $adminIP_PORT 2>&1| grep "succeeded")
if [ -z "$confignc" ]; then
  echo "No Postfix to send email"
  exit
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
echo "`date '+%Y/%m/%d %H:%M:%S'`: $2, send email to sysadmin."
{
echo "MAIL FROM: $myEmail"
sleep 1
echo "RCPT TO: $alarmEmail"
sleep 1
echo "DATA"
sleep 1
echo "To: XpressMIS <$alarmEmail>\nFrom: ${oem} <$myEmail>"
echo "Subject:$oem $1\n($oem)$2 @$LocalIP\n."
echo QUIT
} | telnet $adminIP_PORT  
}

#only bash support array
#tPATH=("/usr/sbin/minerd" "/usr/sbin/monero" "/usr/src/cpuminer-multi" "/usr/src/exit")
#  n=1 ; eval a$n="/usr/sbin/minerd"
#  n=2 ; eval a$n="/usr/sbin/monero"
#  n=3 ; eval a$n="/usr/src/cpuminer-multi"
#  n=4 ; eval a$n="/usr/src/exit"
#  for i in 1 2 3 4; do
#  tmp=$(eval echo \$a$i)
#  done
if [ ! -z "$(ps ax | grep [m]inerd)" ]; then
  echo "`date '+%Y/%m/%d %H:%M:%S'`: minerd running"
  SendAlarm "minerd running" "`date '+%Y/%m/%d %H:%M:%S'`: minerd running"
  sed -i -e '/minerd/d'  /etc/crontab
  sed -i -e '/monero/d'  /etc/crontab
  for tmp in "/usr/sbin/minerd" "/usr/sbin/monero" "/usr/src/cpuminer-multi" "/usr/src/exit"; do
  if [ -f "$tmp" ]; then
    sudo rm -rf $tmp
  else
    echo "$tmp Not found" 
  fi
  done
  config_info=$(ps -ef | awk '/[m]inerd/{print $2}')
  sudo kill -9 $config_info 
else
#  SendAlarm "No minerd running" "`date '+%Y/%m/%d %H:%M:%S'`: No minerd running"
echo "No minerd detected"
fi

#uptime | awk '{print $10$11$12}'
cpu=$(uptime | awk '{print $12}') #NN.NN
cpuN=$(awk -v v="$cpu" 'BEGIN{printf "%d", v}')
thiscpu=$(cat /proc/cpuinfo | grep processor | wc -l)
thiscpuN=$(awk -v v="$thiscpu" 'BEGIN{printf "%d", v}')
#cpu 50%
thiscpuNthreshold=$(awk -v v="$(expr $thiscpuN / 2)" 'BEGIN{printf "%d", v}')
if [ $cpuN -gt $thiscpuNthreshold ]; then
SendAlarm "CPU load Alarm" "`date '+%Y/%m/%d %H:%M:%S'`: CPU load average is $cpu \n(threshold=$thiscpuNthreshold)"
else
echo "current cpu=$cpu (threshold=$thiscpuNthreshold)"
fi