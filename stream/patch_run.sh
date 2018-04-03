SCRIPT_ROOT=`dirname "$0"`
#upgrade ffmpeg  for 14.04
sh $SCRIPT_ROOT/ffmpeg.sh
#upgrade limit nginx  for 14.04
sh $SCRIPT_ROOT/limitconn.sh

WORKDIR="$( cd "$( dirname "$0" )" && pwd )"
sed -i -e 's|^workingFolder=.*|workingFolder="'"$WORKDIR"'"|' $SCRIPT_ROOT/checkMysql.sh
sudo cp $SCRIPT_ROOT/checkMysql.sh /usr/local/lib/stream_server_control/
#sed -i -e 's|alarm@tdi-megasys.com|servicedesk@iveda.com|g' /usr/local/lib/stream_server_control/checkMysql.sh
sudo cp $SCRIPT_ROOT/restartstr.sh /usr/local/lib/stream_server_control/
echo "installed checkMysql.sh/restartstr.sh to /usr/local/lib/stream_server_control/"
#sed -i -e 's|alarm@tdi-megasys.com|servicedesk@iveda.com|' /usr/local/lib/stream_server_control/checkMysql.sh
config_info=$(grep 'stream_server_control\/checkMysql.sh' /etc/crontab)
if [ -z "$config_info" ]; then #-z test if its empty string
#echo "*/2 *     * * *   root  sh /usr/local/lib/stream_server_control/checkMysql.sh  >> /var/tmp/streamservice.log" >> /etc/crontab
sed -i -e "\$a*/2 * * * * root  sh /usr/local/lib/stream_server_control/checkMysql.sh  >> /var/tmp/streamservice.log" /etc/crontab
echo "add crontab to check mysql every 2 minutes"
fi
#recycle stream log
config_info=$(grep '/var/log/evostreamms/' /etc/crontab)
if [ -z "$config_info" ]; then #-z test if its empty string
  echo "0 1     * * 0   root  find  /var/log/evostreamms/  -type f -mtime +60 -exec rm {} +" >> /etc/crontab
  echo "0 1     * * 0   root  find  /var/log/evostreamms/  -type f -mtime +14 -exec gzip {} +" >> /etc/crontab
  echo "write log recycle (60/14days) every sunday 1AM to /etc/crontab"
fi

sed -i -e '/find  \/var\/evostreamms\/temp\/  -type f -mtime +7/d'  /etc/crontab
#config_info=$(grep '/var/evostreamms/temp/  -type f -mtime +7' /etc/crontab)
#if [ -z "$config_info" ]; then #-z test if its empty string
  #sed -i -e "\$a0 1     * * 0   root  find  /var/evostreamms/temp/  -type f -mtime +7 -exec rm {} +" /etc/crontab
  #echo "write temp recycle (7days) to /etc/crontab"
#fi
config_info=$(grep '/var/evostreamms/temp/  -type f -size' /etc/crontab)
if [ -z "$config_info" ]; then #-z test if its empty string
  #sed -i -e "\$a* *     * * *   root  find  /var/evostreamms/temp/  -type f -size 0 -mmin +2 -ls >> /var/tmp/tempdel.log" /etc/crontab
  #sed -i -e "\$a* *     * * *   root  find  /var/evostreamms/temp/  -type f -size 0 -mmin +2 -exec rm {} +" /etc/crontab
  sed -i -e "\$a* *     * * *   root  find  /var/evostreamms/temp/  -type f -size -2048c -mmin +2 -ls >> /var/tmp/tempdel.log" /etc/crontab
  sed -i -e "\$a* *     * * *   root  find  /var/evostreamms/temp/  -type f -size -2048c -mmin +2 -exec rm {} +" /etc/crontab
  echo "recycle temp file < 2K and older than 2 minutes\n"
else
#replace
  sed -i -e '/find  \/var\/evostreamms\/temp\/  -type f -size/d'  /etc/crontab
  sed -i -e "\$a* *     * * *   root  find  /var/evostreamms/temp/  -type f -size -2048c -mmin +2 -ls >> /var/tmp/tempdel.log" /etc/crontab
  sed -i -e "\$a* *     * * *   root  find  /var/evostreamms/temp/  -type f -size -2048c -mmin +2 -exec rm {} +" /etc/crontab
  echo "recycle temp file < 2K and older than 2 minutes\n"
fi

config_info=$(grep 'dateext' /etc/logrotate.conf)
if [ -z "$config_info" ]; then #-z test if its empty string
  #add after create
  sed -i -e '/^create/a\dateext' /etc/logrotate.conf
  echo "add dateext to logrotate.conf" 
fi
#logrotate -f /etc/logrotate.conf
if [ ! -f "/etc/logrotate.d/tempdel" ]; then #-z test if its empty string
  cp tempdel /etc/logrotate.d/
  echo "add tempdel logrotate to logrotate.conf"
  sed -i -e '/tempdel.log  -type f -mtime +7 -exec gzip/d'  /etc/crontab
  echo "remove previous gzip tempdel.log from crontab" 
fi

config=$(cat /etc/lsb-release  | grep DISTRIB_RELEASE | tail -c 6)
if [ "$config" = "14.04" ]; then
filever=$(grep PACKAGE_VERSION /usr/local/lib/stream_server_control/package_version.py | tail -c 11)
  if [ ! -z "$filever" ]; then
      echo $filever | sudo tee version  #tee -a ==> add to file
      chmod 777 version
      #wget -q  -O - 'http://<stream LAN IP>:5544/vod/version'
      #cp version /var/evostreamms/media/
      CURRENT_DIR="$PWD"  #if current version is not in the stream folder, copy to target folder
      config_info=$(echo "$CURRENT_DIR" | grep '/stream')
      if [ -z "$config_info" ]; then
         cp version ./stream/  #no stream folder
         echo "copy version file to stream/ folder"
      fi
  fi
#fi

fi