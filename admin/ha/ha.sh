config=$(dpkg -l | grep heartbeat)
if [ -z "$config" ]; then
apt-get -y --force-yes install heartbeat watchdog
fi
lanip=$(ifconfig | awk '/inet addr/{print substr($2,6)}')
lanip1=$(echo $lanip | awk '{print $1}')
echo "LocalIP is $lanip1"
config_info1=$(grep $lanip1 ha-slave.cf)
config_info=$(grep $lanip1 ha.cf)
if [ -z "$config_info1" -a -z "$config_info" ]; then
  echo "LocalIP is not correct set in ha.cf/ha-slave.cf"
  exit
fi
#set other admin IP in ha.cf 
if [ -z "$config_info" ]; then
    halanip=$(sed -n '/ucast               eth0/p'  ha.cf | awk '{print $3}')
    echo "set master admin IP $halanip."
    cp ha.cf /etc/ha.d/
else
    halanip=$(sed -n '/ucast               eth0/p'  ha-slave.cf | awk '{print $3}')
    echo "set slave admin IP $halanip."
    cp ha-slave.cf /etc/ha.d/ha.cf
fi
cp authkeys /etc/ha.d/
chmod 600 /etc/ha.d/authkeys
cp haresources /etc/ha.d/
config_info=$(grep 'net.ipv4.ip_nonlocal_bind=1' /etc/sysctl.conf)
if [ -z "$config_info" ]; then
  #echo "net.ipv4.ip_nonlocal_bind=1" >> /etc/sysctl.conf
  sed -i -e '$a\net.ipv4.ip_nonlocal_bind=1' /etc/sysctl.conf
fi
if [ ! -d "/usr/igs/scripts" ]; then 
  mkdir /usr/igs
  mkdir /usr/igs/scripts
fi
cp routine.py /usr/igs/scripts/ 

sh routine.sh

/etc/init.d/heartbeat start
#sudo update-rc.d heartbeat defaults
/etc/init.d/watchdog start
#reboot