
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

if [ -z "$1" -o -z "$2" ]; then
  echo "usage: $(basename "$0") <Stream LAN IP> <Stream External PORT>"
  echo "usage: $(basename "$0") $SUBNET3.<SubnetIP> <Stream External PORT>"
  exit
fi
if [ -f "/etc/haproxy/haproxy.cfg" ]; then
  config=$(grep $2 /etc/haproxy/haproxy.cfg)
  if [ -z "$config" ]; then
  IP=$1
  iplen=${#IP}
  if [ $iplen -le 3 ]; then
    IP=$(echo "$SUBNET3.$IP")
  fi
  echo "add $IP stream$2 to config"
  sed -i -e '$a\\nlisten stream'"$2"' :'"$2"'\n  mode  http\n  stats enable\n  balance  roundrobin\n  maxconn  250\n  server  stream '"$IP"':5544 check\n' /etc/haproxy/haproxy.cfg
  sudo service haproxy reload
  else
    echo "add $IP stream$2 already exist!"
  fi
else
  echo "haproxy is not installed.\n"
fi