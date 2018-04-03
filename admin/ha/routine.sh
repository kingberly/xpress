###For uninstall HA admin, sudo sh routine.sh remove
if [ "$1" = "remove" ]; then
  config_info=$(grep "/usr/igs/scripts/routine.py" /etc/crontab)
  if [ ! -z "$config_info" ]; then
      sed -i -e '/routine.py/d' /etc/crontab
  fi
#special character * in sed was \x2A, start with 0/1/2/5/*
    sed -i -e '/\/var\/www\/qlync_admin\/html\/server/ s|^#*||g' /etc/crontab
    sed -i -e '/daily_update_device.php/ s|^#1|1|' /etc/crontab
    sed -i -e '/auto_model_list_feature.php/ s|^#2|2|' /etc/crontab
    sed -i -e '/addLicenseNotify.php/ s|^#5|5|' /etc/crontab
    #new added
    sed -i -e '/reservation_check.php/ s|^#1|1|' /etc/crontab
    sed -i -e '/monthly_service_log.php/ s|^#0|0|' /etc/crontab
    echo "restore admin service from routine.py"
  #config_info=$(grep "#1 1  \* \* \* root /usr/bin/php5  \"/var/www/qlync_admin/html/common/daily_update_device" /etc/crontab)
  #if [ ! -z "$config_info" ]; then
  #    sed -i -e '/1 1  \x2A \x2A \x2A/, /partner_log.php/ s|#||' /etc/crontab
  #fi
  /etc/init.d/heartbeat stop
  /etc/init.d/watchdog stop
  exit
else
  echo "to remove usage: sudo sh routine.sh remove" 
fi

config_info=$(grep "/usr/igs/scripts/routine.py" /etc/crontab)
if [ -z "$config_info" ]; then
    sed -i -e '$a\* * * * * root /usr/bin/python /usr/igs/scripts/routine.py' /etc/crontab
    echo "add routine.py to crontab\n" 
fi
 
config_info=$(grep "#1 1  \* \* \* root /usr/bin/php5  \"/var/www/qlync_admin/html/common/daily_update_device" /etc/crontab)
if [ -z "$config_info" ]; then 
  sed -i -e '/1 1  \x2A \x2A \x2A/, /partner_log.php/ s|^|#|' /etc/crontab
  #sed -i -e '/^#$/,/partner_log.php/ s/^/# /' /etc/crontab 
  echo "disable qlync log from crontab\n"
fi
#remove others
cd ..
sh patch_const.sh remove
sh patch_rpic.sh remove
cd ha/
config=$(grep reservation_check /etc/crontab)
if [ ! -z "$config" ]; then
  config_info=$(grep "#10 \* \* \* \* root /usr/bin/php5 \"/var/www/qlync_admin/html/common/reservation_check" /etc/crontab)
  if [ -z "$config_info" ]; then
    #sed -i -e '/10 \x2A \x2A \x2A \x2A/, /partner_log.php/ s|^|#|' /etc/crontab
    sed -i -e '/10 \x2A \x2A \x2A \x2A/, /monthly_bill_update.log/ s|^|#|' /etc/crontab
    echo "disable new feature for HA mode"
  fi
fi #new feature after 101 /v3.2.1

#comment out
<<'COMMENT'
echo "test comment out"
config_info=$(grep "/var/www/qlync_admin/html/server/tunnel_log.php" /etc/crontab | awk '{print $1}')
if [ ! "$config_info" = "#*" ]; then 
  sed -i -e '/server\/tunnel_log.php/ s|^|#|' /etc/crontab
  echo "remove schedule tunnel_log from crontab to routine.py\n"
fi
config_info=$(grep "/var/www/qlync_admin/html/server/evo_log.php" /etc/crontab | awk '{print $1}')
if [ ! "$config_info" = "#*" ]; then 
  sed -i -e '/server\/evo_log.php/ s|^|#|' /etc/crontab
  echo "remove schedule evo_log from crontab to routine.py\n"
fi
config_info=$(grep "/var/www/qlync_admin/html/server/web_log.php" /etc/crontab | awk '{print $1}')
if [ ! "$config_info" = "#*" ]; then 
  sed -i -e '/server\/web_log.php/ s|^|#|' /etc/crontab
  echo "remove schedule web_log from crontab to routine.py\n"
fi
config_info=$(grep "/var/www/qlync_admin/html/server/partner_log.php" /etc/crontab | awk '{print $1}')
if [ ! "$config_info" = "#*" ]; then 
  sed -i -e '/server\/partner_log.php/ s|^|#|' /etc/crontab
  echo "remove schedule partner_log from crontab to routine.py\n"
fi

config_info=$(grep "/var/www/qlync_admin/html/common/daily_update_device.php" /etc/crontab | awk '{print $1}')
if [ ! "$config_info" = "#1" ]; then 
  sed -i -e '/common\/daily_update_device.php/ s|^|#|' /etc/crontab
  echo "remove schedule daily_update_device.php from crontab to routine.py\n"
fi
config_info=$(grep "/var/www/qlync_admin/html/common/auto_model_list_feature.php" /etc/crontab | awk '{print $1}')
if [ ! "$config_info" = "#2" ]; then 
  sed -i -e '/common\/auto_model_list_feature.php/ s|^|#|' /etc/crontab
  echo "remove schedule auto_model_list_feature from crontab to routine.py\n"
fi

config_info=$(grep "/plugin/licservice/addLicenseNotify.php" /etc/crontab | awk '{print $1}')
if [ "$config_info" = "#5" ]; then 
  sed -i -e '/addLicenseNotify.php/ s|^|#|' /etc/crontab
  echo "remove licservice notify from crontab\n"
fi
COMMENT