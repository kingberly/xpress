java_web_upgrade_after3795=0
if [ "$1" = "java_web_upgrade_after3795" ]; then
java_web_upgrade_after3795=1
fi

#Undefined index: HTTPS in /var/www/SAT-CLOUDNVR/index.php on line 12/13
#L12==>if (FORCE_HTTPS && !isset($_SERVER['HTTPS']) ) {

config_info=$(grep 'G09' /var/www/SAT-CLOUDNVR/include/oem_id.php)
#oem_id_to_host /var/www/SAT-CLOUDNVR/include/oem_id.php
if [ -z "$config_info" ]; then
#'X02' => ,'S08' ,'S09' => 'xpress.megasys.com.tw','G09' => 'www.godwatch.cloud',
sed -i -e '/X02\x27 =>/a\                \x22G09\x22 => \x22www.godwatch.cloud\x22,\n                \x22S08\x22 => \x22xpress.megasys.com.tw\x22,\n                \x22S09\x22 => \x22xpress.megasys.com.tw\x22,' /var/www/SAT-CLOUDNVR/include/oem_id.php
#sed -i -e '/X02\x27 =>/a\                \x22G09\x22 => \x22www.godwatch.cloud\x22,' /var/www/SAT-CLOUDNVR/include/oem_id.php
#sed -i -e '/X02\x27 =>/a\                \x22S08\x22 => \x22xpress.megasys.com.tw\x22,' /var/www/SAT-CLOUDNVR/include/oem_id.php
#sed -i -e '/X02\x27 =>/a\                \x22S09\x22 => \x22xpress.megasys.com.tw\x22,' /var/www/SAT-CLOUDNVR/include/oem_id.php
fi

#eric deploy VM issue 
sudo sed -i -e '/192.168.1.99/d'  /etc/hosts

configVer=$(grep 'SVN_REVISION' /var/www/SAT-CLOUDNVR/include/global.php | tail -c 8 | head -c 4)
configVerN=$(expr $configVer)

