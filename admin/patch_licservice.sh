#######temp cleanup
config_info=$(grep '/var/tmp/' /etc/crontab)
if [ -z "$config_info" ]; then #-z test if its empty string
  echo "0 1     * * 0   root  find  /var/tmp/  -type f -mtime +7 -exec rm {} +" >> /etc/crontab
  echo "set /etc/crontab to cleanup /var/tmp/ temp folder in 7 days\n"
fi
config_info=$(grep '/var/www/qlync_admin/plugin/licservice/log/' /etc/crontab)
if [ -z "$config_info" ]; then #-z test if its empty string
  echo "0 1     * * 0   root  find  /var/www/qlync_admin/plugin/licservice/log/  -type f -mtime +7 -exec rm {} +" >> /etc/crontab
  echo "set /etc/crontab to cleanup plugin/licservice/log/ temp folder in 7 days\n"
fi

#has set to routine.py
#config_info=$(grep '/var/www/qlync_admin/plugin/licservice/addLicenseNotify.php' /etc/crontab)
#if [ -z "$config_info" ]; then #-z test if its empty string
  #echo "5 5     * * 0 root /usr/bin/php5 \"/var/www/qlync_admin/plugin/licservice/addLicenseNotify.php\"" >> /etc/crontab
  #echo "set license expiration notification every sunday\n"
#fi
#############install
if [ ! -d "/var/www/qlync_admin/plugin" ]; then
  mkdir /var/www/qlync_admin/plugin
  echo "mkdir plugin"
fi
if [ ! -d "/var/www/qlync_admin/plugin/licservice" ]; then
  mv licservice /var/www/qlync_admin/plugin/
  echo "installed licservice"
else
  cp -avr licservice/* /var/www/qlync_admin/plugin/licservice
  echo "update licservice"
fi
if [ ! -d "/var/www/qlync_admin/plugin/licservice/log" ]; then
  mkdir /var/www/qlync_admin/plugin/licservice/log
  echo "mkdir log"
fi
chmod 777 /var/www/qlync_admin/plugin/licservice/log
echo "chmod 777 /var/www/qlync_admin/plugin/licservice/log"
###install new php-gd for qr code library
config_info=$(dpkg -l | grep php5-gd)
if [ -z "$config_info" ]; then
  sudo apt-get -y --force-yes install php5-gd
fi

config_info=$(grep "Plugin Tools" /var/www/qlync_admin/html/faq/turtorial_content.php)
if [ -z "$config_info" ]; then #empty insert
sed -i -e '/?>/ {
i\
if($_SESSION["ID_admin_oem"] or $_SESSION["ID_admin_qlync"]) {
i\
$turtorial[Tools][1][q]	="Plugin Tools:";
i\
$turtorial[Tools][1][a][]	="<a href='https://xpress.megasys.com.tw:8080/plugin/licservice/genQR.php'>Generate QR code</a>";}
}' /var/www/qlync_admin/html/faq/turtorial_content.php
echo "add Title and QRcode link"
else
config_info=$(grep "Generate QR code" /var/www/qlync_admin/html/faq/turtorial_content.php)
if [ -z "$config_info" ]; then #empty insert
sed -i -e '/Plugin Tools/  {
i\
$turtorial[Tools][1][a][]	="<a href='https://xpress.megasys.com.tw:8080/plugin/licservice/genQR.php'>Generate QR code</a>";
}' /var/www/qlync_admin/html/faq/turtorial_content.php
echo "add QRcode link"
fi
fi

#######replace dbtuil.php
#/var/www/qlync_admin/doc/config.php
config_info=$(grep 'mysql_ip' /var/www/qlync_admin/doc/config.php)
#bash only
#config_info1=$(sed -r 's/[^\"]*([\"][^\"]*[\"][,]?)[^\"]*/\1 /g' <<< "$config_info")
len=${#config_info}
#echo $len
COUNT=$(expr $len - 2)
#echo $COUNT
substr=$(expr substr $config_info 11 $COUNT)
#echo ${substr%?}
config_info1=${substr%?}
#echo $config_info1
sed -i -e 's/define("DB_HOST","127.0.0.1")/define("DB_HOST",'"$config_info1"')/' /var/www/qlync_admin/plugin/licservice/dbutil.php
echo "update licservice database access info in dbutil.php"