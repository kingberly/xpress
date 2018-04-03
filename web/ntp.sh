apt-get install -y --force-yes ntp 
/etc/init.d/ntp stop
ntpdate pool.ntp.org
/etc/init.d/ntp start