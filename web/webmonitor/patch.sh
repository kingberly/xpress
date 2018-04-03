if [ ! -d "/var/www/SAT-CLOUDNVR/stats" ]; then
  mkdir /var/www/SAT-CLOUDNVR/stats
fi
cp status.html /var/www/SAT-CLOUDNVR/stats 
apt-get -y --force-yes install rrdtool
if [ ! -f "/etc/lighttpd/conf-enabled/10-rrdtool.conf" ]; then
    lighty-enable-mod rrdtool
fi
echo "installed rrdtool."
touch /var/www/lighttpd.rrd
chown www-data:www-data /var/www/lighttpd.rrd
/etc/init.d/lighttpd force-reload
echo "updated lighttpd.rrd."
if [ ! -d "/usr/igs/scripts/" ]; then
  mkdir /usr/igs/
  mkdir /usr/igs/scripts/
fi 
cp updateRRDs.sh /usr/igs/scripts/
echo "installed updateRRDs.sh"
chmod +x /usr/igs/scripts/updateRRDs.sh
config_info=$(grep 'updateRRDs.sh' /etc/crontab)
if [ -z "$config_info" ]; then
  sed -i -e '$a\*/5 * * * * root /usr/igs/scripts/updateRRDs.sh' /etc/crontab
fi
sleep 5
sudo -S -p sh /usr/igs/scripts/updateRRDs.sh
echo "if showing ERROR manually execute: sudo -S -p sh /usr/igs/scripts/updateRRDs.sh" 