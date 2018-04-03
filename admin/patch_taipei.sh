if [ "$1" = "remove" ]; then
    config_info=$(grep 'connlog_tpe.php' /etc/crontab)
    if [ ! -z "$config_info" ]; then
        sed -i -e '/tunnel_connlog_tpe.php/d'  /etc/crontab 
        sed -i -e '/rtmpd_connlog_tpe.php/d'  /etc/crontab
        echo "cleanup t04 crontab service"
    fi
    exit  
else
  echo "to remove usage: sudo sh patch_taipei.sh remove"
fi
#remove old taipei folder
rm -rf taipei/
  if [ -f "rpic/turtorial_content.php" ]; then
    cp rpic/turtorial_content.php  /var/www/qlync_admin/html/faq/
    cp rpic/faq.php /var/www/qlync_admin/html/faq/
    echo "update T04 faq/turtorial php"
  fi
  
  if [ ! -d "/var/www/qlync_admin/plugin/taipei" ]; then
    mkdir /var/www/qlync_admin/plugin/taipei
  fi
    chmod 777 /var/www/qlync_admin/plugin/taipei/
  cp -avr rpic/* /var/www/qlync_admin/plugin/taipei
  echo "install T04 share user mgmt plugin"   
  
  if [ ! -d "/var/www/qlync_admin/html/faq/picture/" ]; then 
    mkdir /var/www/qlync_admin/html/faq/picture/
  fi
  cd rpic
  rm *.zip
  wget --timeout=10 --tries=1 ftp://iveda:2wsxCFT6@ftp.tdi-megasys.com/TW/APP/RPIC/pdf.zip
  wget --timeout=10 --tries=1 ftp://iveda:2wsxCFT6@ftp.tdi-megasys.com/TW/APP/RPIC/picture.zip
  cd ..
  if [ -f "rpic/picture.zip" ]; then
    rm /var/www/qlync_admin/html/faq/picture/*
    apt-get -y --force-yes install unzip
    cd rpic
    unzip -o picture.zip #force ovwerwrite
    cd ..
    #if [ ! -z "$(ls -A rpic/picture/)" ]; then
    mv rpic/picture/* /var/www/qlync_admin/html/faq/picture/
    echo "upload T04 faq pictures"
  fi
  #remove old files
  if [ -f "/var/www/qlync_admin/plugin/taipei/pdf.zip" ]; then
    rm /var/www/qlync_admin/plugin/taipei/pdf.zip #extra zip file deleted
  fi
 
  if [ -f "rpic/pdf.zip" ]; then
    rm /var/www/qlync_admin/html/faq/*.pdf
    cd rpic
    unzip -O BIG5 pdf.zip
    cd ..
    mv rpic/*.pdf /var/www/qlync_admin/html/faq/
    echo "update pdf files"
  fi

config_info=$(grep 'tunnel_connlog_tpe.php' /etc/crontab)
if [ -z "$config_info" ]; then #-z test if its empty string
sed -i -e "\$a*/30 * * * * root /usr/bin/php5  \x22/var/www/qlync_admin/plugin/taipei/tunnel_connlog_tpe.php\x22" /etc/crontab
echo "add ceontab to log tunnel connection"
fi
config_info=$(grep 'rtmpd_connlog_tpe.php' /etc/crontab)
if [ -z "$config_info" ]; then #-z test if its empty string
sed -i -e "\$a*/15 * * * * root /usr/bin/php5  \x22/var/www/qlync_admin/plugin/taipei/rtmpd_connlog_tpe.php\x22" /etc/crontab
echo "add ceontab to log rtmp connection"
fi
"  