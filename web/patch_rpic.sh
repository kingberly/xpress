RPIC_FOLDER="rpic"
USER_PWD_T04="Ea9M7gOu586UQaOtXJ3e6f51"
AUTOLOGIN_T04="backstage_login_tpe.php"
USER_PWD_T05="tN8V8bMTtuKycj7BNW2Esp8p"
AUTOLOGIN_T05="backstage_login_ty.php"
USER_PWD_K01="ydEP6Ug6uBzWTXU28gfSV3hu"
AUTOLOGIN_K01="backstage_login_rpic.php"
USER_PWD_T06="vzEG2Ea6fWzEGb5Ea235uZvm"
AUTOLOGIN_T06="backstage_login_rpic.php"
USER_PWD_X02="ydEP6Ug6uBzWTXU28gfSV3hu"
AUTOLOGIN_X02="backstage_login_rpic.php"
USER_PWD_RPIC="ydEP6Ug6uBzWTXU28gfSV3hu"
AUTOLOGIN_RPIC="backstage_login_rpic.php"
USER_PWD_C13="Cqjf6NE2R5xY5unCMXVDqqmP"
AUTOLOGIN_C13="backstage_login_rpic.php"
#USER_PWD_XXX="skv9SjcwfFGgFzZduacc"
FTPPATH="ftp://iveda:2wsxCFT6@ftp.tdi-megasys.com/TW/APP/RPIC"
#FTPPATH2="ftp://IvedaIGS:IvedaIGS85168@118.163.90.31/IvedaIGS/TW/APP"
#wget ftp://iveda:1qaz2wsX@118.163.90.31/IvedaIGS/TW/APP/xpress.c13.ipa 
FTPPATH2="ftp://iveda:1qaz2wsX@118.163.90.31/IvedaIGS/TW/APP"
FTPparam="--timeout=10 --tries=1"
#--recover------------------------
if [ "$1" = "recover" ]; then
  config_info=$(grep 'jinho fix' /var/www/SAT-CLOUDNVR/index.php)
  if [ ! -z "$config_info" ]; then #-z test if its empty string
    cp ../iSVW_install_scripts/SAT-CLOUDNVR/index.php /var/www/SAT-CLOUDNVR/
    echo "recover index.php"
  fi
  sed -i -e 's|device_list.php?mode=rpic|device_list.php?mode=<?php echo \$mode;?>|' /var/www/SAT-CLOUDNVR/iveda/index.php
  sed -i -e 's|device_list.php?mode=rpic|device_list.php?mode=<?php echo \$mode;?>|' /var/www/SAT-CLOUDNVR/index.php
  sed -i -e 's|<!--a href="#" id="delete_tab_header"><?php echo _("Delete"); ?></a-->|<a href="#" id="delete_tab_header"><?php echo _("Delete"); ?></a>|' /var/www/SAT-CLOUDNVR/device.php
  sed -i -e 's|echo "rpic";|echo "personal";|' /var/www/SAT-CLOUDNVR/my_account.php 
  echo "recover device_list delete disable"
  exit
#--remove------------------------
elif [ "$1" = "remove" ]; then
  
  if [ -d "/var/www/SAT-CLOUDNVR/rpic" ]; then
    rm -rf /var/www/SAT-CLOUDNVR/rpic
  elif [ -d "/var/www/SAT-CLOUDNVR/taipei" ]; then
    rm -rf /var/www/SAT-CLOUDNVR/taipei
  elif [ -d "/var/www/SAT-CLOUDNVR/ty" ]; then
    rm -rf /var/www/SAT-CLOUDNVR/ty
  fi
  rm /var/www/SAT-CLOUDNVR/manage/manage_share.php
  rm /var/www/SAT-CLOUDNVR/$AUTOLOGIN_T04
  rm /var/www/SAT-CLOUDNVR/$AUTOLOGIN_T05
  rm /var/www/SAT-CLOUDNVR/$AUTOLOGIN_RPIC
  echo "cleanup app download folder"
  #sed -n '/pattern/p' file
  sed -i -e 's|$recovery_code, "alarm@tdi-megasys.com") )|$recovery_code, $user_info_row\["reg_email"]) )|' /var/www/SAT-CLOUDNVR/backstage_user_register.php
  exit
