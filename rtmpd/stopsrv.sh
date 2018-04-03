#sudo netstat -ntlp
if [ "$1" = "restart" ]; then
    /etc/init.d/nginx stop
    /etc/init.d/nginx start
    /etc/init.d/rtmpd_control stop
    /etc/init.d/rtmpd_control start
    echo "restart service."
else
  /etc/init.d/nginx stop
  /etc/init.d/rtmpd_control stop
  echo "stop rtmp service"
fi
