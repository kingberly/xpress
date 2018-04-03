#https://redmine.lighttpd.net/projects/1/wiki/Docs_Performance
#Disabling keep-alive completely is the last resort if you are still short on file descriptors:  server.max-keep-alive-requests = 0
#cat /proc/`ps ax | grep lighttpd | grep -v grep | awk -F " " '{print $1}'`/limits |grep "Max open files"  
  
#81 / reconnect.html
SCRIPT_ROOT=`dirname "$0"`
cp $SCRIPT_ROOT/rpic/images/reconnect*  /var/www/SAT-CLOUDNVR/images/
cp $SCRIPT_ROOT/rpic/images/timout*  /var/www/SAT-CLOUDNVR/images/
configbind=$(sudo lsof -i -P -n | grep LISTEN | grep 81)
config=$(grep ":81"  /etc/lighttpd/lighttpd.conf)
if [ -z "$configbind" ]; then
  if [ -z "$config" ]; then
  echo "add port 81 access for system busy message"
  #sudo sed -i -e 's|server.error-handler-404 = "reconnect.html"|server.error-handler-404 = "timeout.mp4"|'  /etc/lighttpd/lighttpd.conf
  sed -i -e '$a\\n$SERVER["socket"] == ":81" {\nserver.document-root = "/var/www/SAT-CLOUDNVR/images/"\nindex-file.names = ( "reconnect.html" )\nserver.error-handler-404 = "timeout.mp4"\nserver.max-fds = 8192\nserver.max-connections = 4096\nserver.max-read-idle = 15\nserver.max-keep-alive-requests = 4\nserver.max-keep-alive-idle = 4\n}' /etc/lighttpd/lighttpd.conf
  /etc/init.d/lighttpd force-reload
  else
    echo "port 81 not used. check /etc/lighttpd/lighttpd.conf"
  fi
else
  if [ -z "$config" ]; then
    echo "port 81 was bind by other service"
  else
    echo "manually update /etc/lighttpd/lighttpd.conf"
  fi
fi
#5544 / mp4
configbind=$(sudo lsof -i -P -n | grep LISTEN | grep 5544)
config=$(grep ":5544"  /etc/lighttpd/lighttpd.conf)
if [ -z "$configbind" ]; then
if [ -z "$config" ]; then
echo "add port 5544 access for timeup message"
#sed -i -e 's|server.error-handler-404 = "timeout.png"|server.error-handler-404 = "reconnect.mp4"|'  /etc/lighttpd/lighttpd.conf
sed -i -e '$a\\n$SERVER["socket"] == ":5544" {\nserver.document-root = "/var/www/SAT-CLOUDNVR/images/"\nindex-file.names = ( "reconnect.png" )\nserver.error-handler-404 = "reconnect.mp4"\nserver.max-fds = 8192\nserver.max-connections = 4096\nserver.max-read-idle = 15\nserver.max-keep-alive-requests = 4\nserver.max-keep-alive-idle = 4\n}' /etc/lighttpd/lighttpd.conf
/etc/init.d/lighttpd force-reload
else
  echo "port 5544 not used. check /etc/lighttpd/lighttpd.conf"
fi
else   #bind
if [ -z "$config" ]; then
  echo "port 5544 was bind by other service"
else
  echo "manually update /etc/lighttpd/lighttpd.conf"
fi
fi 