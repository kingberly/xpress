if [ "$1" = "remove" ]; then
    /etc/init.d/nginx stop
    /etc/init.d/rtmpd_control stop
    rm -rf /usr/local/lib/rtmpd_control
    rm /etc/rtmpd_control.conf
    rm /etc/init.d/rtmpd_control
    echo "remove installed service."
    exit  
else
  echo "to remove rtmp server usage: sudo sh patch.sh remove"
  config_info=$(ps ax | grep [n]ginx)
  if [ -z "$config_info" ]; then
  echo "nginx restart fail. Fix install.sh"
  sed -i -e '/etc\/init.d\/nginx restart/d'  ../isat_rtmpd/install.sh
  config_info1=$(grep 'nginx stop 2' ../isat_rtmpd/install.sh)
  if [ -z "$config_info1" ]; then
      sed -i -e '/etc\/init.d\/rtmpd_control stop/i\sudo \/etc\/init.d\/nginx stop 2>\/dev\/null' ../isat_rtmpd/install.sh
      sed -i -e '/etc\/init.d\/rtmpd_control stop/i\sudo \/etc\/init.d\/nginx start' ../isat_rtmpd/install.sh
      cd ../isat_rtmpd
      sudo sh install.sh
      cd ../rtmpd
  fi
  fi
fi
#make version
#filename=$(ls -Art ../03-*.tar.gz | tail -n 1)
#if [ -z "$filename" ]; then #if current folder is at home directory
#  filename=$(ls -Art 03-*.tar.gz | tail -n 1)
#  echo "get filename from home directory"
#fi
#if [ ! -z "$filename" ]; then
#  filever=$(echo $filename | tail -c 25 | head -c 8)
filever=$(grep PACKAGE_VERSION /usr/local/lib/rtmpd_control/package_version.py | tail -c 11)
  if [ ! -z "$filever" ]; then
      echo $filever | sudo tee version  #tee -a ==> add to file
      chmod 777 version
      CURRENT_DIR="$PWD"  #if current version is not in the stream folder, copy to target folder
      config_info=$(echo "$CURRENT_DIR" | grep '/rtmpd')
      if [ -z "$config_info" ]; then
         cp version ./rtmpd/  #no stream folder
         echo "copy version file to rtmpd/ folder"
      fi
  fi
#fi

sudo cp checkMysql.sh /usr/local/lib/rtmpd_control/
echo "installed checkMysql.sh to /usr/local/lib/rtmpd_control/"
config_info=$(grep 'rtmpd_control\/checkMysql.sh' /etc/crontab)
if [ -z "$config_info" ]; then #-z test if its empty string
echo "*/2 *     * * *   root  sh /usr/local/lib/rtmpd_control/checkMysql.sh  >> /var/tmp/rtmpdservice.log" >> /etc/crontab
echo "add ceontab to check mysql every 2 minutes"
fi  
