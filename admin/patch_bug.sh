#qlync db utf8 setting error for first time,qlync will be fixed in v321
#sed -i -e 's|utf-8|UTF8|' /var/www/qlync_admin/doc/mysql_connect.php
  adminVer=$(grep -E -m1 'ver_log' /var/www/qlync_admin/html/webmaster/version_content.php | head -c 17 | tail -c 3) #100 or 99-
  adminVerN=$(expr $adminVer)
  if [ "$adminVer" -eq "$adminVer" ]; then
    echo "Current Version is $adminVerN !!\n\n"
    #if  [ $adminVerN -ge 103  ]; then
    #  echo "Does not patch after v103" 
    #  exit
    #fi
  else #<99
    adminVer=$(grep -E -m1 'ver_log' /var/www/qlync_admin/html/webmaster/version_content.php | head -c 16 | tail -c 2)
    adminVerN=$(expr $adminVer)
  fi
  echo "current Admin version is $adminVerN"
# /html/member
oemid=$(sed -n '/OEM_ID=/p' ../isat_partner/install.conf | tail -c 6) #check config "X02"
if [ "${oemid}" = '"T04"' -o "${oemid}" = '"T05"' -o "${oemid}" = '"K01"'  ]; then
	echo "Skip turtorial_content.php"
else
	#run if is not rpic, or before rpic
	echo "Update turtorial_content"
	if [ $adminVerN -ge 87  ]; then
		cp v225/turtorial_content.php /var/www/qlync_admin/html/faq/
	else
		cp v202/turtorial_content.php /var/www/qlync_admin/html/faq/
	fi