#------------------------------------------------------------------------   
elif [ "$1" = "T04" ]; then
	RPIC_FOLDER="taipei"
  if [ ! -d "/var/www/SAT-CLOUDNVR/$RPIC_FOLDER" ]; then
    mkdir /var/www/SAT-CLOUDNVR/$RPIC_FOLDER
  fi
   
  #cp taipei/appdownload.php /var/www/SAT-CLOUDNVR/$RPIC_FOLDER/
  cp taipei/rpic* /var/www/SAT-CLOUDNVR/$RPIC_FOLDER/

  #wget ${FTPparam} -P /var/www/SAT-CLOUDNVR/taipei/ ${FTPPATH}/rpic.apk
  wget ${FTPparam} ${FTPPATH2}/rpic.apk
  wget ${FTPparam} ${FTPPATH2}/rpic.ipa
  wget ${FTPparam} ${FTPPATH2}/ivedamobile.apk
  sudo mv *.apk *.ipa /var/www/SAT-CLOUDNVR/$RPIC_FOLDER/
  cp rpic/ivedamobile.htm /var/www/SAT-CLOUDNVR/$RPIC_FOLDER/
  cp rpic/rpic.htm /var/www/SAT-CLOUDNVR/$RPIC_FOLDER/
  cp rpic.plist /var/www/SAT-CLOUDNVR/$RPIC_FOLDER/
  cp rpic/rpic.php /var/www/SAT-CLOUDNVR/$RPIC_FOLDER/
  cp rpic/rpic.php /var/www/SAT-CLOUDNVR/$RPIC_FOLDER/ivedamobile.php
  cp rpic/rpic.plist.php /var/www/SAT-CLOUDNVR/$RPIC_FOLDER/   #rpic.plist cannot redirect
  #cp rpic/rpic.plist.php /var/www/SAT-CLOUDNVR/$RPIC_FOLDER/
  echo "copy rpic files (plist, html, apk, ipa) to $RPIC_FOLDER folder."

  config_info=$(grep 'USER_PWD' /var/www/SAT-CLOUDNVR/backstage_login.php)
  if [ -z "$config_info" ]; then #-z test if its empty string
  sed -i -e '/Check number of failure attemps/ {
      a\define("APP_USER_PWD","'$USER_PWD_T04'");
      a\if (\$_REQUEST[\x27user_pwd\x27] == APP_USER_PWD  ) 
      a\   if ( (preg_match("/^[0-9A-Z\x27-]{6,15}\$/",\$_REQUEST[\x27user_name\x27])) )
      a\       throw new Exception(\x27Web access of this account is not allowed.\x27);
  }' /var/www/SAT-CLOUDNVR/backstage_login.php
  fi
  cp rpic/manage_share.php /var/www/SAT-CLOUDNVR/manage/
  cp rpic/$AUTOLOGIN_RPIC /var/www/SAT-CLOUDNVR/$AUTOLOGIN_T04
  echo "$RPIC_FOLDER project API installation done."
	echo "disable delete camera"
  #wget ${FTPparam} -O /var/www/SAT-CLOUDNVR/taipei/rpic_support.pdf ${FTPPATH}/rpic_support.pdf
  wget ${FTPparam} ${FTPPATH2}/rpic_support.pdf
  sudo mv *.pdf /var/www/SAT-CLOUDNVR/$RPIC_FOLDER/
sed -i -e 's|device_list.php?mode=<?php echo \$mode;?>|device_list.php?mode=rpic|' /var/www/SAT-CLOUDNVR/index.php
sed -i -e 's|device_list.php?mode=<?php echo \$mode;?>|device_list.php?mode=rpic|' /var/www/SAT-CLOUDNVR/iveda/index.php
	echo "disable camera delete function from web"
