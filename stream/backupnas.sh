if [ -z "$1" ]; then
  echo "usage:sudo sh backupnas.sh local\nsudo sh backupnas.sh movenas\n"
  exit
elif [ "$1" = "movenas" ]; then
echo "mount old nas to /var/evostreamms/nas1/, new NAS to /var/evostreamms/media/"
config_info=$(grep '/var/evostreamms/nas1' /proc/mounts)
if [ -z "$config_info" ]; then
  echo "old nas is not mounted, Aborted!"
  mkdir /var/evostreamms/nas1/
  chown -R evostreamd /var/evostreamms/nas1
  exit
else
  nas1=$(grep '/var/evostreamms/nas1' /proc/mounts | awk '{print $1}' )
  echo "nas1 mounted @ $nas1" 
  config_info2=$(grep '/var/evostreamms/media' /proc/mounts)
  if [ -z "$config_info2" ]; then
    echo "new nas is not mounted, Aborted!"
    exit
  else
    newnas=$(grep '/var/evostreamms/media' /proc/mounts | awk '{print $1}' )
    echo "media mounted @ $newnas"
    sudo -u evostreamd cp -avr /var/evostreamms/nas1/* /var/evostreamms/media/
    echo "copy nas1 done. Please manually clean up old NAS."
    #rm -rf /var/evostreamms/nas1/
  fi
  
fi
elif [ "$1" = "local" ]; then

umount /var/evostreamms/media/ #if mounted
if [ -d "/var/evostreamms/media/" ]; then
mv /var/evostreamms/media/ /var/evostreamms/medialocal/
echo "move files to medialocal folder."
mkdir -p /var/evostreamms/media/
chown -R evostreamd /var/evostreamms/media
echo "make media folder"
fi
#mount point
config_info=$(grep '/var/evostreamms/media' /etc/rc.local)
if [ ! -z "$config_info" ]; then
  eval $config_info
  echo "mount done by rc.local $config_info"
fi
config_info=$(grep '/var/evostreamms/media' /etc/fstab)
if [ ! -z "$config_info" ]; then
  mount -a
  echo "mount fstab done"
fi
chown evostreamd /var/evostreamms/media
echo "change share media folder owner to evostreamd"
if [ -d "/var/evostreamms/medialocal/" ]; then
cp -avr /var/evostreamms/medialocal/* /var/evostreamms/media/
echo "copy medialocal folder done."
rm -rf /var/evostreamms/medialocal/
echo "remove medialocal folder done."
fi
fi

#chown -R evostreamd /var/evostreamms/media