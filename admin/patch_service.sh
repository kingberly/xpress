myPrintAlert(){
  echo "\\033[31m$1\\033[0m"
}
myPrintInfo(){
  echo "\\033[92m$1\\033[0m"
}

if [ "$1" = "restore" ]; then
  cp ../plugin_service/admin.php /var/www/qlync_admin/plugin/service/
  cp v231/server/vlc_server.php /var/www/qlync_admin/html/server/
  cp v225/mac_check.php /var/www/qlync_admin/html/license/
  echo "restore vlc, mac_check, service/admin.php"
  exit
fi

if [ -d "/var/www/qlync_admin/plugin/service/upload/" ]; then
  chmod 777 /var/www/qlync_admin/plugin/service/upload/
  myPrintInfo "set plugin service upload folder permission"
else 
  mkdir /var/www/qlync_admin/plugin/service/upload/
  chmod 777 /var/www/qlync_admin/plugin/service/upload/
  myPrintAlert "create plugin service upload folder"  
fi


config_info=$(sed -n '/$oem=/p' /var/www/qlync_admin/doc/config.php | tail -c 7)
oemid=$(echo $config_info | head -c 5) #"X02"
p1="HD 5 fps"
p2="VGA 5 fps"
p3="QVGA 5 fps"
if [ "${oemid}" = '"X02"' ]; then
  p1="HD w/Audio"
  p2="VGA 256Kbps"
  p3="IvedaMobile"
  	 sed -i -e 's|<option value="1" <?php if($days==\x271\x27) echo \x27selected\x27; ?>>1<\/option>|<option value="180" <?php if (($days==\x271\x27) or ($days==\x27\x27)) echo \x27selected\x27; ?>>180<\/option>|' /var/www/qlync_admin/plugin/service/admin.php
  	 sed -i -e 's|<option value="1" <?php if($days==\x271\x27) echo \x27selected\x27; ?>>1<\/option>|<option value="180" <?php if (($days==\x271\x27) or ($days==\x27\x27)) echo \x27selected\x27; ?>>180<\/option>|' /var/www/qlync_admin/plugin/service/upload.php
elif [ "${oemid}" = '"X01"' ]; then
  p1="HD w/Audio"
  p2="VGA 256Kbps"
  p3="QVGA"
elif [ "${oemid}" = '"V04"' -o "${oemid}" = '"V03"' ]; then
  #p1="HD 512Kbps 15fps"
  #p2="VGA 256Kbps 10fps"
  #p3="IvedaMobile"
  p1="HD 512Kbps"
  p2="VGA 256Kbps"
  p3=""
elif [ "${oemid}" = '"T04"' -o "${oemid}" = '"T05"' -o "${oemid}" = '"K01"' -o "${oemid}" = '"T06"' ]; then
  p1="HD 512Kbps"
  p2="HVGAW 256Kbps"
  p3="IvedaMobile"
  myPrintInfo "Add and default setting as 180d Profile1 to upload/admin.php"
  if [ -f "/var/www/qlync_admin/plugin/service/admin.php" ]; then
  	 sed -i -e 's|if(($days==\x277\x27)or($days==\x27\x27)) echo \x27selected\x27;|if(($days==\x277\x27)) echo \x27selected\x27;|' /var/www/qlync_admin/plugin/service/admin.php
  	 sed -i -e 's|<option value="1" <?php if($days==\x271\x27) echo \x27selected\x27; ?>>1<\/option>|<option value="180" <?php if (($days==\x271\x27) or ($days==\x27\x27)) echo \x27selected\x27; ?>>180<\/option>|' /var/www/qlync_admin/plugin/service/admin.php
  	 sed -i -e 's|if(($resolution==\x271\x27)or ($resolution==\x27\x27)) echo \x27selected\x27;|if(($resolution==\x271\x27)) echo \x27selected\x27;|' /var/www/qlync_admin/plugin/service/admin.php
  	 sed -i -e 's|if($resolution==\x270\x27) echo \x27selected\x27;|if(($resolution==\x270\x27)or ($resolution==\x27\x27)) echo \x27selected\x27;|' /var/www/qlync_admin/plugin/service/admin.php 
  fi
  if [ -f "/var/www/qlync_admin/plugin/service/upload.php" ]; then
  	 sed -i -e 's|if(($days==\x277\x27)or($days==\x27\x27)) echo \x27selected\x27;|if(($days==\x277\x27)) echo \x27selected\x27;|' /var/www/qlync_admin/plugin/service/upload.php
  	 sed -i -e 's|<option value="1" <?php if($days==\x271\x27) echo \x27selected\x27; ?>>1<\/option>|<option value="180" <?php if (($days==\x271\x27) or ($days==\x27\x27)) echo \x27selected\x27; ?>>180<\/option>|' /var/www/qlync_admin/plugin/service/upload.php
  	 # sed -n '/if ($_REQUEST\[\x27resolution\x27]==\x270\x27) echo \x27selected\x27;/p' /var/www/qlync_admin/plugin/service/upload.php
  	 sed -i -e 's|if ($_REQUEST\[\x27resolution\x27]==\x270\x27) echo \x27selected\x27;|if (($_REQUEST\[\x27resolution\x27]==\x270\x27) or !isset(\$_REQUEST\[\x27resolution\x27])) echo \x27selected\x27;|' /var/www/qlync_admin/plugin/service/upload.php
		 sed -i -e 's|if (($_REQUEST\[\x27resolution\x27]==\x271\x27) or !isset(\$_REQUEST\[\x27resolution\x27])) echo \x27selected\x27;|if ($_REQUEST\[\x27resolution\x27]==\x271\x27) echo \x27selected\x27;|' /var/www/qlync_admin/plugin/service/upload.php  
  fi
