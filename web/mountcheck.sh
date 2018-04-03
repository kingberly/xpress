targetFolder="/media/videos"  #/var/evostreamms/media

checkFolderExist(){
if [ ! -d "$1" ]; then
  echo "$1 does not exist for share storage\n"
  mkdir /media
  mkdir $1
fi
}

if [ -z "$1" ]; then
  oemid=$(sed -n '/OEM_ID=/p' ../iSVW_install_scripts/install.conf | tail -c 6)
  #capital letter only
  #Web server only
  oem_id=$oemid
else
  oem_id="\"$1\""         
fi
mount_point=""                #used for glusterfs mount type
mount_point_fstab=""          #fstab mount
#get home directory
SCRIPT_ROOT=`dirname "$0"`

if [ "${oem_id}" = "\"X02\"" ]; then
  #mount_point="glusterfs 192.168.1.46:\/vitotest"
  mount_point="glusterfs 192.168.1.46:\/xpress1-vol"
elif [ "${oem_id}" = "\"X01\"" ]; then
  #mount_point="glusterfs 192.168.4.1:\/xpress2-vol"
  mount_point="glusterfs 192.168.1.31:\/express-vol"
elif [ "${oem_id}" = "\"T04\"" ]; then
  mount_point="glusterfs 192.168.1.33:\/taipei-express-vol"
elif [ "${oem_id}" = "\"T05\"" ]; then
  mount_point="glusterfs 192.168.1.31:\/express-vol"
elif [ "${oem_id}" = "\"K01\"" ]; then
  mount_point="glusterfs 192.168.2.31:\/express-vol"
elif [ "${oem_id}" = "\"Z02\"" ]; then
  mount_point_fstab="10.0.3.100:/vol/vol_xpress $targetFolder nfs defaults 0 0"
elif [ "${oem_id}" = "\"T03\"" ]; then
  mount_point_fstab="10.0.3.100:/vol/vol_xpress $targetFolder nfs defaults 0 0"
elif [ "${oem_id}" = "\"P04\"" ]; then
  mount_point_fstab="192.168.0.11:/nasvol $targetFolder nfs defaults,_netdev,mountproto=tcp 0 0"
elif [ "${oem_id}" = "\"V03\"" ]; then
  #mount_point="glusterfs 192.168.1.31:\/xpress-vol"
  mount_point="glusterfs 10.254.68.10:\/VNPT_Camera"
elif [ "${oem_id}" = "\"V03hp\"" ]; then
  mount_point="glusterfs 192.168.4.50:\/vnpt-express-vol"
elif [ "${oem_id}" = "\"V03da\"" ]; then
  mount_point="glusterfs 192.168.2.50:\/vnpt-express-vol"
elif [ "${oem_id}" = "\"V03don\"" ]; then
  mount_point="glusterfs 192.168.3.50:\/vnpt-express-vol"
elif [ "${oem_id}" = "\"V04\"" ]; then
  mount_point="glusterfs 192.168.2.50:\/SentirVietnam-xpress-vol"
elif [ "${oem_id}" = "\"J01\"" ]; then
  #mount_point_fstab="192.168.100.138:/data-vol /var/evostreamms/media glusterfs defaults,_netdev 0 0"
  mount_point="glusterfs 10.0.0.10:\/data-vol"
elif [ "${oem_id}" = "\"local\"" ]; then
  checkFolderExist $targetFolder
  config_info=$(grep $targetFolder /etc/rc.local)
  if [ -z "$config_info" ]; then
  sed -i -e '/^exit 0/i\\/bin\/mount -o bind \/var\/evostreamms\/media \/media\/videos' /etc/rc.local
  echo "add $targetFolder to rc.local"
  fi
  config_info=$(grep $targetFolder /proc/mounts)
  if [ -z "$config_info" ]; then
  mount -o bind /var/evostreamms/media /media/videos
  echo "mount local evostreamms/media to $targetFolder"
  exit
  fi
elif [ "${oem_id}" = "\"localsdb1\"" ]; then  ###???check

    checkFolderExist $targetFolder
    echo "sudo mount /dev/sdb1 $targetFolder"
  exit
elif [ "${oem_id}" = "\"remove\"" ]; then
  sed -i -e '\/media\/videos/d'  /etc/rc.local
  sed -i -e '\/media\/videos /d' /etc/fstab
  echo "remove mount point from rc.local and fstab"
  exit
else
  echo "No correct oem_id set. please type: sudo sh mountcheck.sh X0X\nex:X01 X02 T03 T04 Z02 P04 V03 V03hp V03da V03don V04 J01 remove local localsda1"
  exit
fi
#########install glusterfs
if [ "$mount_point" != "" ]; then
  config_info=$(dpkg -l  | grep 'glusterfs')
  if [ -z "$config_info" ]; then #-z test if its empty string
    #manual install only, required online Enter
    #if [ "${oem_id}" = "V03" ]; then    #vnpt stream vm bug
    #    sudo apt-get -y --force-yes -f install
    #fi
    sh $SCRIPT_ROOT/glusterfs.sh
  else #vnpt stream vm bug for 3.5
    config_info=$(dpkg -l  | grep 'glusterfs-client' | awk '{print $3}' | head -c 3)
    if [ "$config_info" = "3.4" ]; then #-z test if its empty string
        sh $SCRIPT_ROOT/glusterfs.sh
    fi
  fi
elif [ "$mount_point_fstab" != "" ]; then
  if [ "${oem_id}" = "\"P04\"" ]; then
    config_info=$(dpkg -l  | grep 'nfs-common')
    if [ -z "$config_info" ]; then #-z test if its empty string
      sudo apt-get -y --force-yes install nfs-common
    fi
  fi
fi
#########check mount process if exist to umount
config_info=$(grep $targetFolder /proc/mounts)
if [ ! -z "$config_info" ]; then
  echo "umount shared nas"
  #umount /var/evostreamms/media
  umount $targetFolder
fi
######check mount point folder
checkFolderExist $targetFolder 

#####replace mount point to rc.local if differ
if [ "$mount_point" != "" ]; then
    config_info=$(grep $targetFolder /etc/rc.local)
    check_mount_point=$(echo "/bin/mount -t $mount_point $targetFolder" | sed 's|\\||g')
    if [ ! "$config_info" = "$check_mount_point" ]; then
      sed -i -e '/glusterfs /d'  /etc/rc.local
      sed -i -e '/^exit 0/i\\/bin\/mount -t '"$mount_point"' \/media\/videos' /etc/rc.local

      echo "replace mount point with new one"
    fi
fi

#####add mount point to rc.local or fstab
config_info=$(grep $targetFolder /etc/rc.local)
config_info1=$(grep $targetFolder /etc/fstab)
if [ -z "$config_info" -a -z "$config_info1" ]; then #-z test if its empty string
  echo "$targetFolder does not mount to share storage\n"
  if [ "$mount_point" != "" ]; then
			sed -i -e '/^exit 0/i\\/bin\/mount -t '"$mount_point"' \/media\/videos' /etc/rc.local

  elif [ "$mount_point_fstab" != "" ]; then
      sed -i -e '$a\'"$mount_point_fstab"'' /etc/fstab
      echo "please reboot to mount fstab"
  fi
fi

########check mount point process
config_info=$(grep $targetFolder /proc/mounts)
if [ -z "$config_info" ]; then
  if [ "$mount_point" != "" ]; then
    echo "mount shared nas"
    config_info2=$(grep $targetFolder /etc/rc.local)
    eval $config_info2  
  elif [ "$mount_point_fstab" != "" ]; then
    echo "mount shared fstab nas"
    mount $targetFolder
  fi
fi


