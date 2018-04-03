#14.04 default is 14.33 now
config_infol=$(dpkg -l | grep lighttpd | awk '{print $3}' | head -c 6)
echo "current lighttpd version: $config_infol\n"
if [ "$config_infol" = "1.4.28" ]; then
config_info=$(ls /var/lib/apt/lists/ |grep 'nathan-renniewaldock' )
if [ -z "$config_info" ]; then
  sudo rm -rf /etc/apt/sources.list.d/lighttpd.list
  sudo rm -rf /var/lib/apt/lists/* 
  sudo add-apt-repository ppa:nathan-renniewaldock/ppa
  sudo apt-get update
  sudo apt-get -y --force-yes install lighttpd #first y, next two Default/N
fi #ppa check
#elif [ "$config_infol" = "1.4.33" ]; then
  #sudo rm -rf /etc/apt/sources.list.d/lighttpd.list
  #sudo rm -rf /var/lib/apt/lists/*
  #sudo add-apt-repository ppa:cheako/lighttpd
  #sudo apt-get update
  #sudo apt-get -y --force-yes install lighttpd
fi
if [ -f "/usr/sbin/lighttpd" ]; then
echo "upgrade lighttpd to $(/usr/sbin/lighttpd -v | grep lighttpd | awk '{print $1}')\n"  
fi