fi
if [ $adminVerN -ge 101  ]; then
  cp v321/member/* /var/www/qlync_admin/html/member/
  cp v303/member/maintain.php /var/www/qlync_admin/html/member/
  echo "patch maintain button error and update fields"
  echo "Patch QXP-335 SQL injection risk after v101"
  cp v303/member/*_list.php /var/www/qlync_admin/html/member/
  #cp v303/member/online_list.php /var/www/qlync_admin/html/member/
  echo "patch account_list/online_list with playback 5544 page"
  cp v321/common/reservation_check.php /var/www/qlync_admin/html/common/
  echo "patch wedding reservation issue"
  php scidupdate.php
	echo "patch scid table update"
elif [ $adminVerN -ge 99  ]; then
  cp v303/member/* /var/www/qlync_admin/html/member/
  echo "patch online_list with admin liveview 5544"
  echo "patch account_list with playback 5544 page"
  echo "patch maintain button error and update fields"
  echo "Patch QXP-335 SQL injection risk after v99"
  php scidupdate.php
	echo "patch scid table update"
elif [ $adminVerN -ge 88  ]; then
  cp v225/member/* /var/www/qlync_admin/html/member/
  #cp v225/member/account_list.php /var/www/qlync_admin/html/member/
  echo "patch account_list with playback 5544 page" 
  #cp v225/member/online_list.php /var/www/qlync_admin/html/member/
  echo "patch online_list with admin liveview 5544"
  cp v202/member/maintain.php /var/www/qlync_admin/html/member/
  cp v202/member/login.php /var/www/qlync_admin/html/member/
  echo "patch maintain button error."
else
  #after version 88
  cp v202/member/* /var/www/qlync_admin/html/member/
  #cp v202/member/online_list.php /var/www/qlync_admin/html/member/
  echo "patch online_list search display issue"
  #patch QXP-247 search MAC display error
  #cp v202/member/account_list.php /var/www/qlync_admin/html/member/
  echo "patch QXP-247"
  #cp v202/member/maintain.php /var/www/qlync_admin/html/member/
  echo "patch maintain button error."
  #after v72
  #cp v202/member/login.php /var/www/qlync_admin/html/member/
  echo "Patch QXP-335 SQL injection risk after v2.0.2" 
fi

# /upload, /public
if [ $adminVerN -ge 102  ]; then
  cp -avr v321/upload/*.php /var/www/qlync_admin/html/upload/
  echo "patch godwatch admin page utf8 issue"
  cp -avr v303/public/*.php /var/www/qlync_admin/html/public/
  echo "patch for mobile page layout @/html/public"
elif [ $adminVerN -ge 99  ]; then
  cp -avr v303/public/*.php /var/www/qlync_admin/html/public/
  echo "patch for mobile page layout @/html/public"
  cp -avr v303/upload/*.php /var/www/qlync_admin/html/upload/  
fi

#push.php
if [ $adminVerN -ge 95  ]; then
  cp v237/push.php /var/www/qlync_admin/html/plug-in/notification/
  echo "ver95 patch to_group message sending"
fi
# /html/server
if [ $adminVerN -ge 99  ]; then  #after version 98 godwatch
  cp v303/server/* /var/www/qlync_admin/html/server/
  cp v231/server/*log.php /var/www/qlync_admin/html/server
  echo "after v2.3.x patch Auto HA criteria (cpu0 and mem100) @tun/str"
elif [ $adminVerN -ge 89  ]; then  #after version 89, region
  cp v231/server/* /var/www/qlync_admin/html/server/
elif [ $adminVerN -ge 87  ]; then  #after version 87 v2.2.1
  cp v225/server/* /var/www/qlync_admin/html/server/ #minor update for v2.2.5
  cp v202/server/*log.php /var/www/qlync_admin/html/server
  echo "patch Auto HA criteria (cpu0 and mem100) @tun/str"
elif [ $adminVerN -ge 72  ]; then #after v2.0.2
  cp v202/server/* /var/www/qlync_admin/html/server/
fi
echo "patch $adminVer server/vls_server.php...etc"

#scid sync every day
if [ $adminVerN -ge 99  ]; then
config_info=$(grep 'scidupdate.php' /etc/crontab)
if [ -z "$config_info" ]; then #-z test if its empty string
  if [ -f "scidupdate.php" ]; then
  curPATH=$(readlink -f scidupdate.php)
	sed -i -e "\$a4 4 * * * root /usr/bin/php5 \x22$curPATH\x22" /etc/crontab
	echo "backup file between master/slave admin every day"
  fi 
fi
fi
#ha file sync every sunday
config_info=$(grep 'hafilesync.php' /etc/crontab)
if [ -z "$config_info" ]; then #-z test if its empty string
  if [ -f "hafilesync.php" ]; then
  curPATH=$(readlink -f hafilesync.php)
	sed -i -e "\$a3 3 * * 0 root /usr/bin/php5 \x22$curPATH\x22 /var/www/qlync_admin" /etc/crontab
	echo "sync file between master/slave admin every sunday"
  fi
fi

cp v202/server/web_log.php /var/www/qlync_admin/html/server/
echo "patch Auto HA criteria (cpu0 and mem100) for 2 minutes delay @web"
cp v202/server/partner_server.php /var/www/qlync_admin/html/server/
echo "patch admin version info on partner_server.php"


#patch mac_check to provide service pkg information
  cp v303/mac_check.php /var/www/qlync_admin/html/license/
  echo "patch camera realtime status on mac_check.php"
#if [ $adminVerN -ge 88  ]; then  #after version 88
#  cp v225/mac_check.php /var/www/qlync_admin/html/license/
#  echo "patch mac check features on mac_check.php"
#else
#  cp v202/mac_check.php /var/www/qlync_admin/html/license/
#  echo "patch mac check features on mac_check.php"   
#fi


  #patch QXP-165 New Firmware feature will not automatically updated
  cp v202/auto_model_list_feature.php /var/www/qlync_admin/html/common/
  echo "patch QXP-165 auto FW model list update on auto_model_list_feature.php"
  #patch admin info
  cp v202/login_log.php /var/www/qlync_admin/html/admin/
  echo "patch QXP-318 login_log.php"
  cp v202/menu_update.php /var/www/qlync_admin/html/common/
  echo "patch QXP-86 menu update order issue menu_update.php"
  #patch QXP-318  admin login log display and admin sys Log overloading limit 200
  config_info=$(grep 'order by ID desc limit 200' /var/www/qlync_admin/html/log/log.php) 
  if [ -z "$config_info" ]; then   #-z test if its empty string
    sed -i -e 's/order by ID desc\"/order by ID desc limit 200\"/' /var/www/qlync_admin/html/log/log.php
    echo "patch sys log display loading @log.php\n"
  fi


#delete uncessary files
find /var/www/qlync_admin/html/ -name "*.swp" -type f -delete
find /var/www/qlync_admin/html/ -name "*.xls" -type f -delete
rm -rf /var/www/qlync_admin/html/member/*.txt
rm -rf /var/www/qlync_admin/html/log/201407*
rm -rf /var/www/qlync_admin/html/license/doc/*
rm -rf /var/www/qlync_admin/html/fw/*.txt
rm -rf /var/www/qlync_admin/html/temp/*
rm -rf /var/www/qlync_admin/html/webmaster/rt.sql*
rm -rf /var/www/qlync_admin/html/common/device_update.log
rm -rf /var/www/qlync_admin/html/common/log

#patch Email test to return result
config_info=$(grep "echo \"<script>document.write('<h2>Email test:" /var/www/qlync_admin/html/webmaster/oem.php)
if [ -z "$config_info" ]; then
  #echo "<script>document.write('<h2>Email test: (send to ".$receiver." ) ".$content_test_email['status']."  ".$content_test_email['error_msg']."</h2>');</script>";
  sed -i -e '/\$content_test_email=json_decode(\$result,true);/ a\echo "<script>document.write(\x27<h2>Email test: (send to ".$receiver." ) ".$content_test_email[\x27status\x27]."  ".$content_test_email[\x27error_msg\x27]."</h2>\x27);</script>";'  /var/www/qlync_admin/html/webmaster/oem.php
  echo "patch Email alert test on admin oem page\n"
fi

#patch HA function limited to god admin
#config_info=$(grep 'ID_admin_qlync' /var/www/qlync_admin/html/server/vlc_server.php)
config_info=$(grep '_SESSION\["ID_admin_qlync"]) echo' /var/www/qlync_admin/html/server/isat_server.php)
if [ -z "$config_info" ]; then
#sed -i -e '/echo \"<input type=submit class=btn_4 value=\x27\"._(\"switch\").\"\x27>\\n\";/ i\if(\$_SESSION[\"ID_admin_qlync\"]) ' /var/www/qlync_admin/html/server/isat_server.php 
sed -i -e 's/echo \"<input type=submit class=btn_4 value=\x27\"._(\"switch\").\"\x27>\\n\";/if(\$_SESSION[\"ID_admin_qlync\"]) echo \"<input type=submit class=btn_4 value=\x27\"._(\"switch\").\"\x27>\\n\";/' /var/www/qlync_admin/html/server/isat_server.php
echo "patch HA policy for god admin only @isat_server.php\n"
fi

#remove HR from admin server display 
#config_info=$(grep '"<HR>"' /var/www/qlync_admin/html/server/isat_server.php)
#config_info=$(grep '"<HR>"' /var/www/qlync_admin/html/server/web_server.php) 
#config_info=$(grep '"<HR>\\n"' /var/www/qlync_admin/html/server/partner_server.php)
sed -i -e 's/echo "<BR>";/echo "";/' /var/www/qlync_admin/html/server/isat_server.php
sed -i -e 's/echo "<HR>";/echo "";/' /var/www/qlync_admin/html/server/isat_server.php
echo "remove HR BR display @isat_server.php\n"
sed -i -e 's/echo "<BR>";/echo "";/' /var/www/qlync_admin/html/server/web_server.php
sed -i -e 's/echo "<HR>";/echo "";/' /var/www/qlync_admin/html/server/web_server.php
echo "remove HR BR display @web_server.php\n"
sed -i -e 's/echo "<BR>";/echo "";/' /var/www/qlync_admin/html/server/partner_server.php
sed -i -e 's/echo "<HR>";/echo "";/' /var/www/qlync_admin/html/server/partner_server.php
sed -i -e 's/echo "<HR>\\n";/echo "";/' /var/www/qlync_admin/html/server/partner_server.php
echo "remove HR BR display @partner_server.php\n"

config_info=$(grep "Pragma: no-cache" /var/www/qlync_admin/header.php)
if [ -z "$config_info" ]; then
sed -i -e '/^include_once("parameter.php");/a header(\x27Cache-Control: no-store, no-cache, must-revalidate, private\x27);header(\x27Pragma: no-cache\x27);' /var/www/qlync_admin/header.php || Exit
fi

cp -avr v303/locale/ /var/www/qlync_admin/
echo "update TW locale."
cp -avr v321/log/ /var/www/qlync_admin/html/