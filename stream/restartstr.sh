myPrint(){
  echo "\\033[31m$1\\033[0m"
}

/etc/init.d/stream_server_control stop
myPrint "stopped stream service"
config_info=$(ps -ef | awk '/[f]fmpeg/{print $2}')
if [ ! -z "$config_info" ]; then # ! -z if it is not empty
  myPrint " killing ffmpeg pid $config_info "
  kill -9 $config_info
fi
config_info=$(ps -ef | awk '/[v]lc/{print $2}')
if [ ! -z "$config_info" ]; then # ! -z if it is not empty
  myPrint " killing vlc pid $config_info "
  kill -9 $config_info
fi
service nginx restart
myPrint " restarted nginx "
sleep 10
sudo /etc/init.d/stream_server_control start
grep daemon.py /proc/`cat /var/run/evostreamms/stream_server_control.pid`/cmdline || Exit 
myPrint " started stream service "
sleep 10
ps ax | grep [v]lc
myPrint " checked above vlc process (1) "
sleep 10
ps ax | grep [/u]sr/local/bin/ffmpeg
myPrint " check ffmpeg process-camera upload streaming (ps ax | grep ffmpeg) "