#disable pwd change
cp rpic/my_account.php /var/www/SAT-CLOUDNVR/
#hyjack forgot password to MIS  #sed -n '/pattern/p' file
sed -i -e 's|$recovery_code, $user_info_row\["reg_email"]) )|$recovery_code, "alarm@tdi-megasys.com") )|' /var/www/SAT-CLOUDNVR/backstage_user_register.php
#add lb support
sh web_redirecterror.sh
#for load balancer project
sudo cp rpic/get_camera_info.php /var/www/SAT-CLOUDNVR/manage/
#------------------------------------------------------------------------ 
elif [ "$1" = "T05" ]; then
	RPIC_FOLDER="ty"
  if [ ! -d "/var/www/SAT-CLOUDNVR/$RPIC_FOLDER" ]; then
  mkdir /var/www/SAT-CLOUDNVR/$RPIC_FOLDER
	fi 
  cp rpic/rpic* /var/www/SAT-CLOUDNVR/$RPIC_FOLDER/

  #wget cannot overwrite
  #wget ${FTPparam} -P /var/www/SAT-CLOUDNVR/ty/ ${FTPPATH}/rpic.ty.apk
  wget ${FTPparam} ${FTPPATH2}/rpic.ty.apk
  wget ${FTPparam} ${FTPPATH2}/rpic.ty.ipa
  wget ${FTPparam} ${FTPPATH2}/ivedamobile.ty.apk
  sudo mv *.apk *.ipa /var/www/SAT-CLOUDNVR/$RPIC_FOLDER/
  cp rpic/ivedamobile.ty.htm /var/www/SAT-CLOUDNVR/$RPIC_FOLDER/ivedamobile.htm
  cp rpic/rpic.ty.htm /var/www/SAT-CLOUDNVR/$RPIC_FOLDER/rpic.htm
  cp rpic/rpic.php /var/www/SAT-CLOUDNVR/$RPIC_FOLDER/rpic.ty.php
  cp rpic/rpic.php /var/www/SAT-CLOUDNVR/$RPIC_FOLDER/ivedamobile.ty.php
  cp rpic.plist /var/www/SAT-CLOUDNVR/$RPIC_FOLDER/
  echo "copy rpic files (plist, html, apk, ipa) to folder."

  config_info=$(grep 'USER_PWD' /var/www/SAT-CLOUDNVR/backstage_login.php)
  if [ -z "$config_info" ]; then #-z test if its empty string
  sed -i -e '/Check number of failure attemps/ {
      a\define("APP_USER_PWD","'$USER_PWD_T05'");
      a\if ( (preg_match("/^[0-9A-Z\x27-]{5,15}\$/",\$_REQUEST[\x27user_name\x27])) 
      a\and (\$_REQUEST[\x27user_pwd\x27] == APP_USER_PWD  )   )
      a\       throw new Exception(\x27Web access of this account is not allowed.\x27);
  }' /var/www/SAT-CLOUDNVR/backstage_login.php
  fi
  cp rpic/manage_share.php /var/www/SAT-CLOUDNVR/manage/
  cp rpic/$AUTOLOGIN_RPIC /var/www/SAT-CLOUDNVR/$AUTOLOGIN_T05
  echo "$RPIC_FOLDER project API installation done."
  #update@GUI change
  wget ${FTPparam} ${FTPPATH2}/rpic_support.ty.pdf
  sudo mv rpic_support.ty.pdf /var/www/SAT-CLOUDNVR/$RPIC_FOLDER/rpic_support.pdf
sed -i -e 's|device_list.php?mode=<?php echo \$mode;?>|device_list.php?mode=rpic|' /var/www/SAT-CLOUDNVR/index.php
sed -i -e 's|device_list.php?mode=<?php echo \$mode;?>|device_list.php?mode=rpic|' /var/www/SAT-CLOUDNVR/iveda/index.php
	echo "disable camera delete function from web"
#disable pwd change
cp rpic/my_account.php /var/www/SAT-CLOUDNVR/
#hyjack forgot password to MIS  #sed -n '/pattern/p' file
sed -i -e 's|$recovery_code, $user_info_row\["reg_email"]) )|$recovery_code, "alarm@tdi-megasys.com") )|' /var/www/SAT-CLOUDNVR/backstage_user_register.php

