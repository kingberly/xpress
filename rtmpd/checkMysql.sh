lastfile="/var/log/rtmpd_control.log"
config=$(tail -n 3 $lastfile | grep "MySQL server has gone away")
if [ ! -z "$config" ]; then
  echo "`date '+%Y/%m/%d %H:%M:%S'`: mysql connection broken on rtmp $lastfile"
  /etc/init.d/rtmpd_control restart
  echo "restart rtmpd service"
  #if one PM system, while stream server running,does not restart nginx
  config_info=$(ps -ef | awk '/[v]lc/{print $2}') 
  if [ -z "$config_info" ]; then
  /etc/init.d/nginx stop 2 /dev/null
  /etc/init.d/nginx start
  echo "restart nginx service"
  fi
fi
#config=$(grep "Can't connect to MySQL server" $lastfile)