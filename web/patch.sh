
if [ "$1" = "remove" ]; then
    sudo service lighttpd stop
    rm -rf /var/www/SAT-CLOUDNVR
    rm /etc/lighttpd/ssl/*.pem
    rm /etc/lighttpd/ssl/*.key
    sed -i -e '/lighttpd/d'  /etc/crontab
    apt-get remove lighttpd
    config_info=$(grep '/media/videos' /proc/mounts)
    if [ ! -z "$config_info" ]; then
        umount /media/videos                    
        echo "unmount shared nas"
        sed -i -e '/\/media\/videos/d'  /etc/rc.local
        sed -i -e '/\/media\/videos/d'  /etc/fstab
        echo "cleanup bootup mount process"
    fi
    sed -i -e '/checkNAS.sh/d'  /etc/crontab
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
addMySupportPage(){
  if [ "$2" = "Support.php" ]; then
    cp solevision/SAT-CLOUDNVR/Support.php /var/www/SAT-CLOUDNVR/
    cp solevision/SAT-CLOUDNVR/css/* /var/www/SAT-CLOUDNVR/css/ 
    cp -avr solevision/SAT-CLOUDNVR/js/ /var/www/SAT-CLOUDNVR/
  fi
	if [ -z "$3" ]; then
		chtPage=$2
	else
		chtPage=$3
	fi
	config_info=$(grep '"SUPPORT"' /var/www/SAT-CLOUDNVR/index.php)
	if [ -z "$config_info" ]; then
		sed -i -e '/includeOemGlobal/ {
		a\if ($_SESSION["user_language"] == "zh_TW")\ndefine("SUPPORT","使用說明");\nelse define("SUPPORT","'"$1"'");\nif ($_SESSION["user_language"] == "zh_TW")\ndefine("SUP_PAGE","'"$chtPage"'");\nelse define("SUP_PAGE","'"$2"'");\n
		}' /var/www/SAT-CLOUDNVR/index.php
	fi
    config_info=$(grep '<!--jinho fix-->' /var/www/SAT-CLOUDNVR/index.php)
    if [ -z "$config_info" ]; then
      sed -i -e ' 
    /<li><a id="menu_share" class="navi_link open_page bottom_menu" href="shared_matrix.php?mode=<?php echo $mode;?>" onClick="toActive(this.id);"><?php echo _("Share");?><\/a><\/li>/ {
    c\
    <li><a id="menu_share" class="navi_link open_page bottom_menu" href="shared_matrix.php?mode=<?php echo $mode;?>" onClick="toActive(this.id);"><?php echo _("Share");?><\/a><\/li>\n<!--jinho fix-->\n<li><a id="menu_share" class="navi_link open_page bottom_menu" href="<?php echo SUP_PAGE;?>" onClick="toActive(this.id);"><?php echo SUPPORT;?><\/a><\/li>
    }' /var/www/SAT-CLOUDNVR/index.php
      echo "add Support page to index.php"
    fi
}
setIvedaGUI(){
#####iveda new UI
  cd Iveda/
  sh patch.sh
  cd ..
  if [ "$1" = "V04" ]; then
  sed -i -e 's|zee.ivedaxpress.com|sentirvietnam.vn|g' /var/www/SAT-CLOUDNVR/js/faq.json 
  sed -i -e 's|itunes.apple.com/us/app/ivedaxpress/id740328884?mt=8|itunes.apple.com/tw/app/sentir-vn/id1031486220?mt=8|g' /var/www/SAT-CLOUDNVR/js/faq.json
  sed -i -e 's|play.google.com/store/apps/details?id=com.iveda.cloudnvr|play.google.com/store/apps/details?id=com.vn.cloudnvr|g' /var/www/SAT-CLOUDNVR/js/faq.json
  elif [ "$1" = "V03" ]; then
  #sed -i -e 's|zee.ivedaxpress.com|camera.vinaphone.vn|g' /var/www/SAT-CLOUDNVR/js/faq.json
  sed -i -e 's|itunes.apple.com/us/app/ivedaxpress/id740328884?mt=8|itunes.apple.com/vn/app/ivedaxpress/id1114416250?mt=8|g' /var/www/SAT-CLOUDNVR/js/faq.json
  #sed -i -e 's|play.google.com/store/apps/details?id=com.iveda.cloudnvr|???|g' /var/www/SAT-CLOUDNVR/js/faq.json
  elif [ "$1" = "X02" ]; then
  sed -i -e 's|zee.ivedaxpress.com|xpress.megasys.com.tw|g' /var/www/SAT-CLOUDNVR/js/faq.json
  sed -i -e 's|itunes.apple.com/us/app/ivedaxpress/id740328884?mt=8|itunes.apple.com/tw/app/megasysxpress/id931909110?mt=8|g' /var/www/SAT-CLOUDNVR/js/faq.json
  sed -i -e 's|play.google.com/store/apps/details?id=com.iveda.cloudnvr|play.google.com/store/apps/details?id=com.xpress1.cloudnvr|g' /var/www/SAT-CLOUDNVR/js/faq.json
  fi
}
#######################config site parameter#################################
megasys_enable=1    #megasys java jar
iveda_gui_enable=0
smtpauth_disable=1  #default set to 1 for iveda/pldt/local postfix LAN Email server
oemid=$(sed -n '/OEM_ID=/p' ../iSVW_install_scripts/install.conf | tail -c 6)
oemid=$(echo $oemid | head -c 4 | tail -c 3)
#if [ "${oemid}" = "V04" ]; then
oemstr=$(sed -n '/oem_id/p' /var/www/SAT-CLOUDNVR/include/index_title.php | tail -c 6) #check install file
if [ "${oemstr}" = "X02';" ]; then
  smtpauth_disable=1
  sslpem="xpress_megasys_com_tw.pem" 
  sslca="xpress_megasys_com_tw.ca.pem"
  cp SSL/X02/* .
  myPrintAlert "set X02 SSL and parameters."
  cp -avr solevision/cert/push_notify/* /var/www/SAT-CLOUDNVR/push_notify/
  cp solevision/cert/push_notify/PushSVI_I02_X02_apns_production.pem /var/www/SAT-CLOUDNVR/push_notify/PushSVI_ivedaxpress_megasysxpress_apns_production.pem
  cp solevision/cert/push_notify/PushSVI_I02_X02_apns_development.pem /var/www/SAT-CLOUDNVR/push_notify/PushSVI_ivedaxpress_megasysxpress_apns_development.pem
  cp solevision/cert/push_notify/PushSVI_I02_X02_apns_*.pem /var/www/SAT-CLOUDNVR/push_notify/
  myPrintInfo "New megasys X02 iOS apns SSL installed\n"
elif [ "${oemstr}" = "X01';" ]; then
  smtpauth_disable=1
  sslpem="server.pem" 
  sslca="ca.pem"  
  cp SSL/X01/* .
  myPrintAlert "set X01 SSL and parameters."
  #debug web URL deploy only on x01
  cp testip.php /var/www/SAT-CLOUDNVR/
elif [ "${oemstr}" = "T04';" ]; then
  smtpauth_disable=1
  sslpem="rpic.taipei.server.pem"
  sslca="rpic.taipei.ca.pem"
  cp SSL/T04/* .
  myPrintAlert "set T04 SSL and parameters."

elif [ "${oemstr}" = "T05';" ]; then
  smtpauth_disable=1
  sslpem="rpic.tycg.gov.tw.server.pem"
  sslca="rpic.ca.pem"
  cp SSL/T05/* .
  cp SSL/$sslca .
  myPrintAlert "set T05 SSL and parameters."
elif [ "${oemstr}" = "K01';" ]; then
  smtpauth_disable=1
  sslpem="kreac_kcg_gov_tw.server.pem"
  sslca="rpic.ca.pem"
  cp SSL/K01/* .
  cp SSL/$sslca .
  myPrintAlert "set ${oemid} SSL and parameters."
elif [ "${oemstr}" = "T06';" ]; then
  smtpauth_disable=1
  sslpem="workeye.pem"
  sslca="godaddy.ca.pem"
  cp SSL/T06/* .
  myPrintAlert "set ${oemid} SSL and parameters."
elif [ "${oemstr}" = "C13';" ]; then
  smtpauth_disable=1
  sslpem="engeye.chimei.com.tw.pem"
  sslca="engeye.chimei.com.tw.ca.pem"
  cp SSL/C13/* .
  myPrintAlert "set ${oemid} SSL and parameters."

elif [ "${oemstr}" = "Z02';" ]; then
  iveda_gui_enable=1   #iveda GUI
  smtpauth_disable=1  #default 0, set to 1 for internal Email server
  sslpem="zee.ivedaxpress.com.pem"
  sslca="zee.ivedaxpress.com.ca.pem"
  cp SSL/Z02/* .
  myPrintAlert "set Z02 SSL and parameters."
  cp ivedasol/SAT-CLOUDNVR/push_notify/PushSVI_I02_Z02_apns_development.pem /var/www/SAT-CLOUDNVR/push_notify/
  cp ivedasol/SAT-CLOUDNVR/push_notify/PushSVI_I02_Z02_apns_production.pem /var/www/SAT-CLOUDNVR/push_notify/
  myPrintInfo "New iveda Z02 iOS apns SSL installed\n" 
elif [ "${oemstr}" = "T03';" ]; then
  smtpauth_disable=1
  sslpem="test.ivedaxpress.com.pem"
  sslca="test.ivedaxpress.com.ca.pem"
  cp SSL/T03/* .
  myPrintAlert "set T03 SSL and parameters." 
elif [ "${oemstr}" = "P04';" ]; then
  smtpauth_disable=1  #default 0, set to 1 for internal Email server
  sslpem="server201606.pem"
  sslca="ca201606.pem"
  cp SSL/P04/* .
  myPrintAlert "set P04 SSL and parameters."
  cp p04/SAT-CLOUDNVR/push_notify/*.pem /var/www/SAT-CLOUDNVR/push_notify/
  cp p04/SAT-CLOUDNVR/push_notify/PushSVI_I02_P04_apns_production.pem /var/www/SAT-CLOUDNVR/push_notify/PushSVI_pldt_apns_production.pem
  myPrintInfo "New PLDT iOS apns SSL installed\n"
  #####pldt internal web server
  #route add -p 10.0.0.0 mask 255.0.0.0 172.16.31.194
  config_info=$(grep '172.16.31.193' /etc/network/interfaces)
  if [ -z "$config_info" ]; then #-z test if its empty string
     myPrintAlert "PLDT INTERNAL MAIL SERVER VNET is not existed!!\n"
  else
     myPrintAlert "PLDT INTERNAL MAIL SERVER VNET is added.\n"
  fi    
elif [ "${oemstr}" = "V03';" ]; then
  smtpauth_disable=0
  sslpem="camera_vinaphone_vn.pem" 
  sslca="camera_vinaphone_vn.ca.pem"
  cp SSL/V03/* .
  myPrintAlert "set V03 SSL and parameters."
  #new ios app
  cp v03/SAT-CLOUDNVR/push_notify/*.pem /var/www/SAT-CLOUDNVR/push_notify/
  myPrintInfo "New VNPT iOS apns SSL installed\n"
elif [ "${oemstr}" = "V04';" ]; then
  smtpauth_disable=1
  sslpem="sentirvietnam.vn.pem" 
  sslca="sentirvietnam.vn.ca.pem"
  cp SSL/V04/* .
  myPrintAlert "set V04 SSL and parameters."
  cp v04/SAT-CLOUDNVR/push_notify/*.pem /var/www/SAT-CLOUDNVR/push_notify/
  myPrintInfo "New SentirVN iOS apns SSL installed\n"     
elif [ "${oemstr}" = "J01';" ]; then
  smtpauth_disable=1  #default 0, set to 1 for internal Email server
  sslpem="japan.ivedaxpress.com.pem"
  sslca="japan.ivedaxpress.com.ca.pem"
  cp SSL/J01/* .
  myPrintAlert "set J01 SSL and parameters."
else
  myPrintAlert "ERROR!! NO PARAMETER SET in index_title.php!!\n  iSVW_install_scripts/install.conf used $oemid"
  sudo rm /var/lib/apt/lists/*
  sudo rm /etc/apt/sources.list.d/lighttpd.list 
  if [ -f "../iSVW_install_scripts/install.sh" ]; then
    sed -i -e 's|sudo.*sources.list.d\/lighttpd.list|echo skip|' ../iSVW_install_scripts/install.sh
    echo "remove old lighttpd setting from install.sh"
  fi
  myPrintAlert "please install Web Package."
  exit  
fi
#######################end of config site parameter#################################

if [ "${oemid}" = "T04" ]; then
  configVer=$(grep 'SVN_REVISION' /var/www/SAT-CLOUDNVR/include/global.php | tail -c 8 | head -c 4)
  configVerN=$(expr $configVer)
  if [ $configVerN  -lt 3662 ]; then
    #patch v2.2.1/2 and 180 days pkg, update support page link later
    cp -avr v221_d180/SAT-CLOUDNVR/* /var/www/SAT-CLOUDNVR
    #cp taipei/my_account.php /var/www/SAT-CLOUDNVR/     #disable pwd, @180d pkg
    sed -i -e 's|<input id="pwd-edit" class="full_size_input" type="password"|<input id="pwd-edit" class="full_size_input" disabled type="password"|' /var/www/SAT-CLOUDNVR/my_account.php
    sed -i -e 's|<input id="pwd_confirm-edit" class="full_size_input" type="password"|<input id="pwd_confirm-edit" class="full_size_input" disabled type="password"|' /var/www/SAT-CLOUDNVR/my_account.php 

  fi
  sh patch_rpic.sh T04   #$oemid  
elif [ "${oemid}" = "T05" ]; then
    sh patch_rpic.sh T05   #$oemid
elif [ "${oemid}" = "K01" ]; then
    sh patch_rpic.sh K01   #$oemid
elif [ "${oemid}" = "X02" ]; then
    sh patch_rpic.sh X02
elif [ "${oemid}" = "C13" ]; then
    sh patch_rpic.sh C13
else
    sh patch_rpic.sh RPIC
fi
####patch for LAN email server
sh patch_internalSMTP.sh $smtpauth_disable
#patch new android gcm API license
config=$(grep 'AIzaSyBUcLEFzkaYN5OKdea5pDT3SnaYSAxqKp4' /var/www/SAT-CLOUDNVR/push_notify/gcm.php)
if [ -z "$config" ]; then
#replace line with ">authString =" (.*)
sed -i -e 's|>authString =.*|>authString = \x27AIzaSyBUcLEFzkaYN5OKdea5pDT3SnaYSAxqKp4\x27;|' /var/www/SAT-CLOUDNVR/push_notify/gcm.php
fi
####patch depending on site
if [ "${oemid}" = "P04" ]; then
#  sh patchpldt.sh
  config_info=$(grep 'IvedaXpress Event Triggered' /var/www/SAT-CLOUDNVR/push_notify/push.php)
  if [ ! -z "$config_info" ]; then #-z test if its empty string
    config_tz=$(date | awk '{print $5}')
    sed -i -e 's/Motion Event detected at $local_date,/Motion Event detected at $local_date ('"$config_tz"'),/' /var/www/SAT-CLOUDNVR/push_notify/push.php
    myPrintInfo "notification email timezone updated $config_tz\n"
    #subject for PLDT
    sed -i -e 's/IvedaXpress Event Triggered/VideoMonitoring Event Triggered/' /var/www/SAT-CLOUDNVR/push_notify/push.php
    sed -i -e 's/IvedaXpress account/VideoMonitoring account/' /var/www/SAT-CLOUDNVR/push_notify/push.php
    sed -i -e 's/IvedaXpress Event Triggered/VideoMonitoring Event Triggered/' /var/www/SAT-CLOUDNVR/Mailer/mailer.php
    myPrintInfo "notification email subject updated to VideoMonitoring"
  fi
else
  #set timezone and timezone info in the notification email
  config_info=$(grep 'IvedaXpress Event Triggered' /var/www/SAT-CLOUDNVR/push_notify/push.php)
  if [ ! -z "$config_info" ]; then #-z test if its empty string
    config_tz=$(date | awk '{print $5}')
    #echo ${config_tz} 
    sed -i -e 's/Motion Event detected at $local_date,/Motion Event detected at $local_date ('"$config_tz"'),/' /var/www/SAT-CLOUDNVR/push_notify/push.php
    #for mesa server (MST)?  #for japan (JST)  #for PLDT (PHT)  #for xpress (CST)
    myPrintInfo "notification email timezone updated as $config_tz\n"
  fi
fi
webVer=$(grep 'SVN_REVISION' /var/www/SAT-CLOUDNVR/include/global.php | tail -c 8 | head -c 4)
webVerN=$(expr $webVer)
config_info=$(sudo /etc/init.d/lighttpd status | grep 'running')
if [ ! -z "$config_info" ]; then #-z test if its empty string
  myPrintAlert "stop web service before patching jar file"
  /etc/init.d/lighttpd stop
fi
if [ $webVerN -ge 3510  ]; then #auth enabled after v2.2.5 version 3621
#elif [ $webVerN -ge 3510  ]; then   #stream5544_enable=1 using new jar after v2.1.1
    if [ ${megasys_enable} -eq 1 ]; then
      cp solevision/cert/jar/*.jar /var/www/SAT-CLOUDNVR/jar/
    else
      cp ivedasol/SAT-CLOUDNVR/jar/*.jar /var/www/SAT-CLOUDNVR/jar/
    fi
      myPrintInfo "replaced player jar file for public stream port release\n"
elif [ $webVerN -le 3355  ]; then
    #default jar before v2.0.2
    if [ ${megasys_enable} -eq 1 ]; then
      cp solevision/cert/jar_v202/*.jar /var/www/SAT-CLOUDNVR/jar/
    else 
      cp ivedasol/SAT-CLOUDNVR/jar_v202/*.jar /var/www/SAT-CLOUDNVR/jar/
    fi
    myPrintInfo "replaced player jar files via tunnel release\n"
fi 

#####GUI setup for each site
if [ "${oemid}" = "V04" ]; then
  setIvedaGUI $oemid
  cp -avr v04/SAT-CLOUDNVR/* /var/www/SAT-CLOUDNVR/
  myPrintInfo "Set $oemid IvedaGUI title.png\n"
elif [ "${oemid}" = "V03" ]; then
  setIvedaGUI $oemid
  cp -avr v03/SAT-CLOUDNVR/* /var/www/SAT-CLOUDNVR/
  myPrintInfo "Set V03 gradient GUI\n"
elif [ "${oemid}" = "T03" ]; then
  setIvedaGUI $oemid
  #cp -avr Telmex/SAT-CLOUDNVR/* /var/www/SAT-CLOUDNVR/
  myPrintInfo "Set $oemid IvedaGUI title.png\n"  
elif [ "${oemid}" = "P04" ]; then  
  #####pldt new GUI
  #setIvedaGUI
  #cp -avr p04/SAT-CLOUDNVR/* /var/www/SAT-CLOUDNVR/
  #myPrintInfo "Set $oemid IvedaGUI title.png\n"
  sed -i -e '/.footer_logo_iveda {/,/top: 16px;/ s/width: 150px;/width: 200px;/' /var/www/SAT-CLOUDNVR/css/index_iveda.css
  cp -avr solevision/SAT-CLOUDNVR/* /var/www/SAT-CLOUDNVR/
  cp Iveda/SAT-CLOUDNVR/Support.php /var/www/SAT-CLOUDNVR/
  sed -i -e 's|zee.ivedaxpress.com|videomonitoring.pldtcloud.com|g' /var/www/SAT-CLOUDNVR/js/faq.json
  sed -i -e 's|itunes.apple.com/us/app/ivedaxpress/id740328884?mt=8|itunes.apple.com/TR/app/id908859882|g' /var/www/SAT-CLOUDNVR/js/faq.json
  sed -i -e 's|play.google.com/store/apps/details?id=com.iveda.cloudnvr|play.google.com/store/apps/details?id=com.pldt.cloudnvr|g' /var/www/SAT-CLOUDNVR/js/faq.json
elif [ "${oemid}" = "K01" -o "${oemid}" = "T04" -o "${oemid}" = "T05" -o "${oemid}" = "T06"  -o "${oemid}" = "C13" ]; then
  echo "GUI change made after patch_bug.sh"
elif [ ${iveda_gui_enable} -eq 1 ]; then #Z02
  setIvedaGUI $oemid
elif [ "${oemid}" = '"X02"' -o "${oemid}" = '"X01"' ]; then
  sed -i -e '/.footer_logo_iveda {/,/top: 16px;/ s/width: 150px;/width: 200px;/' /var/www/SAT-CLOUDNVR/css/index_iveda.css
  cp -avr solevision/SAT-CLOUDNVR/* /var/www/SAT-CLOUDNVR/
  #sed -i -e 's|xpress.megasys.com.tw|xpress.megasys.com.tw|g' /var/www/SAT-CLOUDNVR/js/faq.json
  sed -i -e 's|itunes.apple.com/us/app/ivedaxpress/id740328884?mt=8|itunes.apple.com/tw/app/megasysxpress/id931909110?mt=8|g' /var/www/SAT-CLOUDNVR/js/faq.json
  sed -i -e 's|play.google.com/store/apps/details?id=com.iveda.cloudnvr|play.google.com/store/apps/details?id=com.xpress1.cloudnvr|g' /var/www/SAT-CLOUDNVR/js/faq.json
else  #J01
  sed -i -e '/.footer_logo_iveda {/,/top: 16px;/ s/width: 150px;/width: 200px;/' /var/www/SAT-CLOUDNVR/css/index_iveda.css
  cp -avr solevision/SAT-CLOUDNVR/* /var/www/SAT-CLOUDNVR/  
fi

###set default image
  cp favicon.ico /var/www/SAT-CLOUDNVR/
  myPrintInfo "copy favicon.ico to www root"
  chmod 777 /var/www/SAT-CLOUDNVR/favicon.ico
#only set logo for first time installation
#if [ ! -f "/var/www/SAT-CLOUDNVR/images/config/product_logo.png" ]; then
  cp web_product_logo.png /var/www/SAT-CLOUDNVR/images/config/product_logo.png
  chmod 777 /var/www/SAT-CLOUDNVR/images/config/product_logo.png
  myPrintInfo "copy default product_logo to www"
#fi
#only set logo for first time installation
#if [ ! -f "/var/www/SAT-CLOUDNVR/images/config/company_logo.png" ]; then
  cp web_company_logo.png /var/www/SAT-CLOUDNVR/images/config/company_logo.png
  chmod 777 /var/www/SAT-CLOUDNVR/images/config/company_logo.png
  myPrintInfo "copy default company_logo to www"
#fi

####clean memcache for new Mailer (previous disgard events, new will queue####
#: ' to ' #comment out after v1.4.7 install
myPrintInfo "print out Memcache server info"
{
#echo flush_all
sleep 1
echo stats items
sleep 1
echo quit
} |telnet 127.0.0.1 11211
#restart memcached
#/etc/init.d/memcached restart

#check mount point
if [ ! -d "/media/videos" ]; then
  myPrintInfo "/media/videos does not exist for share storage\n"
  mkdir /media
  mkdir /media/videos
fi
config_info=$(grep '/media/videos' /etc/rc.local)
config_info1=$(grep '/media/videos' /etc/fstab)
if [ -z "$config_info" -a -z "$config_info1" ]; then #-z test if its empty string
  myPrintInfo "/media/videos does not mount to share storage in rc.local or fstab, please execute mountcheck.sh manually\n"
  #sudo sh mountcheck.sh   
fi

#check nas and mount point once on sunday, only need to install one web
cp checkNAS.sh /usr/local/lib/web_server_control/
config_info=$(grep '/usr/local/lib/web_server_control/checkNAS.sh' /etc/crontab)
if [ -z "$config_info" ]; then #-z test if its empty string
sed -i -e "\$a1 1 * * 0 root  sh /usr/local/lib/web_server_control/checkNAS.sh" /etc/crontab
myPrintInfo "Add mount point and NAS check to crontab"
fi    
#recycle web server in 30 days
config_info=$(grep '/var/log/lighttpd/' /etc/crontab)
if [ -z "$config_info" ]; then #-z test if its empty string
  echo "0 1     * * 0   root  find  /var/log/lighttpd/  -type f -mtime +30 -exec rm {} +" >> /etc/crontab
  myPrintInfo "write log recycle (30days) to /etc/crontab\n"
fi

#patch web root access
cp robots.txt /var/www/SAT-CLOUDNVR/
myPrintInfo "copy robot access file to www root"

#re-patch mailer
chmod 777  /var/www/SAT-CLOUDNVR/Mailer/configuration.php
myPrintInfo "chmod 777 to Mailer configuration.php"

######patch web bugs before original index.php update
if [ "${oemid}" = "T05" ]; then
	sh patch_bug.sh
  addMySupportPage "Manual" "ty/rpic_support.pdf"
elif [ "${oemid}" = "T04" ]; then
	sh patch_bug.sh
  addMySupportPage "Manual" "taipei/rpic_support.pdf"
elif [ "${oemid}" = "K01" ]; then
	sh patch_bug.sh java_web_upgrade_after3795
  addMySupportPage "Manual" "rpic/rpic_support.pdf"
  #K01 security check issue
  rm -rf /var/www/SAT-CLOUDNVR/download/
  rm -rf /var/www/SAT-CLOUDNVR/shadowbox-3.0.3/player.swf 
  
elif [ "${oemid}" = "T06" -o "${oemid}" = "X02" ]; then
	sh patch_bug.sh java_web_upgrade_after3795
	addMySupportPage "Support" "Support.php" "support.pdf"
elif [ "${oemid}" = "C13" ]; then
	sh patch_bug.sh java_web_upgrade_after3795
	addMySupportPage "Support" "Support.php" "support.pdf"
elif [ "${oemid}" = "X01" ]; then
	sh patch_bug.sh java_web_upgrade_after3795
  addMySupportPage "Support" "Support.php"
else
	sh patch_bug.sh
  addMySupportPage "Support" "Support.php"
fi
myPrintInfo "Add Support link to index.php after patch bug"

#patch chrome support
cp -avr rpic/images/ /var/www/SAT-CLOUDNVR/
sed -i -e 's|In the address bar, enter <input class="autoselect" type="text" value="chrome:\/\/flags\/#enable-npapi" \/>, then click enable button.|Install Extension IE Tab<input class="autoselect" type="text" value="https:\/\/chrome.google.com\/webstore\/detail\/ie-tab\/hehijbfgiekmjfkfjpbkbammjbdenadd?hl=zh-TW" \/>, enable as IE10 force standard mode.|' /var/www/SAT-CLOUDNVR/index.php

#patch TCP/PHP
sh patch_sysctl.sh
sh patch_phpfpm.sh

myPrintInfo "start to update SSL files"
config_info=$(grep 'FORCE_HTTPS="false"' ../iSVW_install_scripts/install.conf)
if [ -z "$config_info" ]; then #empty = https only
  sh ssl-lighttpd.sh $sslpem $sslca  #will restart lighttpd service /etc/init.d/lighttpd restart
  myPrintInfo "lighttpd service restarted with SSL"
else
  myPrintInfo "lighttpd service restarted"
  /etc/init.d/lighttpd restart
fi
config_infol=$(dpkg -l | grep lighttpd | awk '{print $3}' | head -c 6)
if [ "$config_infol" = "1.4.28" ]; then
  echo "Upgrade lighttpd: sh ssl-lighttpd_upgrade.sh"
  #sudo sh ssl-lighttpd_upgrade.sh
fi
/etc/init.d/web_server_control restart #restart web control
myPrintInfo "web_server_control service restarted"
