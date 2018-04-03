myPrintAlert(){
  echo "\\033[31m$1\\033[0m"
}
myPrintInfo(){
  echo "\\033[92m$1\\033[0m"
}

#install or patch customer service form (PLDT)
if [ ! -d "/var/www/qlync_admin/plugin/" ]; then
  mkdir /var/www/qlync_admin/plugin
fi
# pldt/status.php /var/www/qlync_admin/plugin/
#echo "<?php header(\"Location: {\$_SERVER['HTTP_HOST']}/state/status.html\");?>" | sudo tee /var/www/qlync_admin/plugin/status.php

if [ ! -d "/var/www/qlync_admin/plugin/customerservice" ]; then
   mkdir /var/www/qlync_admin/plugin/customerservice
   #mv customerservice /var/www/qlync_admin/plugin
   cp -avr customerservice/* /var/www/qlync_admin/plugin/customerservice
  myPrintInfo "install customerservice\n"
  php _db_add_plugin_menu_customer.php
  myPrintInfo "set customerservice plugin menu"
else
  cp -avr customerservice/* /var/www/qlync_admin/plugin/customerservice
  myPrintInfo "update customerservice\n"
  php _db_add_plugin_menu_customer.php
  myPrintInfo "set customerservice plugin menu"
fi
 