#------------------------------------------------------------------------
elif [ "$1" = "K01" ]; then
  if [ ! -d "/var/www/SAT-CLOUDNVR/$RPIC_FOLDER" ]; then
    mkdir /var/www/SAT-CLOUDNVR/$RPIC_FOLDER
  fi
  wget ${FTPparam} -O rpic.apk ${FTPPATH2}/rpic.k01.apk
  wget ${FTPparam} -O ivedamobile.apk ${FTPPATH2}/ivedamobile.k01.apk
  #wget ${FTPparam} ${FTPPATH2}/rpic.k01.1121.apk
  sudo mv *.apk /var/www/SAT-CLOUDNVR/$RPIC_FOLDER/ 
  cp rpic/rpic.php /var/www/SAT-CLOUDNVR/$RPIC_FOLDER/
  cp rpic/rpic.php /var/www/SAT-CLOUDNVR/$RPIC_FOLDER/ivedamobile.php
  echo "copy $RPIC_FOLDER files (plist, php/html, apk, ipa) to folder."

  config_info=$(grep 'USER_PWD' /var/www/SAT-CLOUDNVR/backstage_login.php)
  if [ -z "$config_info" ]; then #-z test if its empty string
  sed -i -e '/Check number of failure attemps/ {
      a\define("APP_USER_PWD","'$USER_PWD_K01'");
      a\if ( (preg_match("/^[0-9A-Z\x27-]{5,32}\$/",\$_REQUEST[\x27user_name\x27])) 
      a\and (\$_REQUEST[\x27user_pwd\x27] == APP_USER_PWD  )   )
      a\       throw new Exception(\x27Web access of this account is not allowed.\x27);
  }' /var/www/SAT-CLOUDNVR/backstage_login.php
  fi
  cp rpic/manage_share.php /var/www/SAT-CLOUDNVR/manage/
  cp rpic/$AUTOLOGIN_K01 /var/www/SAT-CLOUDNVR/
  echo "${1} project API installation."

  wget ${FTPparam} -O rpic_support.pdf ${FTPPATH2}/rpic_support.k01.pdf
  sudo mv rpic_support.pdf /var/www/SAT-CLOUDNVR/$RPIC_FOLDER/

	sed -i -e 's|g_page_control_auto_cycle_interval = setInterval(AutoCycleNext, 10000);|g_page_control_auto_cycle_interval = setInterval(AutoCycleNext, 300000);|' /var/www/SAT-CLOUDNVR/js/page_control.js
	echo "${1} auto cycle time set to 5 min(300s)"
	cp rpic/login.php /var/www/SAT-CLOUDNVR/
	echo "add security to login and password page"
#disable pwd change
cp rpic/my_account.php /var/www/SAT-CLOUDNVR/
#hyjack forgot password to MIS  #sed -n '/pattern/p' file
sed -i -e 's|$recovery_code, $user_info_row\["reg_email"]) )|$recovery_code, "alarm@tdi-megasys.com") )|' /var/www/SAT-CLOUDNVR/backstage_user_register.php
cd v324
sudo sh patch.sh
cd ..
#--X02----------------------------------------------------------------
elif [ "$1" = "X02" ]; then
  if [ ! -d "/var/www/SAT-CLOUDNVR/$RPIC_FOLDER" ]; then
    mkdir /var/www/SAT-CLOUDNVR/$RPIC_FOLDER
  fi
	#wget ${FTPparam} ${FTPPATH}/rpic.t06.apk
	wget ${FTPparam} -O ivedamobile.apk ${FTPPATH2}/ivedamobile.x02.apk
  sudo mv *.apk /var/www/SAT-CLOUDNVR/$RPIC_FOLDER/ 
  #cp $RPIC_FOLDER/rpic.php /var/www/SAT-CLOUDNVR/$RPIC_FOLDER/rpic.t06.php
  cp rpic/rpic.php /var/www/SAT-CLOUDNVR/$RPIC_FOLDER/ivedamobile.php
  echo "copy $RPIC_FOLDER files (plist, php/html, apk, ipa) to folder /var/www/SAT-CLOUDNVR/$RPIC_FOLDER."

  cp rpic/$AUTOLOGIN_X02 /var/www/SAT-CLOUDNVR/
  cp rpic/manage_share.php /var/www/SAT-CLOUDNVR/manage/
  myPrintInfo "${1} rpic manage_share api installed"

#  config_info=$(grep 'USER_PWD' /var/www/SAT-CLOUDNVR/backstage_login.php)
#  if [ -z "$config_info" ]; then #-z test if its empty string
#  sed -i -e '/Check number of failure attemps/ {
#      a\define("APP_USER_PWD","'$USER_PWD_RPIC'");
#      a\if ( (preg_match("/^[0-9A-Z-]{5,32}\$/",\$_REQUEST[\x27user_name\x27])) 
#      a\and (\$_REQUEST[\x27user_pwd\x27] == APP_USER_PWD  )   )
#      a\       throw new Exception(\x27Web access of this account is not allowed.\x27);
#  }' /var/www/SAT-CLOUDNVR/backstage_login.php
#  fi

  wget ${FTPparam} ${FTPPATH2}/support.pdf
  sudo mv support.pdf /var/www/SAT-CLOUDNVR/

 	sed -i -e 's|g_page_control_auto_cycle_interval = setInterval(AutoCycleNext, 10000);|g_page_control_auto_cycle_interval = setInterval(AutoCycleNext, 300000);|' /var/www/SAT-CLOUDNVR/js/page_control.js
	echo "${1} auto cycle time set to 5 min(300s)"
	sudo cp rpic/login.php /var/www/SAT-CLOUDNVR/
