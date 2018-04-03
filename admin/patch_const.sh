if [ "$1" = "remove" ]; then
    config_info=$(grep 'connlog_Const' /etc/crontab)
    if [ ! -z "$config_info" ]; then
        sed -i -e '/tunnel_connlog_Const.php/d'  /etc/crontab 
        sed -i -e '/rtmpd_connlog_Const.php/d'  /etc/crontab
        echo "cleanup crontab service"
    fi
    exit  
else
  echo "to remove usage: sudo sh patch_const.sh remove"
fi

if [ ! -d "/var/www/qlync_admin/plugin/debug" ]; then
  if [ ! -d "/var/www/qlync_admin/plugin" ]; then
      sudo mkdir /var/www/qlync_admin/plugin 
  fi
  sudo mkdir /var/www/qlync_admin/plugin/debug
fi
cp debug/tunnel_connlog_Excel_Const.php /var/www/qlync_admin/plugin/debug
cp debug/_connchartConst.php /var/www/qlync_admin/plugin/debug
cp debug/rtmpd_connlog_Const.php /var/www/qlync_admin/plugin/debug
cp debug/tunnel_connlog_Const.php /var/www/qlync_admin/plugin/debug

if [ ! -f "/usr/igs/scripts/routine.py" ]; then
  config_info=$(grep '_connlog_Const' /etc/crontab)
  if [ -z "$config_info" ]; then #-z test if its empty string
    #\x27 single quote, \x22 double quote
    sed -i -e "\$a*/15 * * * * root /usr/bin/php5  \x22/var/www/qlync_admin/plugin/debug/tunnel_connlog_Const.php\x22" /etc/crontab
    sed -i -e "\$a*/15 * * * * root /usr/bin/php5  \x22/var/www/qlync_admin/plugin/debug/rtmpd_connlog_Const.php\x22" /etc/crontab
    echo "add ceontab to enable tunnel_connlog_Const"
  fi  
fi

php _db_add_plugin_menu_nest_Const.php
php /var/www/qlync_admin/html/common/menu_update.php