elif [ "${oemid}" = '"P04"' ]; then
  p1="VGA w/Audio"
  p2="VGA"
  p3="QVGA"
  #patch service plugin, (pldt needs special items setting)
  if [ -f "/var/www/qlync_admin/plugin/service/admin.php" ]; then
    sed -i -e 's/<option value="1" <?php if($days==\x271\x27) echo \x27selected\x27; ?>>1<\/option>/<!--option value="1" <?php if($days==\x271\x27) echo \x27selected\x27; ?>>1<\/option-->/' /var/www/qlync_admin/plugin/service/admin.php
    sed -i -e 's/<option value="3" <?php if($days==\x273\x27) echo \x27selected\x27; ?>>3<\/option>/<!--option value="3" <?php if($days==\x273\x27) echo \x27selected\x27; ?>>3<\/option-->/' /var/www/qlync_admin/plugin/service/admin.php
    sed -i -e 's/<option value="5" <?php if($days==\x275\x27) echo \x27selected\x27; ?>>5<\/option>/<!--option value="5" <?php if($days==\x275\x27) echo \x27selected\x27; ?>>5<\/option-->/' /var/www/qlync_admin/plugin/service/admin.php
    sed -i -e 's/<option value="10" <?php if($days==\x2710\x27) echo \x27selected\x27; ?>>10<\/option>/<!--option value="10" <?php if($days==\x2710\x27) echo \x27selected\x27; ?>>10<\/option-->/' /var/www/qlync_admin/plugin/service/admin.php
    sed -i -e 's/<option value="30" <?php if($days==\x2730\x27) echo \x27selected\x27; ?>>30<\/option>/<!--option value="30" <?php if($days==\x2730\x27) echo \x27selected\x27; ?>>30<\/option-->/' /var/www/qlync_admin/plugin/service/admin.php
    myPrintInfo "remove option others than 3/7/14/21/28"
  fi
  if [ -f "/var/www/qlync_admin/plugin/service/upload.php" ]; then
    sed -i -e 's/<option value="1">1<\/option>/<!--option value="1">1<\/option-->/' /var/www/qlync_admin/plugin/service/upload.php
    sed -i -e 's/<option value="3">3<\/option>/<!--option value="3">3<\/option-->/' /var/www/qlync_admin/plugin/service/upload.php
    sed -i -e 's/<option value="5">5<\/option>/<!--option value="5">5<\/option-->/' /var/www/qlync_admin/plugin/service/upload.php                          
    sed -i -e 's/<option value="10">10<\/option>/<!--option value="10">10<\/option-->/' /var/www/qlync_admin/plugin/service/upload.php
    sed -i -e 's/<option value="30">30<\/option>/<!--option value="30">30<\/option-->/' /var/www/qlync_admin/plugin/service/upload.php
    myPrintInfo "remove option other than 7/14/21/28"
  fi
