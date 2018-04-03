#rm -rf /var/lib/apt/lists/*
rm /etc/apt/sources.list.d/gluster-glusterfs-3*
#default without autoupdate install 3.4.2
config=$(dpkg -l | grep glusterfs-client)
mInstallGluster()
{
    wget --timeout=10 --tries=1 ftp://IvedaIGS:IvedaIGS85168@118.163.90.31/IvedaIGS/TW/APP/glusterfs-3.6.0.tar.gz
  modprobe fuse
  config=$(dmesg | grep -i fuse)
  if [ -z "$config" ]; then
    echo "Linux Kernal Fuse is not loaded!\n"
    exit
  fi
    tar zxvf glusterfs-3.6.0.tar.gz
    chmod a+wrx -R  glusterfs-3.6.0
    cd glusterfs-3.6.0
    apt-get -y --force-yes install flex;apt-get -y --force-yes install  bison;apt-get -y --force-yes install libssl-dev;apt-get -y --force-yes install libxml2-dev
    #./configure --prefix=/home/ivedasuper/glusterfs-3.6.0
    ./configure
    make
    make install
    apt-get -y --force-yes install glusterfs-client
    cd ..
    rm -rf glusterfs-3.6.0
    rm glusterfs-3.6.0.tar.gz    
}
if [ -z "$config" ]; then
if [ -z "$1" ]; then
  apt-get -y --force-yes install glusterfs-client
elif [ "$1" = "V03" -o "$1" = "NEW" ]; then  #VNPT
  #for add-apt-repository
  apt-get update;apt-get -y --force-yes install python-software-properties
  #add-apt-repository universe;add-apt-repository restricted;add-apt-repository multiverse
  #add-apt-repository -r ppa:gluster/glusterfs-3.5;
  #add-apt-repository ppa:gluster/glusterfs-3.8
  add-apt-repository ppa:gluster/glusterfs-3.8;apt-get install glusterfs-client -y
fi
else
#########installed##############
glusterV=$(glusterfs --version | grep "glusterfs " | awk '{ print $2 }' | head -c 3)
echo "current glusterfs Version is $glusterV\n"
#if [ "$glusterV" = "3.4" ]; then #do nothing
#elif [ "$glusterV" = "3.5" ]; then #do nothing
#elif [ "$glusterV" = "3.6" ]; then #do nothing
if [ "$glusterV" = "3.8" ]; then
  if [ "$1" = "V03" ]; then  #VNPT
    echo "VNPT gluster version require 3.8\n"
    exit
  else
    #config_info2=$(grep '/var/evostreamms/media' /etc/rc.local)
    umount /var/evostreamms/media
    umount /media/videos
    add-apt-repository -r ppa:gluster/glusterfs-$glusterV
  #apt-get install ppa-purge
  #sudo ppa-purge gluster/glusterfs-3.8
  dpkg -r glusterfs-client
  sudo apt-get remove --auto-remove glusterfs-client
  apt-get -y --force-yes install glusterfs-client
  glusterV=$(glusterfs --version | grep "glusterfs " | awk '{ print $2 }' | head -c 3)
  echo "current glusterfs Version is $glusterV\n"
  fi
fi

fi

echo "usage: sudo sh glusterfs.sh <(blank)/V03/NEW>"