#config_info=$(grep '.3818' /var/www/SAT-CLOUDNVR/include/global.php)
#if [ ! -z "$config_info" ]; then
if [ $configVerN  -eq 3818 ]; then
  chmod 755 /var/www/jf2/assets/misc/*
fi

#patch godwatch camera license upload issue after v2.2.1 before v2.3.x (3662)
if [ $configVerN  -lt 3662 ]; then
  cp v221_d180/SAT-CLOUDNVR/manage/include/rtmpd.php /var/www/SAT-CLOUDNVR/manage/include/
fi

#config_info=$(grep '.3795' /var/www/SAT-CLOUDNVR/include/global.php)
#if [ ! -z "$config_info" ]; then
if [ $configVerN  -eq 3795 ]; then
  sed -i -e 's|define(\x27FILE_SERVER_ENABLED\x27, true);|define(\x27FILE_SERVER_ENABLED\x27, false);|' /var/www/SAT-CLOUDNVR/include/global.php
 cp v302/ImportEncapsoluteCodeOrder.php /var/www/SAT-CLOUDNVR/manage/include/
 echo "patch camera license upload error"
fi
#patch QXP-390, HA fail issue (line443)
#grep "if (\$move_count < 0)" /var/www/SAT-CLOUDNVR/include/streamserver.php
sed -i -e 's|if ($move_count < 0)|if ($move_count <= 0)|' /var/www/SAT-CLOUDNVR/include/streamserver.php
#patch google js link for security issue
  sed -i -e 's/http:\/\/www.google.com\/recaptcha\/api\/js\/recaptcha_ajax.js/\/\/www.google.com\/recaptcha\/api\/js\/recaptcha_ajax.js/'  /var/www/SAT-CLOUDNVR/password_recovery.php
  sed -i -e 's/http:\/\/www.google.com\/recaptcha\/api\/js\/recaptcha_ajax.js/\/\/www.google.com\/recaptcha\/api\/js\/recaptcha_ajax.js/'  /var/www/SAT-CLOUDNVR/user_register.php
  sed -i -e 's/http:\/\/www.google.com\/recaptcha\/api\/js\/recaptcha_ajax.js/\/\/www.google.com\/recaptcha\/api\/js\/recaptcha_ajax.js/'  /var/www/SAT-CLOUDNVR/user_register_for_router.php
  echo "patch to remove http for recaptcha_ajax.js"

#patch QXP-356, video file is not in timestamp order
sed -i -e 's|return $data_db->QueryRecordDataArray($table, $condition, $columns, \x27\x27, $params)|return $data_db->QueryRecordDataArray($table, $condition, $columns, \x27order by start\x27, $params)|' /var/www/SAT-CLOUDNVR/backstage_recording_list.php

#patch QXP-207
config_info=$(grep "if (\$os != '')" /var/www/SAT-CLOUDNVR/contact_us.php)
if [ -z "$config_info" ]; then
  sed -i -e 's/include("include\/config\/contact_\$os.html");/if (\$os != \x27\x27) include("include\/config\/contact_\$os.html");/' /var/www/SAT-CLOUDNVR/contact_us.php
  echo "patch QXP-207 include(include/config/contact_.html): failed to open stream at contact_us.php\n"
  #sudo cp /var/www/SAT-CLOUDNVR/include/config/contact_android.html /var/www/SAT-CLOUDNVR/include/config/contact_windows.html
fi
#patch QXP-251
config_info=$(grep 'Congradulation' /var/www/SAT-CLOUDNVR/include/mail_class.php)
if [ ! -z "$config_info" ]; then
  sed -i -e 's/Congradulation/Congratulation/' /var/www/SAT-CLOUDNVR/include/mail_class.php || Exit
  sed -i -e 's/Congradulation/Congratulation/' /var/www/SAT-CLOUDNVR/manage/manage_oem.php || Exit
  echo "patch QXP-251 Test Email content typo Congratulation at mail_class.ph and manage_oem.php\n"
fi
#patch QXP-277 (/["']/) => /["'\[+&#]/g
config_info=$(grep "(/\[\"'\]/)" /var/www/SAT-CLOUDNVR/js/device_common.js)
if [ -z "$config_info" ]; then
  #sed -i -e 's/\/["\x27]\//\/["\x27\[+\&#]\/g/' /var/www/SAT-CLOUDNVR/js/device_common.js
  cp v151/device_common.js /var/www/SAT-CLOUDNVR/js/ 
  echo "patch QXP-277 camera name special characters filtered at device_common.js\n"
fi

#patch v2.3.4 only
#config_info=$(grep '.3682' /var/www/SAT-CLOUDNVR/include/global.php)
#if [ ! -z "$config_info" ]; then
if [ $configVerN  -eq 3682 ]; then
  cp -avr v234/SAT-CLOUDNVR/* /var/www/SAT-CLOUDNVR/
  echo "patch v2.3.4 QXP-339	app event is not existed issue\n"
fi                                                                  
#patch v2.2.2 only
#config_info=$(grep '.3544' /var/www/SAT-CLOUDNVR/include/global.php)
#if [ ! -z "$config_info" ]; then
if [ $configVerN  -eq 3544 ]; then
  cp -avr v222/SAT-CLOUDNVR/* /var/www/SAT-CLOUDNVR/
  sed -i -e 's|$memcache = &GetMemcacheInstance();|$memcache = GetMemcacheInstance();|' /var/www/SAT-CLOUDNVR/push_notify/push.php
  echo "patch v2.2.2 app notification, forget pwd feature\n"
fi
#patch v2.2.3 only
#config_info=$(grep '.3597' /var/www/SAT-CLOUDNVR/include/global.php)
#if [ ! -z "$config_info" ]; then
if [ $configVerN  -eq 3597 ]; then
  sed -i -e 's|SendEmailForIveda(|//SendEmailForIveda(|' /var/www/SAT-CLOUDNVR/push_notify/push.php
  echo "patch v2.2.4 QXP-316 while running more than 95 cameras (event enabled), web server will crash\n"
  sed -i -e 's|/dev/random|/dev/urandom|' /var/www/SAT-CLOUDNVR/include/utility.php
  echo "patch v2.2.4 QXP-323 Takes more than 1 minute for Forgot password page on web\n"
fi
#config_info=$(grep '.3621' /var/www/SAT-CLOUDNVR/include/global.php)
#if [ ! -z "$config_info" ]; then
if [ $configVerN  -eq 3621 ]; then
  sed -i -e 's|UpdateSeriesNumberAndGetPassword($mac_addr, $sid, $uid, $cid, $pid, $activated_code|UpdateSeriesNumberAndGetPassword($mac_addr, $sid, $uid, $cid, $pid, $activated_code=""|' /var/www/SAT-CLOUDNVR/include/license_db_function.php
  echo "patch QXP-324 web error LicenseDBFunction\n"
fi
#config_info=$(grep '.3662' /var/www/SAT-CLOUDNVR/include/global.php)
#if [ ! -z "$config_info" ]; then
if [ $configVerN  -eq 3662 ]; then
  sed -i -e 's|include_once(\x27include/config/uid.php\x27);|include_once(\x27config/uid.php\x27);|' /var/www/SAT-CLOUDNVR/include/db_function.php
  echo "patch QXP-327\n"
  #sudo /etc/init.d/web_server_control restart
fi
#patch QXP-214, in v2.2.2 patch, backstage_user_register.php is updated, move patch after
config_info=$(grep "QXP-214" /var/www/SAT-CLOUDNVR/backstage_user_register.php)
if [ -z "$config_info" ]; then
  sed -i -e '/\/\/ Set default values/{
i\
\/\/jinho fix QXP-214  
i\
        if (!filter_var($_GET["reg_email"], FILTER_VALIDATE_EMAIL)) throw new Exception( "Please enter a valid email address." );
i\
\/\/end fix QXP-214
  }' /var/www/SAT-CLOUDNVR/backstage_user_register.php
  sed -i 's/\r//g' /var/www/SAT-CLOUDNVR/backstage_user_register.php
  echo "patch QXP-214 Email format check in creating user at backstage_user_register.php\n"  
fi
#php security patch => header('Cache-Control: no-store, no-cache, must-revalidate, private');header('Pragma: no-cache');
#awk 'sub(/^M/,"");1' /var/www/SAT-CLOUDNVR/user_register_resend.php
sed -i -e 's|header(\x27Cache-Control: no-cache, must-revalidate\x27);|header(\x27Cache-Control: no-store, no-cache, must-revalidate, private\x27);header(\x27Pragma: no-cache\x27);|g' /var/www/SAT-CLOUDNVR/backstage_user_register.php
config_info=$(grep "Pragma: no-cache" /var/www/SAT-CLOUDNVR/include/index_title.php)
if [ -z "$config_info" ]; then
sed -i -e '/^<?php/a header(\x27Cache-Control: no-store, no-cache, must-revalidate, private\x27);header(\x27Pragma: no-cache\x27);' /var/www/SAT-CLOUDNVR/include/index_title.php || Exit
  #patch v2.0.2 only
  #config_info=$(grep '.3355' /var/www/SAT-CLOUDNVR/include/global.php)
  #if [ ! -z "$config_info" ]; then
  if [ $configVerN  -eq 3355 ]; then
    sed -i -e 's|if (!array_key_exists|session_start();if (!array_key_exists|' /var/www/SAT-CLOUDNVR/include/index_title.php
    echo "patch v2.0.2 QXP-195	web service will generate FastCGI-stderr error log\n"
  fi                                                                  
fi
config_info=$(grep "Pragma: no-cache" /var/www/SAT-CLOUDNVR/logout.php)
if [ -z "$config_info" ]; then
sed -i -e '/^include_once( \x22include\/user_function.php\x22 );/a header(\x27Cache-Control: no-store, no-cache, must-revalidate, private\x27);header(\x27Pragma: no-cache\x27);' /var/www/SAT-CLOUDNVR/logout.php || Exit
fi
config_info=$(grep "Pragma: no-cache" /var/www/SAT-CLOUDNVR/password_recovery.php)
if [ -z "$config_info" ]; then
sed -i -e '/^include_once( \x22.\/include\/global.php\x22 );/a header(\x27Cache-Control: no-store, no-cache, must-revalidate, private\x27);header(\x27Pragma: no-cache\x27);' /var/www/SAT-CLOUDNVR/password_recovery.php || Exit
fi
config_info=$(grep "Pragma: no-cache" /var/www/SAT-CLOUDNVR/js/translatejs.php)
if [ -z "$config_info" ]; then
sed -i -e '/^include(\x27..\/include\/global.php\x27);/a header(\x27Cache-Control: no-store, no-cache, must-revalidate, private\x27);header(\x27Pragma: no-cache\x27);' /var/www/SAT-CLOUDNVR/js/translatejs.php || Exit
fi
#patch auto complete
sed -i -e 's|<input id="pwd_confirm-edit" class="form-control" type="password" size="20" maxlength="32" \/>|<input id="pwd_confirm-edit" class="form-control" type="password" size="20" maxlength="32" AUTOCOMPLETE=\x27OFF\x27\/>|' /var/www/SAT-CLOUDNVR/user_register.php || Exit
sed -i -e 's|<input id="pwd-edit" class="form-control" type="password" size="20" maxlength="32" \/>|<input id="pwd-edit" class="form-control" type="password" size="20" maxlength="32" AUTOCOMPLETE=\x27OFF\x27\/>|' /var/www/SAT-CLOUDNVR/user_register.php || Exit
#added index.php after <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
#<META HTTP-EQUIV='Pragma' CONTENT='no-cache'>
#<META HTTP-EQUIV='Cache-Control' CONTENT='no-cache'> 

#special for v3.0.3 after java applet web server
if [ $java_web_upgrade_after3795 -eq 1 ]; then
#if [ $configVerN  -ge 3795 ]; then
  cd v322
  sudo sh patch.sh
  cd ..
#fi
fi
