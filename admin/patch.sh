if [ "$1" = "remove" ]; then
    sudo service apache2 stop
    rm -rf /var/www/qlync_admin
    rm /etc/apache2/ssl/*.pem
    rm /etc/apache2/ssl/*.key
    sed -i -e '/qlync_admin/d'  /etc/crontab
    sed -i -e '/updateRRDs.sh/d'  /etc/crontab
    sed -i -e '/apache2/d'  /etc/crontab
    apt-get remove apache2
    echo "remove installed service."
    exit  
else
  echo "to remove server usage: sudo sh patch.sh remove"
fi

myPrintAlert(){
  echo "\\033[31m$1\\033[0m"
}
myPrintInfo(){
  echo "\\033[92m$1\\033[0m"
}
#### define parameter, default 0
licservice_enable=0
god_pwd="qwedcxzas"
AdminTitle=""
FTPPATH2="ftp://IvedaIGS:IvedaIGS85168@118.163.90.31/IvedaIGS/TW/APP"
FTPparam="--timeout=10 --tries=1"
# get oem_id from installation, check oem id $oemid="Xnn";
oemid=$(sed -n '/OEM_ID=/p' ../isat_partner/install.conf | tail -c 6) #check config "X02"
oemstr=$(sed -n '/$oem=/p' /var/www/qlync_admin/doc/config.php | tail -c 6) # X02";
oemstrTest=$(sed -n '/$oem=/p' /var/www/qlync_admin/doc/config.php)
oemlen=${#oemstrTest} #m="";
if [ $oemlen -lt 9 ]; then
  oemstr=$(echo $oemid | tail -c 5)
  oemstr=$(echo "$oemstr;")
  myPrintAlert "ERROR!! NO PARAMETER SET in config.php!!\nUse install.conf instead."
fi
if [ "${oemstr}" = 'X02";' ]; then

  licservice_enable=1 #plugin lic_service install
  sslpem="xpress_megasys_com_tw.pem"
  sslca="xpress_megasys_com_tw.ca.pem"
  sslkey="xpress_megasys_com_tw.key"
  cp SSL/X02/* .
  myPrintAlert "set ${oemid} SSL and parameters."
  god_pwd="1qazxdr56yhN"
elif [ "${oemstr}" = 'X01";' ]; then

  sslpem="server.pem"
  sslca="ca.pem"
  sslkey="server.key"
  cp SSL/X01/* .
  myPrintAlert "set ${oemid} SSL and parameters."
elif [ "${oemstr}" = 'T04";' ]; then

  sslpem="rpic.taipei.server.pem"
  sslca="rpic.taipei.ca.pem"
  sslkey="rpic.taipei.key"
  cp SSL/T04/* .
  myPrintAlert "set ${oemid} SSL and parameters."
  god_pwd="1qazxdr56yhN"
elif [ "${oemstr}" = 'T05";' ]; then

  sslpem="rpic.tycg.gov.tw.server.pem"
  sslca="rpic.ca.pem"
  sslkey="rpic.tycg.gov.tw.key"
  cp SSL/T05/* .
  cp SSL/$sslca .
  myPrintAlert "set ${oemid} SSL and parameters."
  god_pwd="1qazxdr56yhN"
elif [ "${oemstr}" = 'T06";' ]; then

  sslpem="workeye.pem"
  sslca="godaddy.ca.pem"
  sslkey="workeye.key"
  cp SSL/T06/* .
  myPrintAlert "set ${oemid} SSL and parameters."
  god_pwd="1qazxdr56yhN"

elif [ "${oemstr}" = 'C13";' ]; then

  sslpem="engeye.chimei.com.tw.pem"
  sslca="engeye.chimei.com.tw.ca.pem"
  sslkey="engeye.chimei.com.tw.key"
  cp SSL/C13/* .
  myPrintAlert "set ${oemid} SSL and parameters."
  god_pwd="1qazxdr56yhN"
  AdminTitle="CHIMEI Cloud Admin"
elif [ "${oemstr}" = 'K01";' ]; then

  sslpem="kreac_kcg_gov_tw.pem"
  sslca="rpic.ca.pem"
  sslkey="kreac_kcg_gov_tw.key"
  cp SSL/K01/* .
  cp SSL/$sslca .
  myPrintAlert "set ${oemid} SSL and parameters."
  #god_pwd="1qazxdr56yhN"
  god_pwd="2wsxcft67ujM"
elif [ "${oemstr}" = 'Z02";' ]; then
  sslpem="zee.ivedaxpress.com.pem"
  sslca="zee.ivedaxpress.com.ca.pem"
  sslkey="zee.ivedaxpress.com.key"
  cp SSL/Z02/* .
  myPrintAlert "set ${oemid} SSL and parameters."
elif [ "${oemstr}" = 'T03";' ]; then
  sslpem="test.ivedaxpress.com.pem"
  sslca="test.ivedaxpress.com.ca.pem"
  sslkey="test.ivedaxpress.com.key"
  cp SSL/T03/* .
  myPrintAlert "set ${oemid} SSL and parameters."
elif [ "${oemstr}" = 'P04";' ]; then
  sslpem="server201708.pem"
  sslca="ca.pem"
  sslkey="server.key"
  cp SSL/P04/* .
  myPrintAlert "set ${oemid} SSL and parameters."
elif [ "${oemstr}" = 'V03";' ]; then
  sslpem="camera_vinaphone_vn.pem" 
  sslca="camera_vinaphone_vn.ca.pem"
  sslkey="camera_vinaphone_vn.key"
  cp SSL/V03/* .
  myPrintAlert "set ${oemid} SSL and parameters."
elif [ "${oemstr}" = 'V04";' ]; then

  sslpem="sentirvietnam.vn.pem" 
  sslca="sentirvietnam.vn.ca.pem"
  sslkey="sentirvietnam.vn.key"
  cp SSL/V04/* .
  myPrintAlert "set ${oemid} SSL and parameters."
elif [ "${oemstr}" = 'J01";' ]; then
  sslpem="japan.ivedaxpress.com.pem"
  sslca="japan.ivedaxpress.com.ca.pem"
  sslkey="japan.ivedaxpress.com.key"
  cp SSL/J01/* .
  myPrintAlert "set ${oemid} SSL and parameters."
else
  myPrintAlert "ERROR!! NO PARAMETER SET in config.php!!\n  isat_partner/install.conf used $oemid"
  exit
fi

#update god admin pwd
php _db_god_admin.php $god_pwd
myPrintAlert "Replaced God Admin PWD ${god_pwd} if password is not default or empty."
php _db_god_admin.php
#python package install for admin util
apt-get -y --force-yes install python-paramiko
sh check/pyupgrade.sh
#db preset
php _db_preset.php
myPrintInfo "Fill in database preset data if not existed"
if [ ! -d "/var/www/qlync_admin/plugin/" ]; then
  mkdir /var/www/qlync_admin/plugin/
  echo "make plugin dir"
fi
# install patch before all other patch files
sh patch_bug.sh
#install debug (engineer)
sudo sh patch_debug.sh
if [ "${oemid}" = '"X02"' ]; then
cp debug/sp/maintain.php /var/www/qlync_admin/plugin/debug

cp rpic/appdownload.X02.php /var/www/qlync_admin/plugin/debug/
fi
php _db_add_plugin_menu_nest_debug.php
myPrintInfo "complete plugin debug installation\n"
#install user_log (engineer) menu is added under user
#?????check why first time install has no folder
sudo sh patch_user_log.sh
php _db_add_plugin_menu_userlog.php
myPrintInfo "complete plugin user_log installation\n"
#install billing report  menu is added under Support
#?????check why first time install make dbutil empty
sudo sh patch_billing.sh
php _db_add_plugin_menu_billing.php
myPrintInfo "complete plugin billing installation\n"
#install api
sudo cp -avr api/* /var/www/qlync_admin/html/api
sudo chmod 777 /var/www/qlync_admin/html/api/util.php
#for scidupdate
chmod -R 777 /var/www/qlync_admin/html/scid
php scidupdate.php
if [ "${oemid}" = '"V04"' -o "${oemid}" = '"V03"' ]; then
  sed -i -e 's|public $DB_ENABLE = 0;|public $DB_ENABLE = 1;|' /var/www/qlync_admin/html/api/util.php
else
  sed -i -e 's|public $DB_ENABLE = 1;|public $DB_ENABLE = 0;|' /var/www/qlync_admin/html/api/util.php
fi
myPrintInfo "complete special admin api\n"

############
#recycle admin server in 90 days
config_info=$(grep '/var/log/apache2/' /etc/crontab)
if [ -z "$config_info" ]; then #-z test if its empty string
  echo "0 1     * * 0   root  find /var/log/apache2/  -type f -mtime +90 -exec rm {} +" >> /etc/crontab
  myPrintInfo "set /var/log/apache2/ recycle (90days) to /etc/crontab"
fi
#sed -i -e 's|/var/tmp/  -type f -mtime +30|/var/tmp/  -type f -mtime +90|' /etc/crontab
config_info=$(grep 'find /var/tmp/' /etc/crontab)
if [ -z "$config_info" ]; then #-z test if its empty string
  echo "0 1     * * 0   root  find /var/tmp/  -type f -mtime +90 -exec rm {} +" >> /etc/crontab
  myPrintInfo "set /var/tmp/ recycle (90days) to /etc/crontab"
fi

#patch customer logo (file is under SSL/XXX/ and copy to root)
cp company_logo.png /var/www/qlync_admin/images/logo_qlync.png
chmod 777 /var/www/qlync_admin/images/logo_qlync.png  
cp favicon.ico /var/www/qlync_admin/
chmod 777 /var/www/qlync_admin/favicon.ico
mv robots.txt /var/www/qlync_admin/
myPrintInfo "set Default Logo/Icon"

if [ "${oemid}" = '"X02"' ]; then
  #php _db_add_plugin_menu_nest_godwatch.php //integrate to _rpic.php
  #myPrintInfo "Add godwatch menu"
  cp -avr debug/gw/* /var/www/qlync_admin/plugin/debug
  myPrintInfo "install godwatch feature pages"
  if [ ${licservice_enable} -eq 1 ]; then
    sudo sh patch_licservice.sh
    php _db_add_plugin_menu_nest_licservice.php
    myPrintInfo "complete licservice installation\n"
  fi
  sudo rm -rf /var/www/qlync_admin/doc/*.pdf
  sudo rm -rf /var/www/qlync_admin/doc/*.pdf.1
  sudo wget ${FTPparam} -P /var/www/qlync_admin/doc -N ${FTPPATH2}/ivedamobile_cht.pdf
  sudo wget ${FTPparam} -P /var/www/qlync_admin/doc -N ${FTPPATH2}/Z3505.pdf
  sudo wget ${FTPparam} -P /var/www/qlync_admin/doc -N ${FTPPATH2}/NCR770.pdf
  sudo wget ${FTPparam} -P /var/www/qlync_admin/doc -N ${FTPPATH2}/Z3020_install_user_guide.pdf
  #sudo wget --timeout=10 --tries=1 -P /var/www/qlync_admin/doc -N ftp://IvedaIGS:IvedaIGS85168@118.163.90.31/IvedaIGS/TW/APP/ 
  sudo wget ${FTPparam} -P /var/www/qlync_admin/doc -N ${FTPPATH2}/MegasysXpressAPP.pdf
  sudo wget ${FTPparam} -P /var/www/qlync_admin/doc -N ${FTPPATH2}/Workeye*.pdf
  sh patch_rpic.sh X02
  php _db_add_plugin_menu_nest_rpic.php
  myPrintInfo "Add RPIC mgmt menu"
  php _db_add_plugin_menu_cleanup.php
  myPrintInfo "Cleanup unnecessary menu for RPIC"
elif [ "${oemid}" = '"P04"' ]; then
  #patch customverservice
  sh patch_customer.sh
  myPrintInfo "install PLDT package customerservice"
elif [ "${oemid}" = '"T04"' ]; then
#patch plugin service menu
  sh patch_rpic.sh T04  #picture files, pdf files
  php _db_add_plugin_menu_nest_rpic.php
  myPrintInfo "Add RPIC mgmt menu"
  #wget --timeout=10 --tries=1 ftp://iveda:2wsxCFT6@ftp.tdi-megasys.com/TW/APP/RPIC/rpic_support.pdf
  #mv rpic_support.pdf /var/www/qlync_admin/doc
  #myPrintInfo "Add Support Document. URL @ https://rpic.taipei:8080/doc/"
  php _db_add_plugin_menu_cleanup.php
  myPrintInfo "Cleanup unnecessary menu for RPIC"
elif [ "${oemid}" = '"T05"' ]; then
  sh patch_rpic.sh T05
  php _db_add_plugin_menu_nest_rpic.php
  myPrintInfo "Add RPIC mgmt menu"
  php _db_add_plugin_menu_cleanup.php
  myPrintInfo "Cleanup unnecessary menu for RPIC"
elif [ "${oemid}" = '"T06"' ]; then
  sh patch_rpic.sh T06
  #php _db_add_plugin_menu_nest_rpic.php
  #myPrintInfo "Add RPIC mgmt menu"
  php _db_add_plugin_menu_cleanup.php
  myPrintInfo "Cleanup unnecessary menu for RPIC"
elif [ "${oemid}" = '"C13"' ]; then
  #sh patch_rpic.sh C13
  php _db_add_plugin_menu_nest_rpic.php
  myPrintInfo "Add RPIC mgmt menu"
  php _db_add_plugin_menu_cleanup.php
  myPrintInfo "Cleanup unnecessary menu for RPIC"

elif [ "${oemid}" = '"K01"' ]; then
  sh patch_rpic.sh K01
  php _db_add_plugin_menu_nest_rpic.php
  myPrintInfo "Add RPIC mgmt menu"
  php _db_add_plugin_menu_cleanup.php
  myPrintInfo "Cleanup unnecessary menu for RPIC"
elif [ "${oemid}" = '"V03"' ]; then #-o "${oemid}" = '"V04"'
  sudo rm -rf /var/www/qlync_admin/doc/*.pdf
  sudo rm -rf /var/www/qlync_admin/doc/*.pdf.1 
  wget --timeout=10 --tries=1 ftp://mesa:Ivedamesa16888@ftp.tdi-megasys.com/SOP/Z3022_install_user_guide_VN.pdf
  wget --timeout=10 --tries=1 ftp://mesa:Ivedamesa16888@ftp.tdi-megasys.com/SOP/Z3202PT_install_user_guide_VN.pdf
  wget --timeout=10 --tries=1 ftp://mesa:Ivedamesa16888@ftp.tdi-megasys.com/SOP/Z3503_install_user_guide_VN.pdf
  mv *.pdf /var/www/qlync_admin/doc/
  myPrintInfo "Add IvedaVN Camera(3) Documentation. URL @ https://camera.vinaphone.vn:8080/doc/" # or https://sentirvietnam.vn:8080/doc/
elif [ "${oemid}" = '"Z02"' ]; then
  sudo rm -rf /var/www/qlync_admin/doc/*.pdf
  sudo rm -rf /var/www/qlync_admin/doc/*.pdf.1
  wget --timeout=10 --tries=1 ftp://mesa:Ivedamesa16888@ftp.tdi-megasys.com/SOP/Z3010_install_user_guide.pdf
  wget --timeout=10 --tries=1 ftp://mesa:Ivedamesa16888@ftp.tdi-megasys.com/SOP/Z3020_install_user_guide.pdf
  wget --timeout=10 --tries=1 ftp://mesa:Ivedamesa16888@ftp.tdi-megasys.com/SOP/Z3501_install_user_guide.pdf
  wget --timeout=10 --tries=1 ftp://mesa:Ivedamesa16888@ftp.tdi-megasys.com/SOP/Z3201PT_install_user_guide.pdf
  mv *.pdf /var/www/qlync_admin/doc/
  myPrintInfo "Add Iveda Camera(4) Documentation. URL @ https://zee.ivedaxpress.com:8080/doc/"
fi

if [ -f "/usr/igs/scripts/routine.py" ]; then
  cp ha/routine.py /usr/igs/scripts/
  echo "update latest routine script"
  cd ha/
  sudo sh routine.sh
  cd ..
fi

######patch timezone
#America/Phoenix for mesa, Asia/Tokyo for japan, Asia/Taipei
config_info=$(grep '$time_zone="Asia/Taipei";' /var/www/qlync_admin/doc/config.php)
if [ ! -z "$config_info" ]; then #-z test if its empty string
  if [ "${oemid}" = '"T03"' ]; then
    sed -i -e 's/$time_zone="Asia\/Taipei";/$time_zone="America\/Phoenix";/' /var/www/qlync_admin/doc/config.php
    myPrintInfo "patch timezone to America/Phoenix\n" 
  elif [ "${oemid}" = '"J01"' ]; then
    sed -i -e 's/$time_zone="Asia\/Taipei";/$time_zone="Asia\/Tokyo";/' /var/www/qlync_admin/doc/config.php
    myPrintInfo "patch timezone to Asia/Tokyo\n"
  elif [ "${oemid}" = '"P04"' ]; then
    sed -i -e 's/$time_zone="Asia\/Taipei";/$time_zone="Asia\/Manila";/' /var/www/qlync_admin/doc/config.php
    myPrintInfo "patch timezone to Asia/Manila\n"
  elif [ "${oemid}" = '"V03"' -o "${oemid}" = '"V04"' ]; then
    sed -i -e 's/$time_zone="Asia\/Taipei";/$time_zone="Asia\/Ho_Chi_Minh";/' /var/www/qlync_admin/doc/config.php
    myPrintInfo "patch timezone to Asia/Ho_Chi_Minh\n"
  elif [ "${oemid}" = '"Z02"' -o "${oemid}" = '"X02"' -o "${oemid}" = '"X01"' -o "${oemid}" = '"T04"' -o "${oemid}" = '"T05"' -o "${oemid}" = '"K01"' -o "${oemid}" = '"T06"' -o "${oemid}" = '"C13"' ]; then
    myPrintInfo "timezone is default Asia/Taipei\n"
  fi
fi

#patch after bug file replaced
sh patch_service.sh
php _db_add_plugin_menu_service.php
myPrintInfo "Add Service menu under License"

#replace plugin home directory is account is not ivedasuper
config_info=$(pwd | grep 'ivedasuper')
if [ -z "$config_info" ]; then #-z test if its empty string
#sudo -S will given user as root
#if [ "${USER}" != "ivedasuper" -a "${USER}" != "root" ]; then
currDIR=${PWD}
BASEDIR=$(echo $currDIR | rev | cut -c 6- | rev)
#variable contains slash, need to user other delimiter like , or | instead of /  
  sed -i -e 's|/home/ivedasuper/|'"$BASEDIR"'|g' /var/www/qlync_admin/html/license/mac_check.php
  sed -i -e 's|/home/ivedasuper/|'"$BASEDIR"'|g' /var/www/qlync_admin/plugin/debug/delete_server.php
  sed -i -e 's|/home/ivedasuper/|'"$BASEDIR"'|g' /var/www/qlync_admin/plugin/debug/delete_server_new.php
  sed -i -e 's|/home/ivedasuper/|'"$BASEDIR"'|g' /var/www/qlync_admin/plugin/debug/stream_statuslog.php
  sed -i -e 's|/home/ivedasuper/|'"$BASEDIR"'|g' check/getCamStatus.sh
  sed -i -e 's|/home/ivedasuper/|'"$BASEDIR"'|g' check/server_check.py
  myPrintAlert "update account home directory path to $BASEDIR."
fi

#update oemid for admin tool
cd check
sh vminfo.sh
cd ..
#install mail server for internal usage
cd mail
sh install.sh
sudo rm /var/mail/root
cd ..
#patch menu sync with database
php /var/www/qlync_admin/html/common/menu_update.php
myPrintInfo "sync all menu changes in the database"
#patch TCP/PHP
sh patch_sysctl.sh
config_info=$(grep 'DOMAIN_NAME="https' ../isat_partner/install.conf)
if [ ! -z "$config_info" ]; then
  #patch SSL,
  adminPort=$(grep LISTEN_PORT= ../isat_partner/install.conf)
  #myPrintInfo $adminPort
  if [ -z "$adminPort" ]; then #fixed in installer jar 
    echo "LISTEN_PORT is not configured"
  fi
  if [ -f "$sslpem" -a -f "$sslkey" ]; then
	  sh ssl-apache.sh $sslpem $sslca $sslkey
	  myPrintInfo "set apache2 ssl key"
  fi
else
  sh ssl-apache.sh http
fi
if [ "${oemid}" = '"X02"' ]; then
  #godwatch use 8081 port
  sh ssl-apache.sh http8081
fi
if [ ! -z "$AdminTitle" ]; then
sed -i -e 's|Cloud Partner|'"$AdminTitle"'|' /var/www/qlync_admin/header.php
fi