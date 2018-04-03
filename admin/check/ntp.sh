#config=$(dpkg -l  | grep 'ntp')#ntpdate will be included
#if [ -z "$config" ]; then
if [ ! -f "/etc/init.d/ntp" ]; then
sudo apt-get install  --force-yes -y ntp
sudo /etc/init.d/ntp stop
sudo ntpdate pool.ntp.org
sudo /etc/init.d/ntp start
fi
/etc/init.d/ntp status
