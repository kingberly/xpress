# sh $0 test
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
SendAlarm(){
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
#if I am admin
if [ "$IP" = "$SUBNET3.201" -o "$IP" = "$SUBNET3.202" ]; then
adminIP_PORT="$IP 587"
else
#check HA admin
adminIP_PORT="$SUBNET3.200 587"
confignc=$(nc -zv $adminIP_PORT 2>&1| grep "succeeded")
if [ -z "$confignc" ]; then
#PLDT use 201 #echo "subIP 200 is closed"
adminIP_PORT="$SUBNET3.201 587"
  configncLocal=$(nc -zv $adminIP_PORT 2>&1| grep "succeeded")
  if [ -z "$configncLocal" ]; then
    echo "Admin Email Server Not Available!!!"
    exit
  fi
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
echo "`date '+%Y/%m/%d %H:%M:%S'`:heartbeat transfer, send email."
{
echo "MAIL FROM: lb@safecity.com.tw"
sleep 1
echo "RCPT TO: alarm@tdi-megasys.com"
sleep 1
echo "DATA"
sleep 1
echo "To: XpressMIS <alarm@tdi-megasys.com>\nFrom: ${oem} <lb@safecity.com.tw>"
echo "Subject:$oem HA Alarm\n($oem) Load Balancer $IP heartbeat takeover occurred.\n."
echo QUIT
} | telnet $adminIP_PORT  
}

#"Taking over resource#
#"mach_down takeover complete."
config=$(tail /var/log/ha-log | grep "mach_down takeover complete.")
if [ ! -z "$config" ]; then
   SendAlarm
else
  if [ "$1" = "test" ]; then
    echo "send test alarm:"
    SendAlarm
    exit
  fi
fi 