sed -i -e 's|device_list.php?mode=<?php echo \$mode;?>|device_list.php?mode=rpic|' /var/www/SAT-CLOUDNVR/index.php
sed -i -e 's|device_list.php?mode=<?php echo \$mode;?>|device_list.php?mode=rpic|' /var/www/SAT-CLOUDNVR/iveda/index.php
	echo "disable camera delete function from web"
  sudo cp rpic/query_info.php /var/www/SAT-CLOUDNVR/
  sudo cp rpic/rpic_query.inc /var/www/SAT-CLOUDNVR/
#--C13----------------------------------------------------------------
elif [ "$1" = "C13" ]; then
  if [ ! -d "/var/www/SAT-CLOUDNVR/$RPIC_FOLDER" ]; then
    mkdir /var/www/SAT-CLOUDNVR/$RPIC_FOLDER
  fi
  wget ${FTPparam} ${FTPPATH2}/support.c13.pdf
  sudo mv support.c13.pdf /var/www/SAT-CLOUDNVR/support.pdf
  sudo sed -i -e 's|xpress.megasys.com.tw|engeye.chimei.com.tw|g' /var/www/SAT-CLOUDNVR/js/faq.json 
  
  #backstage login UI for C13
  #cp rpic/$AUTOLOGIN_C13 /var/www/SAT-CLOUDNVR/
  cp rpic/backstage_login_rpic.login /var/www/SAT-CLOUDNVR/$AUTOLOGIN_C13
  cp rpic/manage_share.php /var/www/SAT-CLOUDNVR/manage/
  myPrintInfo "${1} rpic manage_share api installed"
 	sed -i -e 's|g_page_control_auto_cycle_interval = setInterval(AutoCycleNext, 10000);|g_page_control_auto_cycle_interval = setInterval(AutoCycleNext, 300000);|' /var/www/SAT-CLOUDNVR/js/page_control.js
	echo "${1} auto cycle time set to 5 min(300s)"
sed -i -e 's|device_list.php?mode=<?php echo \$mode;?>|device_list.php?mode=rpic|' /var/www/SAT-CLOUDNVR/index.php
sed -i -e 's|device_list.php?mode=<?php echo \$mode;?>|device_list.php?mode=rpic|' /var/www/SAT-CLOUDNVR/iveda/index.php
  sed -i -e 's|//download_disabled = "disabled";|download_disabled = "disabled";|' /var/www/SAT-CLOUDNVR/js/device_list.js
  #sed -i -e 's|download_disabled = "disabled"; //C13 disable download|//download_disabled = "disabled"; //C13 disable download|' /var/www/SAT-CLOUDNVR/js/device_list.js
	echo "disable camera delete/download function from web"
  sudo sh sharecamera.sh 300
  sudo cp rpic/query_info.php /var/www/SAT-CLOUDNVR/
  sudo cp rpic/rpic_query.inc /var/www/SAT-CLOUDNVR/
