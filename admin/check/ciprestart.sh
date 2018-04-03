#Validated on Dec-22,2017,
# will restart IvedaXpress Service
#Writer: JinHo, Chang
LocalIP=$(echo $(ifconfig | awk '/inet addr/{print substr($2,6)}') | awk '{print $1}')
echo "current LAN IP $LocalIP" 
echo "current Public IP $(dig +short myip.opendns.com @resolver1.opendns.com)"

sudo /etc/init.d/stream_server_control restart
sudo /etc/init.d/tunnel_server restart
sudo /etc/init.d/web_server_control  restart
sudo /etc/init.d/rtmpd_control restart
echo "Stream/Tunnel/Web/Rtmp service Restarted! \n Check if Database Server IP setting is the same."