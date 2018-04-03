#######temp cleanup
config_info=$(grep '/var/www/qlync_admin/plugin/billing/log/' /etc/crontab)
if [ -z "$config_info" ]; then #-z test if its empty string
  echo "0 1 * * 0   root  find  /var/www/qlync_admin/plugin/billing/log/  -type f -mtime +90 -exec rm {} +" >> /etc/crontab
  echo "set /etc/crontab to cleanup plugin/billing/log/ temp folder in 90 days\n"
fi
config_info=$(grep 'listBillingExcelOutput.php' /etc/crontab)
if [ -z "$config_info" ]; then #-z test if its empty string
  echo "3 3 * * *   root   /usr/bin/php5  \"/var/www/qlync_admin/plugin/billing/listBillingExcelOutput.php\" >> \"/var/tmp/billing.log\"" >> /etc/crontab
  echo "set daily generate billing excel file\n"
fi
#############install
if [ ! -d "/var/www/qlync_admin/plugin" ]; then
  mkdir /var/www/qlync_admin/plugin
  echo "mkdir plugin\n"
fi
if [ ! -d "/var/www/qlync_admin/plugin/billing" ]; then
  mkdir /var/www/qlync_admin/plugin/billing
  cp -avr billing/* /var/www/qlync_admin/plugin/billing
  echo "install billing plugin\n"
  mkdir /var/www/qlync_admin/plugin/billing/log
  chmod 777 /var/www/qlync_admin/plugin/billing/log
  echo "chmod 777 /var/www/qlync_admin/plugin/billing/log\n"   
else
  if [ -d "/var/www/qlync_admin/plugin/billing/Classes" ]; then
    #renew PHPExcel
    sudo rm -rf /var/www/qlync_admin/plugin/billing/Classes
    sudo cp -avr billing/Classes /var/www/qlync_admin/plugin/billing
  fi
  cp -avr billing/listBilling*  /var/www/qlync_admin/plugin/billing
  cp billing/dbutil.php /var/www/qlync_admin/plugin/billing
  echo "chmod 777 /var/www/qlync_admin/plugin/billing/log\n"
  chmod 777 /var/www/qlync_admin/plugin/billing/log
  echo "chmod 777 /var/www/qlync_admin/plugin/billing/log\n"
  chmod 777 /var/www/qlync_admin/plugin/billing/billing.log
  echo "chmod 777 /var/www/qlync_admin/plugin/billing/billing.log\n"
  echo "update /var/www/qlync_admin/plugin/billing\n"  
fi
<<COMMENT
if [ -f "/var/www/qlync_admin/plugin/billing/dbutil.php" ]; then
  config_info_chk=$(grep '127.0.0.1' /var/www/qlync_admin/plugin/billing/dbutil.php)
fi
COMMENT