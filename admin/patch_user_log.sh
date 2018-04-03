
if [ ! -d "/var/www/qlync_admin/plugin/user_log" ]; then
  mkdir /var/www/qlync_admin/plugin/user_log
  echo "make plugin/user_log dir"
fi 
  cp -avr user_log/* /var/www/qlync_admin/plugin/user_log
  echo "installed user_log"

  config_info=$(grep '127.0.0.1' /var/www/qlync_admin/plugin/user_log/dbutil.php)
  if [ ! -z "$config_info" ]; then #-z test if its empty string
      config_info=$(grep 'mysql_ip' /var/www/qlync_admin/doc/config.php)
      len=${#config_info}
      COUNT=$(expr $len - 2)
      substr=$(expr substr $config_info 11 $COUNT)
      config_info1=${substr%?}
      sed -i -e 's/define("DB_HOST",\x27127.0.0.1\x27)/define("DB_HOST",'"$config_info1"')/' /var/www/qlync_admin/plugin/user_log/dbutil.php
      echo "replace mysql: $config_info1"  
  #account
      config_info=$(grep 'mysql_id' /var/www/qlync_admin/doc/config.php)
      len=${#config_info}
      COUNT=$(expr $len - 2)
      substr=$(expr substr $config_info 11 $COUNT)
      config_info1=${substr%?}
      sed -i -e 's/define("DB_USER",\x27isatRoot\x27)/define("DB_USER",'"$config_info1"')/' /var/www/qlync_admin/plugin/user_log/dbutil.php
      echo "replace mysql account: $config_info1"  
  #password
      config_info=$(grep 'mysql_pwd' /var/www/qlync_admin/doc/config.php)
      len=${#config_info}
      COUNT=$(expr $len - 2)
      substr=$(expr substr $config_info 12 $COUNT)
      config_info1=${substr%?}
      sed -i -e 's/define("DB_PASSWORD",\x27isatPassword\x27)/define("DB_PASSWORD",'"$config_info1"')/' /var/www/qlync_admin/plugin/user_log/dbutil.php
      echo "update mysql pwd: $config_info1 at /var/www/qlync_admin/plugin/user_log/dbutil.php"
   fi  
