SCRIPT_ROOT=`dirname "$0"`
if [ "$1" = "remove" ]; then
    /etc/init.d/tunnel_server stop
    rm -rf /usr/local/lib/tunnel_server
    rm /etc/tunnel_server.conf
    rm /etc/init.d/tunnel_server
    sed -i -e '/var\/log\/tunnel_server/d'  /etc/crontab
    sed -i -e '/etc\/init.d\/tunnel_server restart/d'  /etc/crontab
    sed -i -e '/checkMysql.sh/d'  /etc/crontab
    sed -i -e '/checkLog.sh/d'  /etc/crontab
    #sed -i -e '/check_readonly.sh/d'  /etc/crontab 
    echo "remove installed service."
    exit  
else
  echo "to remove server usage: sudo sh $0 remove"
fi

#fix install script source.d list issue
#if [ -f "/etc/apt/sources.list.d/semiosis-ubuntu-glusterfs-3_5-precise.list" ]; then 
#  rm -rf /etc/apt/sources.list.d/semiosis-ubuntu-glusterfs-3_5-precise.list*
#fi
#recycle tunnel log
config_info=$(grep '/var/log/tunnel_server' /etc/crontab)
if [ -z "$config_info" ]; then #-z test if its empty string
  echo "0 1     * * 0   root  find  /var/log/tunnel_server  -type f -mtime +30 -exec rm {} +" >> /etc/crontab
  echo "0 1     * * 0   root  find /var/log/tunnel_server  -type f -mtime +14 -exec gzip {} +" >> /etc/crontab
  echo "write log recycle (30/14days) to /etc/crontab"
fi

config_info=$(grep 'dateext' /etc/logrotate.conf)
if [ -z "$config_info" ]; then #-z test if its empty string
  #add after create
  sed -i -e '/^create/a\dateext' /etc/logrotate.conf
  echo "add dateext to logrotate.conf" 
fi
#logrotate will fail to continue capture log
#logrotate -v /etc/logrotate.conf
#if [ ! -f "/etc/logrotate.d/tunnel_server_log" ]; then
#  cp tunnel_server_log /etc/logrotate.d/
#  echo "add tunnel_server_log logrotate to logrotate.conf"
#fi 

sudo cp $SCRIPT_ROOT/checkMysql.sh /usr/local/lib/tunnel_server/
sudo cp $SCRIPT_ROOT/checkLog.sh /usr/local/lib/tunnel_server/
echo "installed checkMysql.sh/checkLog.sh to /usr/local/lib/tunnel_server/"
config_info=$(grep 'tunnel_server restart' /etc/crontab)
if [ ! -z "$config_info" ]; then #-z test if its empty string
  #echo "0 1     * * 0   root  /etc/init.d/tunnel_server restart" >> /etc/crontab
  #delete cmd
  sed -i -e '/tunnel_server restart/d'  /etc/crontab
fi
config_info=$(grep 'tunnel_server\/checkMysql.sh' /etc/crontab)
if [ -z "$config_info" ]; then #-z test if its empty string
#echo "*/2 *     * * *   root  sh /usr/local/lib/tunnel_server/checkMysql.sh  >> /var/tmp/tunservice.log" >> /etc/crontab
sed -i -e "\$a*/2 * * * * root  sh /usr/local/lib/tunnel_server/checkMysql.sh  >> /var/tmp/tunservice.log" /etc/crontab
echo "add crontab to check mysql every 2 minutes"
fi
config_info=$(grep 'checkLog.sh' /etc/crontab)
if [ -z "$config_info" ]; then #-z test if its empty string
#check on sunday
#echo "0 1     * * 0   root  sh /usr/local/lib/tunnel_server/checkLog.sh  >> /var/tmp/tunservice.log" >> /etc/crontab
sed -i -e "\$a0 1     * * 0   root  sh /usr/local/lib/tunnel_server/checkLog.sh  >> /var/tmp/tunservice.log" /etc/crontab
echo "add ceontab to check log size every sunday"
fi

