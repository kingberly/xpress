SCRIPT_ROOT=`dirname "$0"`
CURRENT_DIR="$PWD"
if [ "$1" = "remove" ]; then
    /etc/init.d/stream_server_control stop
    #/etc/init.d/nginx stop
    service nginx stop
    rm -rf /usr/local/lib/stream_server_control
    rm /etc/stream_server_control.conf
    rm /etc/init.d/stream_server_control
    rm /etc/nginx/stream_server_auth.conf
    echo "remove installed service."
    config_info=$(grep '/var/evostreamms/media' /proc/mounts)
    if [ ! -z "$config_info" ]; then
        umount /var/evostreamms/media
        echo "unmount shared nas"
        sed -i -e '/restartstr.sh/d'  /etc/rc.local 
        sed -i -e '/evostreamms/d'  /etc/rc.local
        sed -i -e '/evostreamms/d'  /etc/fstab
        echo "cleanup bootup mount process"
    fi
    sed -i -e '/evostreamms/d'  /etc/crontab
    if [ ! -z "$(grep 'checkMysql.sh' /etc/crontab)" ]; then
      sed -i -e '/checkMysql.sh/d'  /etc/crontab
    fi
    if [ ! -z "$(grep 'checkMysql.sh' /etc/crontab)" ]; then
      sed -i -e '/checkMysql.sh/d'  /etc/crontab
    fi
    exit  
else
  echo "to remove server usage: sudo sh $0 remove"
fi
config=$(cat /etc/lsb-release  | grep DISTRIB_RELEASE | tail -c 6)

if [ ! -d "/usr/local/lib/stream_server_control/" ]; then
  echo "stream server not installed"
#sudo apt-get update &&  sudo apt-get upgrade && sudo apt-get install build-essential
  if [ -z "$(dpkg --get-selections | grep vlc)" ]; then   #blank server
  if [ "$config" = "14.04" ]; then
    sudo sh preinstall.sh
  fi
  fi
fi

if [ "$config" = "14.04" ]; then

installstr=$(ps ax | grep [s]tream_server_control)

if [ -d "$CURRENT_DIR/../isat_stream" ]; then
#filename=$(ls -Art ../*isat_stream*.tar.gz | tail -n 1)
filename=$(grep _VERSION ../isat_stream/package_version.py)
sed -i -e 's|sudo apt-get update|#sudo apt-get update|' ../isat_stream/install.sh
elif [ -d "$CURRENT_DIR/isat_stream" ]; then
#filename=$(ls -Art *isat_stream*.tar.gz | tail -n 1)
filename=$(grep _VERSION isat_stream/package_version.py)
sed -i -e 's|sudo apt-get update|#sudo apt-get update|' isat_stream/install.sh
fi
if [ -z "$filename" ]; then
  echo "Cannot find stream installation package under $CURRENT_DIR"
  exit
else  #0.9.0044
  #filever=$(echo $filename | rev | cut -d'-' -f 1 | rev | head -c 8)
  #filever=$(echo "'${filever}'")
  filever=$(echo $filename | rev | cut -d'=' -f 1 | rev | tail -c 11)
fi


filever2=$(grep PACKAGE_VERSION /usr/local/lib/stream_server_control/package_version.py | tail -c 11)
#install fail
if [ ! -z "$1" -o -z "$installstr"  -o ! "$filever" = "$filever2" ]; then
    config_info=$(grep '/var/evostreamms/media' /proc/mounts)
    if [ ! -z "$config_info" ]; then 
      umount /var/evostreamms/media/
      config_info=$(grep '/var/evostreamms/media' /proc/mounts)
      if [ ! -z "$config_info" ]; then
        config_info1=$(fuser -m /var/evostreamms/media/)
        echo "fail to umount share disk! try kill $config_info1"
        exit
      fi
    fi
    sudo apt-get -f -y --force-yes autoremove
    #remove apt-get update before install
    #sed -i -e 's|^sudo apt-get update|#sudo apt-get update|' ../isat_stream/install.sh
    #sed -i -e 's|sudo apt-mark hold vlc-nox|#sudo apt-mark hold vlc-nox|' ../isat_stream/install.sh
    pkg=$(dpkg --get-selections | grep hold | awk '{print $1}')
    if [ ! -z "$pkg" ]; then
    sudo apt-mark unhold $pkg
    fi 
    sudo apt-get -f install

    if [ -d "$CURRENT_DIR/../isat_stream" ]; then #when run under stream/ folder
      cd ../isat_stream
      sudo sh install.sh
      cd ../stream
    elif [ -d "$CURRENT_DIR/isat_stream" ]; then #when run under stream/ folder
      cd isat_stream
      sudo sh install.sh
      cd  ..
    fi
fi
filever2=$(grep PACKAGE_VERSION /usr/local/lib/stream_server_control/package_version.py | tail -c 11)
if [ ! "$filever" = "$filever2" ]; then
  echo "stream server install fail, skip patch"
  sh $SCRIPT_ROOT/restartstr.sh
  config=$(ps ax | grep '[v]lc')
  if [ ! -z "$config" ]; then
    sh $SCRIPT_ROOT/patch_run.sh
  fi 
else
  sh $SCRIPT_ROOT/patch_run.sh
fi

else #12.04
  sh $SCRIPT_ROOT/patch_run.sh
fi #check 14.04


config_info=$(grep '/var/evostreamms/media' /proc/mounts)
if [ -z "$config_info" ]; then 
    #mount back if it is in rc.local
    config_info=$(grep '/var/evostreamms/media' /etc/rc.local)
    if [ ! -z "$config_info" ]; then
      eval $config_info
      echo "mount done by rc.local $config_info"
    else #fstab
      config_info=$(grep '/var/evostreamms/media' /etc/fstab)
      if [ ! -z "$config_info" ]; then
        mount -a
        echo "mount fstab done"
      fi
    fi
fi
