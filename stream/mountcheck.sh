
#capital letter only
if [ -z "$1" ]; then
  oem_id=""
else
  oem_id=$1         
fi
oem_id=`echo $oem_id | tr [:lower:] [:upper:]`
mount_point=""                #used for glusterfs mount type
mount_point_fstab=""          #fstab mount
myinterval10=0
restartAfterReboot=0
#get home directory
SCRIPT_ROOT=`dirname "$0"`

if [ "${oem_id}" = "X02" ]; then
  #mount_point="glusterfs 192.168.1.46:\/vitotest"
  mount_point="glusterfs 192.168.1.46:\/xpress1-vol"
  sh $SCRIPT_ROOT/uid.sh 107
elif [ "${oem_id}" = "X01" ]; then
  #mount_point="glusterfs 192.168.4.1:\/xpress2-vol"
  mount_point="glusterfs 192.168.1.31:\/express-vol"    
  sh $SCRIPT_ROOT/uid.sh 999
elif [ "${oem_id}" = "T04" ]; then
  mount_point="glusterfs 192.168.1.33:\/taipei-express-vol"
  restartAfterReboot=1
  sh $SCRIPT_ROOT/uid.sh 107 
elif [ "${oem_id}" = "T05" ]; then
  mount_point="glusterfs 192.168.1.31:\/express-vol"
  sh $SCRIPT_ROOT/uid.sh 999
elif [ "${oem_id}" = "K01" ]; then
  mount_point="glusterfs 192.168.2.31:\/express-vol"
  sh $SCRIPT_ROOT/uid.sh 999
elif [ "${oem_id}" = "Z02" ]; then
  mount_point_fstab="10.0.3.100:/vol/vol_xpress /var/evostreamms/media nfs defaults 0 0"
  sh $SCRIPT_ROOT/uid.sh 109
elif [ "${oem_id}" = "T03" ]; then
  mount_point_fstab="10.0.3.100:/vol/vol_xpress /var/evostreamms/media nfs defaults 0 0" 
  myinterval10=1
  sh $SCRIPT_ROOT/uid.sh 109
elif [ "${oem_id}" = "P04" ]; then
  mount_point_fstab="192.168.0.11:/nasvol /var/evostreamms/media nfs defaults,_netdev,mountproto=tcp 0 0"  
  sh $SCRIPT_ROOT/uid.sh 999
elif [ "${oem_id}" = "V03" ]; then
  #mount_point="glusterfs 192.168.1.31:\/xpress-vol"
  #mount -t glusterfs 10.254.68.10:/VNPT_Camera
  mount_point="glusterfs 10.254.68.10:\/VNPT_Camera" 
  sh $SCRIPT_ROOT/uid.sh 999 
elif [ "${oem_id}" = "V03HP" ]; then
  mount_point="glusterfs 192.168.4.50:\/vnpt-express-vol"
  sh $SCRIPT_ROOT/uid.sh 999 
elif [ "${oem_id}" = "V03DA" ]; then
  mount_point="glusterfs 192.168.2.50:\/vnpt-express-vol"
  sh $SCRIPT_ROOT/uid.sh 999 
elif [ "${oem_id}" = "V03DON" ]; then
  mount_point="glusterfs 192.168.3.50:\/vnpt-express-vol"
  sh $SCRIPT_ROOT/uid.sh 999
elif [ "${oem_id}" = "V04" ]; then
  mount_point="glusterfs 192.168.2.50:\/SentirVietnam-xpress-vol"
  sh $SCRIPT_ROOT/uid.sh 999  
elif [ "${oem_id}" = "J01" ]; then
  #mount_point_fstab="192.168.100.138:/data-vol /var/evostreamms/media glusterfs defaults,_netdev 0 0"
  mount_point="glusterfs 10.0.0.10:\/data-vol" 
  myinterval10=1
  sh $SCRIPT_ROOT/uid.sh 999
elif [ "${oem_id}" = "local" ]; then
  if [ ! -d "/var/evostreamms/media" ]; then
    mkdir /var/evostreamms/
    mkdir /var/evostreamms/media
  fi
  echo "sudo mount /dev/sdb1 /var/evostreamms/media"
  if [ ! -d "/media/videos" ]; then
    mkdir /media/videos
  fi
  echo "sudo mount /dev/sdb1 /media/videos"
  #sed -i -e '/^exit 0/i\mount \/dev\/sdb1 \/var\/evostreamms\/media' /etc/rc.local
  #sed -i -e '/^exit 0/i\mount \/dev\/sdb1 \/media\/videos' /etc/rc.local
  exit
elif [ "${oem_id}" = "remove" ]; then
  sed -i -e '/glusterfs /d'  /etc/rc.local
  sed -i -e '/\/var\/evostreamms\/media /d' /etc/fstab
  echo "remove mount point from rc.local and fstab"
  exit
else
  echo "No correct oem_id set. please type: sudo sh mountcheck.sh X0X\nex:X01 X02 T03 T04 Z02 P04 V03 V03hp V03da V03don V04 J01 remove local"
  exit