elif [ "${oemid}" = '"T03"' ]; then
  myPrintInfo "use default"
elif [ "${oemid}" = '"Z02"' ]; then
  p1="HD w/Audio"
  p2="VGA"
  p3="QVGA"
elif [ "${oemid}" = '"J01"' ]; then
  p1="HD 256Kbps"
  p2="SVGAW 512Kbps"
  p3="NA"
else
  #myPrintAlert "ERROR!! $oemid not matched!!"
  #exit
  myPrintAlert "Use Default Profile!!"
  p1="HD 512Kbps"
  p2="HVGAW 256Kbps"
  p3="IvedaMobile"
  	 sed -i -e 's|<option value="1" <?php if($days==\x271\x27) echo \x27selected\x27; ?>>1<\/option>|<option value="180" <?php if (($days==\x271\x27) or ($days==\x27\x27)) echo \x27selected\x27; ?>>180<\/option>|' /var/www/qlync_admin/plugin/service/admin.php
  	 sed -i -e 's|<option value="1" <?php if($days==\x271\x27) echo \x27selected\x27; ?>>1<\/option>|<option value="180" <?php if (($days==\x271\x27) or ($days==\x27\x27)) echo \x27selected\x27; ?>>180<\/option>|' /var/www/qlync_admin/plugin/service/upload.php
fi

if [ -f "/var/www/qlync_admin/plugin/service/admin.php" ]; then
  sed -i -e 's|Profile1<\/option>|Profile1 '"$p1"'<\/option>|' /var/www/qlync_admin/plugin/service/admin.php
  sed -i -e 's|Profile2<\/option>|Profile2 '"$p2"'<\/option>|' /var/www/qlync_admin/plugin/service/admin.php
  sed -i -e 's|Profile3<\/option>|Profile3 '"$p3"'<\/option>|' /var/www/qlync_admin/plugin/service/admin.php
  myPrintInfo "update service admin.php profile name $p1/$p2/$p3"
fi
if [ -f "/var/www/qlync_admin/plugin/service/upload.php" ]; then
  sed -i -e 's|Profile1<\/option>|Profile1 '"$p1"'<\/option>|' /var/www/qlync_admin/plugin/service/upload.php
  sed -i -e 's|Profile2<\/option>|Profile2 '"$p2"'<\/option>|' /var/www/qlync_admin/plugin/service/upload.php
  sed -i -e 's|Profile3<\/option>|Profile3 '"$p3"'<\/option>|' /var/www/qlync_admin/plugin/service/upload.php
  myPrintInfo "update service upload.php profile name $p1/$p2/$p3"
fi
  #patch vlc_server default service package
config_info=$(grep 'VGA 5 fps' /var/www/qlync_admin/html/server/vlc_server.php)
if [ ! -z "$config_info" ]; then #-z test if its empty string
    sed -i -e 's| HD 30 fps| '"$p1"'|' /var/www/qlync_admin/html/server/vlc_server.php
    sed -i -e 's| VGA 5 fps| '"$p2"'|' /var/www/qlync_admin/html/server/vlc_server.php
    sed -i -e 's| QVGA 5 fps| '"$p3"'|' /var/www/qlync_admin/html/server/vlc_server.php
    myPrintInfo "patch vlc_server Video Profile Info $p1/$p2/$p3"
fi

config_info=$(grep 'VGA 5 fps' /var/www/qlync_admin/html/license/mac_check.php)
if [ ! -z "$config_info" ]; then #-z test if its empty string
    sed -i -e 's| HD 30 fps| '"$p1"'|' /var/www/qlync_admin/html/license/mac_check.php
    sed -i -e 's| VGA 5 fps| '"$p2"'|' /var/www/qlync_admin/html/license/mac_check.php
    sed -i -e 's| QVGA 5 fps| '"$p3"'|' /var/www/qlync_admin/html/license/mac_check.php
    myPrintInfo "patch mac_check Video Profile Info $p1/$p2/$p3"
fi
