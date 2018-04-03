if [ "$1" = "remove" ]; then
  sudo rm /forcefsck
  sudo sed -i -e '/forcefsck/d'  /etc/rc.local 
  sudo sed -i -e 's|^FSCKFIX=yes|#FSCKFIX=no|' /etc/default/rcS
  exit
else
  echo "usage: sh forcefsck.sh remove"
fi
touch /forcefsck
sudo sed -i -e 's|^#FSCKFIX=no|FSCKFIX=yes|' /etc/default/rcS

sudo sed -i -e '/^exit 0/i\touch /forcefsck' /etc/rc.local

echo "reboot to FORCE auto disk check!!"