fi
#########install glusterfs
if [ "$mount_point" != "" ]; then
  config_info=$(dpkg -l  | grep 'glusterfs')
  if [ -z "$config_info" ]; then #-z test if its empty string
    #manual install only, required online Enter
    sh $SCRIPT_ROOT/glusterfs.sh
  else #vnpt stream vm bug for 3.5
    config_info=$(dpkg -l  | grep 'glusterfs-client' | awk '{print $3}' | head -c 3)
    if [ "$config_info" = "3.4" ]; then #-z test if its empty string
        sh $SCRIPT_ROOT/glusterfs.sh
    fi
  fi
  #install fail, exit with recommandation
  config_info=$(dpkg -l  | grep 'glusterfs')
  if [ -z "$config_info" ]; then #-z test if its empty string
    echo "install glusterfs fail! try: sudo apt-get -f install\nthen run mountcheck.sh again."
    sudo apt-get -y --force-yes -f install
    config_info=$(dpkg -l  | grep 'glusterfs')
    if [ -z "$config_info" ]; then
      echo "force install fail fail again!!\n"
      exit
    fi
    sh $SCRIPT_ROOT/glusterfs.sh
  fi

elif [ "$mount_point_fstab" != "" ]; then
  if [ "${oem_id}" = "P04" ]; then
    config_info=$(dpkg -l  | grep 'nfs-common')
    if [ -z "$config_info" ]; then #-z test if its empty string
      sudo apt-get -y --force-yes install nfs-common
    fi
  fi
fi
#########check mount process if exist to umount
config_info=$(grep '/var/evostreamms/media' /proc/mounts)
if [ ! -z "$config_info" ]; then
  echo "umount shared nas"
  umount /var/evostreamms/media
fi
######check mount point folder
if [ ! -d "/var/evostreamms/media" ]; then
  echo "/var/evostreamms/media does not exist for share storage\n"
  mkdir /var/evostreamms/
  mkdir /var/evostreamms/media
fi
#change folder ownership before mount
sudo chown -R evostreamd /var/evostreamms/media
sudo chown -R evostreamd /var/evostreamms/temp
#find /var/evostreamms/media -maxdepth 2 -iname 'thumbnail.jpg' -exec chown evostreamd -- {} . \;
echo "change mount parent folder ownership"
#####add mount point to rc.local or fstab
config_info=$(grep '/var/evostreamms/media' /etc/rc.local)
config_info1=$(grep '/var/evostreamms/media' /etc/fstab)
if [ -z "$config_info" -a -z "$config_info1" ]; then #-z test if its empty string
  echo "/var/evostreamms/media does not mount to share storage\n"
  if [ "$mount_point" != "" ]; then
      sed -i -e '/^exit 0/i\\/bin\/mount -t '"$mount_point"' \/var\/evostreamms\/media' /etc/rc.local
        config_info2=$(grep '/var/evostreamms/media' /etc/rc.local)
        eval $config_info2
        echo "mount rc.local $config_info2"
        sudo chown evostreamd /var/evostreamms/media
        sudo chown evostreamd /var/evostreamms/temp
        echo "update folder user permission"
  elif [ "$mount_point_fstab" != "" ]; then
      sed -i -e '$a\'"$mount_point_fstab"'' /etc/fstab
      echo "please reboot to mount fstab"
  fi
fi
#####replace mount point to rc.local if differ
if [ "$mount_point" != "" ]; then
config_info=$(grep '/var/evostreamms/media' /etc/rc.local)
check_mount_point=$(echo "/bin/mount -t $mount_point /var/evostreamms/media" | sed 's|\\||g')
if [ ! "$config_info" = "$check_mount_point" ]; then
  sed -i -e '/glusterfs /d'  /etc/rc.local
  sed -i -e '/^exit 0/i\\/bin\/mount -t '"$mount_point"' \/var\/evostreamms\/media' /etc/rc.local
  echo "replace mount point with new one"
fi
fi
########check mount point process
config_info=$(grep '/var/evostreamms/media' /proc/mounts)
if [ -z "$config_info" ]; then
  if [ "$mount_point" != "" ]; then
    echo "mount shared nas"
    config_info2=$(grep '/var/evostreamms/media' /etc/rc.local)
    eval $config_info2  
  elif [ "$mount_point_fstab" != "" ]; then
    echo "mount shared fstab nas"
    mount /var/evostreamms/media
  fi
fi
#config_info=$(grep '/var/evostreamms/media' /proc/mounts)
#if [ ! -z "$config_info" ]; then
  #while true; do
  #    read -p "Do you wish to change mount folder ownership now [y/n]?" yn
  #    case $yn in
  #        [Yy]* ) chown -R evostreamd /var/evostreamms/media; break;;
  #        [Nn]* ) break;;
  #        * ) echo "Please answer y or n.";;
  #    esac
  #done
  echo "change mount folder ownership: sudo chown -R evostreamd /var/evostreamms/media"  
#fi
#sudo chown evostreamd /var/evostreamms/media

######install restartstr process
if [ ${restartAfterReboot} -eq 1 ]; then
  sed -i -e '/restartstr.sh/d'  /etc/rc.local
  currDIR=${PWD}
  #sed -i -e '/^exit 0/i\sh /home/ivedasuper/stream/restartstr.sh' /etc/rc.local
  sed -i -e '/^exit 0/i\sh '"$currDIR"'/restartstr.sh' /etc/rc.local

else
  #uninstall:
  sed -i -e '/restartstr.sh/d'  /etc/rc.local
fi
######set interval to 10 and ask for restart
if [ ${myinterval10} -eq 1 ]; then
  sh $SCRIPT_ROOT/changeInterval.sh 10
  echo "set recording interval as 10minutes\n"
  while true; do
      read -p "Do you wish to restart service now [y/n]?" yn
      case $yn in
          [Yy]* ) sh restartstr.sh; break;;
          [Nn]* ) exit;;
          * ) echo "Please answer y or n.";;
      esac
  done  
fi

installstr=$(ps ax | grep [s]tream_server_control)
if [ -z "$installstr" ]; then
  sudo sh $SCRIPT_ROOT/restartstr.sh
fi  