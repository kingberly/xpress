echo "fill 0 for empty parameter, USAGE: sh pldt.sh (create user ivedasuper) (hostname) (3security upgrade) (4uuid) (5mount web disk) (6mount stream ext-disk) (7mount db ext-disk) (web Mail)\nex:sh pldt.sh 1 web-111 1 1 0 0 0" 
#root function add user
if [ ! -z "$1" -a ! "$1" = "0" ]; then
  sh addiveda.sh
fi
#change hostname
if [ ! -z "$2" -a ! "$2" = "0" ]; then
  sh changehost.sh
fi

#security upgrade
if [ ! -z "$3" -a ! "$3" = "0" ]; then
  uname -r;apt-get update;
  unattended-upgrades
  #dpkg -l | grep wpasupplicant
  dpkg -l | grep patch
  echo "patch required to be 2.7.1-4ubuntu2.3 and later"
  #dpkg -l | grep libpython
  #dpkg -l | grep unattended-upgrades
  #dpkg -l | grep openssh-server
  #uname -r
  #sudo reboot -h now
fi
if [ ! -z "$4" -a ! "$4" = "0" ]; then
  sh changeuuid.sh
fi
if [ ! -z "$5" -a ! "$5" = "0" ]; then
mkdir /media/videos
apt-get update ; apt-get -y --force-yes install nfs-common
echo "192.168.0.11:/nasvol /media/videos nfs defaults,_netdev,mountproto=tcp 0 0" >> /etc/fstab
fi
if [ ! -z "$6" -a ! "$6" = "0" ]; then
	mkfs -t ext4 /dev/sdb ; mkdir /var/evostreamms/;mkdir /var/evostreamms/temp/
  config=$(echo `blkid | grep sdb | awk -F  "\"" '{print $2}'`)
  config=$(echo "UUID=$config /var/evostreamms/temp/ ext4 defaults 0 0")
	echo $config >> /etc/fstab
fi
if [ ! -z "$7" -a ! "$7" = "0" ]; then
	mkfs -t ext4 /dev/sdb ; mkdir /var/lib/mysql/
  config=$(echo `blkid | grep sdb | awk -F  "\"" '{print $2}'`)
  config=$(echo "UUID=$config /var/lib/mysql ext4 defaults 0 0")
	echo $config >> /etc/fstab
fi
if [ ! -z "$8" -a ! "$8" = "0" ]; then
  config=$(cat /etc/network/interfaces| grep eth1)
  if [ -z "$config" ]; then
    sed -i -e '$a\auto eth1' /etc/network/interfaces
    sed -i -e '$a\iface eth1 inet dhcp' /etc/network/interfaces
    echo "Add eth1 to /etc/network/interfaces\n" 
  fi
  config=$(cat /etc/network/interfaces| grep 172.16.31.193)
  if [ -z "$config" ]; then
    sed -i -e '$a\up route add -net 10.0.0.0 netmask 255.0.0.0 gw 172.16.31.193 eth1' /etc/network/interfaces
    echo "Add gw 172.16.31.193 to /etc/network/interfaces"
    up route add -net 10.0.0.0 netmask 255.0.0.0 gw 172.16.31.193 eth1
  fi
  #sudo ifdown eth0;ifup eth0
  #sudo ifdown eth1;ifup eth1 #??
  while true; do
      read -p "Do you wish to restart eth1 [y/n] now?" yn
      case $yn in
          [Yy]* ) ifdown eth1;ifup eth1;exit;;
          [Nn]* ) exit;;
          * ) echo "Please answer y or n.";;
      esac
  done
  lspci | grep -i net
  ifconfig
  config_info=$(dpkg -l  | grep 'traceroute')
  if [ -z "$config_info" ]; then #-z test if its empty string
    sudo apt-get -y --force-yes install traceroute
  fi
  traceroute 10.31.20.80
fi
  while true; do
      read -p "Do you wish to shutdown [y/n] or reboot [r] now?" yn
      case $yn in
          [Yy]* ) sync;sync;sync;shutdown now;exit;;
          [Rr]* ) sync;sync;sync;reboot;exit;;     #reboot will fail
          [Nn]* ) exit;;
          * ) echo "Please answer y or n.";;
      esac
  done