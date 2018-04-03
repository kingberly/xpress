sudo rm -rf /var/lib/apt/lists/*
apt-get update
apt-get -y --force-yes install python-software-properties
#add-apt-repository ppa:semiosis/ubuntu-glusterfs-3.5
add-apt-repository ppa:gluster/glusterfs-3.5
apt-get update
apt-get -y --force-yes install glusterfs-client
glusterfs --version
modprobe fuse
dmesg | grep -i fuse
#reboot