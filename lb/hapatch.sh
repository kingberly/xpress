#check if network use 16, default instsal to 24
if [ -z "$1" ]; then
netmask=$(ifconfig "eth0" | sed -rn '2s/ .*:(.*)$/\1/p')
else
netmask=$(ifconfig "$1" | sed -rn '2s/ .*:(.*)$/\1/p')
fi
echo "netmask=$netmask"
if [ ! -z "$netmask" ]; then
#${#netmask}  #length=11
#${netmask%.*}  #=255.255.0
#last=${netmask##*.}  #last 0
#awk '{print $1,$2}'  #print space between 
#last=$(echo $netmask | awk -F"." '{print $NF}' )  #last
#last2=$(echo $netmask | awk -F"." '{print $(NF-1)}' )  #last 2nd
#n16=$(echo $last2.$last )
n16=$(echo $netmask | awk -F"." '{printf "%s.%s",$(NF-1),$NF}')
if [ "$n16" = "0.0" ]; then
config=$(grep "/24/" /etc/ha.d/haresources)
if [ ! -z "$config" ]; then 
sed -i -e 's|/24/|/16/|' /etc/ha.d/haresources
echo "haresources set=$(cat /etc/ha.d/haresources)"
/etc/init.d/heartbeat restart
else
  echo "haresources was set=$(cat /etc/ha.d/haresources)"  
fi
else
  echo "current haresources=$(cat /etc/ha.d/haresources)"
fi
fi

config=$(ps ax | grep [h]eartbeat)
if [ -z "$config" ]; then
  /etc/init.d/heartbeat start
fi

config=$(ps ax | grep [/]watchdog)
if [ -z "$config" ]; then
  /etc/init.d/watchdog start
fi
