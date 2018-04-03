RPIC_FOLDER="rpic"
SETDB=1
FTPPATH="ftp://iveda:2wsxCFT6@ftp.tdi-megasys.com/TW/APP/RPIC"
#FTPPATH2="ftp://IvedaIGS:IvedaIGS85168@118.163.90.31/IvedaIGS/TW/APP"
FTPPATH2="ftp://iveda:1qaz2wsX@118.163.90.31/IvedaIGS/TW/APP"
FTPparam="--timeout=10 --tries=1"
apt-get -y --force-yes install unzip

#url, type: extract file in tmp folder and move to target folder
#ZipInstall "wget -O pdf.zip ${FTPparam} ${FTPPATH2}/pdf.xx.zip" pdf 
#ZipInstall "wget -O picture.zip ${FTPparam} ${FTPPATH2}/picture.xx.zip" picture
ZipInstall(){
  SCRIPT_ROOT="${PWD}"
  TMPFOLDER="/var/tmp/rpic"
  TARGETFOLDER="/var/www/qlync_admin/html/faq"
  if [ -d "$TMPFOLDER" ]; then
    rm -rf $TMPFOLDER 
  fi
  mkdir $TMPFOLDER
  cd $TMPFOLDER
  #download $1 to working folder
  eval "$1"
  ZIPFILE=$(ls $TMPFOLDER/${2}*.zip)
  if [ -f "$ZIPFILE" ]; then
    if [ -d "$TARGETFOLDER/$2/" ]; then
      rm -rf $TARGETFOLDER/$2/ 
    else
      if [ "$2" = "pdf" ]; then
      rm $TARGETFOLDER/*.pdf
      elif [ "$2" = "picture" ]; then
      rm $TARGETFOLDER/*.jpg
      rm $TARGETFOLDER/*.png
      fi
    fi
    unzip -O BIG5 $ZIPFILE #force ovwerwrite/filename BIG5

    if [ -d "$TMPFOLDER/$2/" ]; then
      mv $2 $TARGETFOLDER
    else
      if [ "$2" = "pdf" ]; then
        mv *.pdf $TARGETFOLDER/
      elif [ "$2" = "picture" ]; then
        mv *.jpg $TARGETFOLDER/
        mv *.png $TARGETFOLDER/
      fi
    fi
    echo "Complete upload $ZIPFILE to faq"
    rm  $ZIPFILE
  else
      echo "$ZIPFILE is not available"
      return 1
  fi
  cd $SCRIPT_ROOT
} 


if [ "$1" = "remove" ]; then
    if [ ! -z "$(grep 'connlog_tpe.php' /etc/crontab)" ]; then
        sed -i -e '/tunnel_connlog_tpe.php/d'  /etc/crontab 
        sed -i -e '/rtmpd_connlog_tpe.php/d'  /etc/crontab
        echo "cleanup t04 crontab service"
    fi
    sh patch_const.sh remove
    exit  
elif [ "$1" = "T04" ]; then
#remove old taipei folder
#rm -rf taipei/
  RPIC_FOLDER="taipei"
  if [ ! -d "/var/www/qlync_admin/plugin/$RPIC_FOLDER" ]; then
    mkdir /var/www/qlync_admin/plugin/$RPIC_FOLDER
  fi
  #cp -avr rpic/* /var/www/qlync_admin/plugin/taipei
  ZipInstall "wget -O pdf.zip ${FTPparam} ${FTPPATH2}/pdf.zip" pdf
  ZipInstall "wget -O picture.zip ${FTPparam} ${FTPPATH2}/picture.zip" picture
  cp rpic/turtorial.php  /var/www/qlync_admin/html/faq/
  cp rpic/faq.php  /var/www/qlync_admin/html/faq/
  cp rpic/appdownload.T04.php /var/www/qlync_admin/plugin/$RPIC_FOLDER/appdownload.php

  cp rpic/rpic_cht.php /var/www/qlync_admin/plugin/$RPIC_FOLDER/taipeiproj_cht.php
  cp rpic/cmdweb.php /var/www/qlync_admin/plugin/$RPIC_FOLDER
  cp rpic/*_connlog_tpe.php /var/www/qlync_admin/plugin/$RPIC_FOLDER
  cp rpic/tunnel_connlog_Excel.php  /var/www/qlync_admin/plugin/$RPIC_FOLDER
  echo "install ${1} mgmt plugin"

if [ -z "$(grep 'tunnel_connlog_tpe.php' /etc/crontab)" ]; then #-z test if its empty string
sed -i -e "\$a*/30 * * * * root /usr/bin/php5  \x22/var/www/qlync_admin/plugin/$RPIC_FOLDER/tunnel_connlog_tpe.php\x22" /etc/crontab
echo "add crontab to log tunnel connection"
else
echo "crontab tunnel connection log exist!"
fi
if [ -z "$(grep 'rtmpd_connlog_tpe.php' /etc/crontab)" ]; then #-z test if its empty string
sed -i -e "\$a*/15 * * * * root /usr/bin/php5  \x22/var/www/qlync_admin/plugin/$RPIC_FOLDER/rtmpd_connlog_tpe.php\x22" /etc/crontab
echo "add ceontab to log rtmp connection"
fi

elif [ "$1" = "T05" ]; then
  RPIC_FOLDER="ty"
  if [ ! -d "/var/www/qlync_admin/plugin/$RPIC_FOLDER" ]; then
    mkdir /var/www/qlync_admin/plugin/$RPIC_FOLDER
  fi

  #wget ${FTPparam} -O /var/www/qlync_admin/doc/rpic_support.pdf ${FTPPATH}/rpic_support.ty.pdf
  ZipInstall "wget -O pdf.zip ${FTPparam} ${FTPPATH2}/pdf.t05.zip" pdf
  ZipInstall "wget -O picture.zip ${FTPparam} ${FTPPATH2}/picture.t05.zip" picture
  cp rpic/turtorial.php  /var/www/qlync_admin/html/faq/
  cp rpic/faq.php  /var/www/qlync_admin/html/faq/
  cp rpic/appdownload.T05.php /var/www/qlync_admin/plugin/$RPIC_FOLDER/appdownload.php

  cp rpic/rpic_cht.php /var/www/qlync_admin/plugin/$RPIC_FOLDER
  myPrintInfo "Add ${1} $RPIC_FOLDER account mgmt and Share Log menu"
  #conn use Const
  sh patch_const.sh
  php _db_add_plugin_menu_nest_Const.php
  cp rpic/tunnelconn_Excel.php /var/www/qlync_admin/plugin/$RPIC_FOLDER
  if [ -z "$(grep tunnel_connlog_Excel_Const /etc/crontab)" ]; then
  sed -i -e "\$a5 5 1 * * root /usr/bin/php5 \x22/var/www/qlync_admin/plugin/debug/tunnel_connlog_Excel_Const.php\x22  GenerateExcel" /etc/crontab
  sed -i -e "\$a5 8 1 * * root /usr/bin/php5 \x22/var/www/qlync_admin/plugin/debug/tunnel_connlog_Excel_Const.php\x22  GenerateExcel rtmp" /etc/crontab
  fi 
elif [ "$1" = "K01" ]; then
  if [ ! -d "/var/www/qlync_admin/plugin/$RPIC_FOLDER" ]; then
    mkdir /var/www/qlync_admin/plugin/$RPIC_FOLDER
  fi

  ZipInstall "wget -O pdf.zip ${FTPparam} ${FTPPATH2}/pdf.k01.zip" pdf
  ZipInstall "wget -O picture.zip ${FTPparam} ${FTPPATH2}/picture.k01.zip" picture
  cp rpic/turtorial.php  /var/www/qlync_admin/html/faq/
  cp rpic/faq.php  /var/www/qlync_admin/html/faq/
  cp rpic/appdownload.K01.php /var/www/qlync_admin/plugin/$RPIC_FOLDER/appdownload.php

  #cp rpic/ /var/www/qlync_admin/plugin/$RPIC_FOLDER
  cp rpic/rpic_cht.php /var/www/qlync_admin/plugin/$RPIC_FOLDER
  cp rpic/showPipeUnit.php  /var/www/qlync_admin/plugin/$RPIC_FOLDER
  cp -avr rpic/html/* /var/www/qlync_admin/html
  cp rpic/rpic_cleanshare.php /var/www/qlync_admin/plugin/$RPIC_FOLDER
  if [ -z "$(grep 'rpic_cleanshare.php' /etc/crontab)" ]; then #-z test if its empty string
  sed -i -e '$a\1 1 * * * root  /usr/bin/php5  "/var/www/qlync_admin/plugin/'$RPIC_FOLDER'/rpic_cleanshare.php" K01' /etc/crontab
  echo "manually add crontab to cleanshare K01"
  fi
#elif [ "$1" = "T06" ]; then
#  if [ ! -d "/var/www/qlync_admin/plugin/$RPIC_FOLDER" ]; then
#    mkdir /var/www/qlync_admin/plugin/$RPIC_FOLDER
#  fi
# cp rpicmap/appdownload.T06.php /var/www/qlync_admin/plugin/$RPIC_FOLDER/appdownload.php
# cp -avr rpicmap/image/ /var/www/qlync_admin/plugin/$RPIC_FOLDER/
# cp rpic/rpic_cht.php /var/www/qlync_admin/plugin/$RPIC_FOLDER

elif [ "$1" = "C13" ]; then
  if [ ! -d "/var/www/qlync_admin/plugin/$RPIC_FOLDER" ]; then
    mkdir /var/www/qlync_admin/plugin/$RPIC_FOLDER
  fi
  ZipInstall "wget -O pdf.zip ${FTPparam} ${FTPPATH2}/pdf.c13.zip" pdf
  ZipInstall "wget -O picture.zip ${FTPparam} ${FTPPATH2}/picture.c13.zip" picture
  cp rpic/turtorial.php  /var/www/qlync_admin/html/faq/
  cp rpic/faq.php  /var/www/qlync_admin/html/faq/
  cp rpic/appdownload.C13.php /var/www/qlync_admin/plugin/$RPIC_FOLDER/appdownload.php
  #cp -avr rpicmap/image/ /var/www/qlync_admin/plugin/$RPIC_FOLDER/
  cp -avr rpic/html/* /var/www/qlync_admin/html
  cp rpic/rpic_cht.php /var/www/qlync_admin/plugin/$RPIC_FOLDER
  #conn use Const
  sh patch_const.sh
  php _db_add_plugin_menu_nest_Const.php
  cp rpic/tunnelconn_Excel.php /var/www/qlync_admin/plugin/$RPIC_FOLDER
  if [ -z "$(grep tunnel_connlog_Excel_Const /etc/crontab)" ]; then
  sed -i -e "\$a5 5 1 * * root /usr/bin/php5 \x22/var/www/qlync_admin/plugin/debug/tunnel_connlog_Excel_Const.php\x22  GenerateExcel" /etc/crontab
  sed -i -e "\$a5 8 1 * * root /usr/bin/php5 \x22/var/www/qlync_admin/plugin/debug/tunnel_connlog_Excel_Const.php\x22  GenerateExcel rtmp" /etc/crontab 
  fi
elif [ "$1" = "X02" ]; then
  if [ ! -d "/var/www/qlync_admin/plugin/$RPIC_FOLDER" ]; then
    mkdir /var/www/qlync_admin/plugin/$RPIC_FOLDER
  fi
  if [ ! -d "/var/www/qlync_admin/plugin/$RPIC_FOLDER/log" ]; then
    mkdir /var/www/qlync_admin/plugin/$RPIC_FOLDER/log
  fi
  chmod 777 /var/www/qlync_admin/plugin/$RPIC_FOLDER/log
  
  cp rpic/rpic_cht.php /var/www/qlync_admin/plugin/$RPIC_FOLDER
  #cp rpic/_iveda.inc /var/www/qlync_admin/plugin/$RPIC_FOLDER
  cp rpicmap/rpic_getdblist.php /var/www/qlync_admin/plugin/$RPIC_FOLDER
  cp rpicmap/rpic.inc /var/www/qlync_admin/plugin/$RPIC_FOLDER

  cp rpicmap/share.inc /var/www/qlync_admin/plugin/$RPIC_FOLDER
  cp rpicmap/showGIS.php /var/www/qlync_admin/plugin/$RPIC_FOLDER
  cp rpicmap/getGIS.php /var/www/qlync_admin/plugin/$RPIC_FOLDER
  cp rpicmap/editShare.php /var/www/qlync_admin/plugin/$RPIC_FOLDER
  cp rpicmap/showGISLog.php /var/www/qlync_admin/plugin/$RPIC_FOLDER
  cp rpicmap/installGIS.php /var/www/qlync_admin/plugin/$RPIC_FOLDER
  cp -avr rpicmap/image/ /var/www/qlync_admin/plugin/$RPIC_FOLDER/
  cp -avr rpicmap/workeye/image/  /var/www/qlync_admin/plugin/$RPIC_FOLDER/
  cp rpicmap/maintainGIS.php /var/www/qlync_admin/plugin/$RPIC_FOLDER
  cp rpicmap/online_list.php /var/www/qlync_admin/plugin/$RPIC_FOLDER
  cp rpicmap/account_list.php /var/www/qlync_admin/plugin/$RPIC_FOLDER
  cp rpic/workeyemap.php /var/www/qlync_admin/plugin/$RPIC_FOLDER
  
  cp rpicmap/rec_timetable.php /var/www/qlync_admin/plugin/$RPIC_FOLDER

  ZipInstall "wget -O pdf.zip ${FTPparam} ${FTPPATH2}/pdf.t06.zip" pdf
  ZipInstall "wget -O picture.zip ${FTPparam} ${FTPPATH2}/picture.t06.zip" picture
  cp rpic/turtorial.php  /var/www/qlync_admin/html/faq/
  cp rpic/faq.php  /var/www/qlync_admin/html/faq/
  cp rpicmap/appdownload.T06.php /var/www/qlync_admin/plugin/$RPIC_FOLDER/appdownload.php
  cp -avr rpicmap/picture/ /var/www/qlync_admin/html/faq/

elif [ "$1" = "RPIC" ]; then
  if [ ! -d "/var/www/qlync_admin/plugin/$RPIC_FOLDER" ]; then
    mkdir /var/www/qlync_admin/plugin/$RPIC_FOLDER
  fi
  #cp debug/sp/traccar.php /var/www/qlync_admin/plugin/debug
  cp rpic/rpic_cht.php /var/www/qlync_admin/plugin/$RPIC_FOLDER
  cp -avr rpic/html/* /var/www/qlync_admin/html
  echo "install ${1} plugin @$RPIC_FOLDER"   

fi
#cleanup old file
if [ "$1" != "X02" ]; then
rm -rf /var/www/qlync_admin/plugin/debug/godwatch*
fi
cp rpic/playback_list.php /var/www/qlync_admin/plugin/$RPIC_FOLDER
cp rpic/showShareLog.php /var/www/qlync_admin/plugin/$RPIC_FOLDER
cp rpic/_iveda.inc /var/www/qlync_admin/plugin/$RPIC_FOLDER
cp rpic/device_status.php /var/www/qlync_admin/plugin/$RPIC_FOLDER
#if [ -f "/var/www/qlync_admin/plugin/$RPIC_FOLDER/java_applet.pdf" ]; then
#wget ${FTPparam} ${FTPPATH2}/java_applet.pdf
#mv java_applet.pdf /var/www/qlync_admin/plugin/$RPIC_FOLDER/
#fi 
#default 180d, RVHI, AR (default RVME / DB=> 3,  LV), matser only
DBScript(){
python check/db_check.py "ALTER TABLE isat.stream_server_assignment MODIFY COLUMN recycle int(11) DEFAULT '182';"
python check/db_check.py "ALTER TABLE isat.stream_server_assignment MODIFY COLUMN dataplan varchar(8) DEFAULT 'AR';"
python check/web_check.py "sed -i -e 's|const DEFAULT_PURPOSE = \x27RVME\x27;|const DEFAULT_PURPOSE = \x27RVHI\x27;|' /var/www/SAT-CLOUDNVR/include/streamserver.php"
}
if [ $SETDB -eq 1 ]; then
if [ ! -z "$(ps -ax | grep [h]eartbeat)" ]; then
  config_info=$(ifconfig | grep eth0:0 | wc -l)
  if [ "$config_info" = "1" ]; then   #master
      DBScript
  fi
else   #standalone admin, run
  DBScript
fi
#run once
sed -i -e 's|^SETDB=1|SETDB=0|' $0
fi