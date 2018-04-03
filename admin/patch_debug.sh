myPrintAlert(){
  echo "\\033[31m$1\\033[0m"
}
myPrintInfo(){
  echo "\\033[92m$1\\033[0m"
}
#install or patch customer service form (PLDT)
if [ ! -d "/var/www/qlync_admin/plugin/debug" ]; then
    mkdir /var/www/qlync_admin/plugin/debug
    myPrintInfo "make debug dir"
fi

  cp debug/* /var/www/qlync_admin/plugin/debug
  myPrintInfo "update debug\n"

if [ ! -z "$1" ]; then
  if [ "$1" = 'status' ]; then
      config_info=$(grep '/var/www/qlync_admin/plugin/debug/stream_statuslog.php' /etc/crontab)
      if [ -z "$config_info" ]; then #-z test if its empty string
        sed -i -e '$a\*/3 * * * * root  /usr/bin/php5  "/var/www/qlync_admin/plugin/debug/stream_statuslog.php"' /etc/crontab
        myPrintInfo "write debug stream status log to /etc/crontab"
      fi
  fi
  if [ "$1" = 'tunnelconn' ]; then
      config_info=$(grep '/var/www/qlync_admin/plugin/debug/tunnel_connlog.php' /etc/crontab)
      if [ -z "$config_info" ]; then #-z test if its empty string
        sed -i -e '$a\*/3 * * * * root  /usr/bin/php5  "/var/www/qlync_admin/plugin/debug/tunnel_connlog.php"' /etc/crontab
        myPrintInfo "write debug tunnel conn log to /etc/crontab"
      fi
  fi
  if [ "$1" = 'streamconn' ]; then
      config_info=$(grep '/var/www/qlync_admin/plugin/debug/stream_connlog.php' /etc/crontab)
      if [ -z "$config_info" ]; then #-z test if its empty string
        sed -i -e '$a\*/3 * * * * root  /usr/bin/php5  "/var/www/qlync_admin/plugin/debug/stream_connlog.php"' /etc/crontab
        myPrintInfo "write debug stream conn log to /etc/crontab"
      fi
  fi
  if [ "$1" = 'off' ]; then
    sed -i -e '/tunnel_connlog.php/d' /etc/crontab
    sed -i -e '/stream_connlog.php/d' /etc/crontab
    sed -i -e '/stream_statuslog.php/d' /etc/crontab
  fi
fi
if [ -f "/var/tmp/stream_status.log" ]; then
  chown www-data:www-data /var/tmp/stream_status.log
fi
if [ -f "/var/tmp/stream_connection.log" ]; then
  chown www-data:www-data /var/tmp/stream_connection.log
fi
if [ -f "/var/tmp/tunnel_connection.log" ]; then
  chown www-data:www-data /var/tmp/tunnel_connection.log
fi
#patch plugin customer service menu
php _db_add_plugin_menu_nest_debug.php
myPrintInfo "add debug plugin menu"
#php /var/www/qlync_admin/html/common/menu_update.php

#extra tool
config_info=$(grep "Plugin Tools" /var/www/qlync_admin/html/faq/turtorial_content.php)
if [ -z "$config_info" ]; then #empty insert
sed -i -e '/?>/ {
i\
if($_SESSION["ID_admin_oem"] or $_SESSION["ID_admin_qlync"]) {
i\
$turtorial[Tools][1][q]	="Plugin Tools:";
i\
$turtorial[Tools][1][a][]	="<a href='https://xpress.megasys.com.tw:8080/plugin/debug/vmresource.html' target=_blank>VM Resource Estimation</a>";}
}' /var/www/qlync_admin/html/faq/turtorial_content.php
echo "add Title and VM resource link"
else
config_info=$(grep "VM Resource" /var/www/qlync_admin/html/faq/turtorial_content.php)
if [ -z "$config_info" ]; then #empty insert
sed -i -e '/Plugin Tools/  {
i\
$turtorial[Tools][1][a][]	="<a href='https://xpress.megasys.com.tw:8080/plugin/debug/vmresource.html' target=_blank>VM Resource Estimation</a>";
}' /var/www/qlync_admin/html/faq/turtorial_content.php
echo "add VM resource link"
fi
fi 