#--default----------------------------------------------------------------  
elif [ "$1" = "RPIC" ]; then
  oemid=$(sed -n '/OEM_ID=/p' ../iSVW_install_scripts/install.conf | tail -c 6 | head -c 4 | tail -c 3)

  if [ ! -d "/var/www/SAT-CLOUDNVR/$RPIC_FOLDER" ]; then
    mkdir /var/www/SAT-CLOUDNVR/$RPIC_FOLDER
  fi

  config_info=$(grep 'USER_PWD' /var/www/SAT-CLOUDNVR/backstage_login.php)
  if [ -z "$config_info" ]; then #-z test if its empty string
  sed -i -e '/Check number of failure attemps/ {
      a\define("APP_USER_PWD","'$USER_PWD_RPIC'");
      a\if ( (preg_match("/^[0-9A-Za-z\x27-]{5,32}\$/",\$_REQUEST[\x27user_name\x27])) 
      a\and (\$_REQUEST[\x27user_pwd\x27] == APP_USER_PWD  )   )
      a\       throw new Exception(\x27Web access of this account is not allowed.\x27);
  }' /var/www/SAT-CLOUDNVR/backstage_login.php
  fi
  cp rpic/manage_share.php /var/www/SAT-CLOUDNVR/manage/
  cp rpic/$AUTOLOGIN_X02 /var/www/SAT-CLOUDNVR/
  echo "RPIC ${1} ${oemid} project API installation."

	sed -i -e 's|g_page_control_auto_cycle_interval = setInterval(AutoCycleNext, 10000);|g_page_control_auto_cycle_interval = setInterval(AutoCycleNext, 300000);|' /var/www/SAT-CLOUDNVR/js/page_control.js
	echo "RPIC ${oemid} auto cycle time set to 30s" 
fi
#--general----------------------------------------------
#default language is en_TW , zh_HANT-TW
sed -i -e 's|\["user_language"] = "en_US";|\["user_language"] = "zh_TW";|' /var/www/SAT-CLOUDNVR/include/global.php
#sed -i -e 's|"en_US" )|"zh_TW" )|' /var/www/SAT-CLOUDNVR/backstage_language.php

#disable camera delete
cp rpic/js/* /var/www/SAT-CLOUDNVR/js/
#sed -i -e 's|device_list.php?mode=<?php echo \$mode;?>|device_list.php?mode=rpic|' /var/www/SAT-CLOUDNVR/index.php
#sed -i -e 's|device_list.php?mode=<?php echo \$mode;?>|device_list.php?mode=rpic|' /var/www/SAT-CLOUDNVR/iveda/index.php
#echo "disable camera delete function from web"

#disable camera delete function, use sed instead
#cp rpic/device_list.v303 /var/www/SAT-CLOUDNVR/device_list.php
#cp rpic/device.v303 /var/www/SAT-CLOUDNVR/device.php
sed -i -e 's|<a href="#" id="delete_tab_header"><?php echo _("Delete"); ?></a>|<!--a href="#" id="delete_tab_header"><?php echo _("Delete"); ?></a-->|' /var/www/SAT-CLOUDNVR/device.php 
echo "disable delete device button."
#renew google reCaptcha check
cp rpic/password_recovery.php /var/www/SAT-CLOUDNVR/
echo "add reCaptcha v2"
#NTP debug maintain page
cp rpic/camera_setting.php /var/www/SAT-CLOUDNVR/
echo "add maintain page"
#cp rpic/js/device_matrix_ts.js /var/www/SAT-CLOUDNVR/js
#add shared page auto cycle
cp rpic/shared_matrix.php /var/www/SAT-CLOUDNVR/
cp rpic/iveda/shared_matrix.php /var/www/SAT-CLOUDNVR/iveda/
#mobile GUI for Device/Shared Camera List  
cp rpic/backstage_onlineplayer.php /var/www/SAT-CLOUDNVR/
cp rpic/backstage_download.php /var/www/SAT-CLOUDNVR/
cp rpic/backstage_mobile.php /var/www/SAT-CLOUDNVR/ 
echo "VLC player plugin page"
cp rpic/rpic.inc /var/www/SAT-CLOUDNVR/
sed -i -e 's|XNN|'"$1"'|' /var/www/SAT-CLOUDNVR/manage/manage_share.php
if [ "$1" = "T04" ]; then
sed -i -e 's|XNN|'"$1"'|' /var/www/SAT-CLOUDNVR/$AUTOLOGIN_T04
elif [ "$1" = "T05" ]; then
sed -i -e 's|XNN|'"$1"'|' /var/www/SAT-CLOUDNVR/$AUTOLOGIN_T05
else
sed -i -e 's|XNN|'"$1"'|' /var/www/SAT-CLOUDNVR/$AUTOLOGIN_RPIC
fi
sed -i -e 's|XNN|'"$1"'|' /var/www/SAT-CLOUDNVR/rpic.inc
echo "replace OEM_ID"
cp -avr rpic/images /var/www/SAT-CLOUDNVR/
#disable delete function
sed -i -e 's|echo "personal";|echo "rpic";|' /var/www/SAT-CLOUDNVR/my_account.php