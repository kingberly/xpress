if [ "$1" = "remove" ]; then
apt-get remove haproxy
exit
elif [ "$1" = "check" ]; then
  config=$(grep "checkha.sh" /etc/crontab)
  if [ -z "$config" ]; then
  if [ -f "checkha.sh" ]; then
      curPATH=$(readlink -f checkha.sh)
      sed -i -e "\$a*/2 * * * * root  sh $curPATH" /etc/crontab
      echo "add checkha.sh to crontab"
      #sed -i -e '/checkha.sh/d'  /etc/crontab
  fi
  fi
elif [ "$1" = "addweb" ]; then
  config=$(dpkg -l | grep haproxy)
  if [ -z "$config" ]; then
  echo "HAProxy Not Installed. Start Install and Config!"
  apt-get -y --force-yes install haproxy 
  cp haproxy.cfg /etc/haproxy/
  sed -i -e '/server  inst1 127.0.0.1:443 check inter 2000 fall 3/d' /etc/haproxy/haproxy.cfg 
  fi
  config=$(dpkg -l | grep heartbeat)
  if [ -z "$config" ]; then
    echo "HeartBeat Not Installed. Please re-install if HAService is Required"
  fi
LocalIP=$(ifconfig | awk '/inet addr/{print substr($2,6)}' | awk '{print $1}')
#SUBNET3=$(echo $LocalIP | awk -F '.' 'BEGIN { OFS = ".";}{print $1,$2,$3}')
for SUBNET in $LocalIP
  do
    SUBNET3=$(echo $SUBNET | awk -F '.' 'BEGIN { OFS = ".";}{print $1,$2,$3}')
    if [ "$SUBNET3" = "127.0.0" ]; then
      continue
    else
      break
    fi
  done

  if [ -z "$2" ]; then
    echo "No IP parameter to apply"
    exit
  else
  IP=$2
  iplen=${#IP}
  if [ $iplen -le 3 ]; then
    myIP=$(echo "$SUBNET3.$IP")
  else
    myIP=$IP
    IP=$(echo $myIP | awk -F '.' 'BEGIN { OFS = ".";}{print $4}')
  fi
  config=$(grep "$myIP" /etc/haproxy/haproxy.cfg)
  if [ -z "$config" ]; then
    echo "add $myIP to config http/https"
    #server  inst115 192.168.1.115:443 check inter 2000 fall 3
    #server          http115 192.168.1.115:80 check inter 2000 fall 3
    #insert line after first match only
    sed -i -e '0,/^        server  inst/s||        server  inst'"$IP"' '"$myIP"':443 check inter 2000 fall 3\n&|' /etc/haproxy/haproxy.cfg
    sed -i -e '0,/^        server          http/s||        server          http'"$IP"' '"$myIP"':80 check inter 2000 fall 3\n&|' /etc/haproxy/haproxy.cfg
    sudo service haproxy reload
  else
      echo "$IP EXISTED @/etc/haproxy/haproxy.cfg"
  fi
  fi
elif [ "$1" = "addstream" ]; then
  if [ -z "$3" ]; then
    echo "No IP/Port parameter to apply"
    exit
  fi
  sudo sh streamvip.sh $2 $3
else
  echo "usage: sh $0 remove/addweb <IP/SUBNET4>/addstream <IP/SUBNET4> <Ext Port>"
  echo "sh $0 addweb 115"
  echo "sh $0 addstream 131 5544"
fi
