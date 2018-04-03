#upgrade ffmpeg from 2.5 to 2.8.4 for ubuntu14.04 only
config=$(cat /etc/lsb-release  | grep DISTRIB_RELEASE | tail -c 6)
if [ "$config" = "14.04" ]; then
    config=$(/usr/local/bin/ffmpeg -version | grep 'version 2.8.4')
    if [ -z "$config" ]; then
      /etc/init.d/stream_server_control stop
      service nginx stop
      echo "stop stream server"
      tar zxvf ffmpeg.tar.gz
      mv /usr/local/bin/ffmpeg /usr/local/bin/ffmpeg-2.5
      mv ffmpeg /usr/local/bin/ffmpeg
      echo "update ffmpeg program"
      service nginx start
      /etc/init.d/stream_server_control start
      echo "start stream server"
    else
      echo "stream server ffmpeg is already running in v2.8.4"
    fi
fi