if [ -f "/etc/php5/fpm/pool.d/www.conf" ]; then
  echo "Make update on /etc/php5/fpm/pool.d/www.conf"
  config_info=$(grep '^pm.max_children = ' /etc/php5/fpm/pool.d/www.conf) 
  if [ ! -z "$config_info" ]; then
      #delete and re-add
      sed -i -e '/^pm.max_children = /d'  /etc/php5/fpm/pool.d/www.conf
      sed -i -e '$a\pm.max_children = 2500'  /etc/php5/fpm/pool.d/www.conf
      #sed -i -e 's|pm.max_children = 5|pm.max_children = 2500|' /etc/php5/fpm/pool.d/www.conf
      echo "replace pm.max_children @ php5-fpm"
      grep 'max_children =' /etc/php5/fpm/pool.d/www.conf
  else
      sed -i -e '$a\pm.max_children = 2500'  /etc/php5/fpm/pool.d/www.conf
      echo "add pm.max_children @ php5-fpm" 
  fi

  config_info=$(grep '^pm.max_spare_servers = ' /etc/php5/fpm/pool.d/www.conf) 
  if [ ! -z "$config_info" ]; then
      #delete and re-add
      sed -i -e '/^pm.max_spare_servers = /d'  /etc/php5/fpm/pool.d/www.conf
      sed -i -e '$a\pm.max_spare_servers = 5'  /etc/php5/fpm/pool.d/www.conf
      #sed -i -e 's|pm.max_spare_servers = 3|pm.max_spare_servers = 5|' /etc/php5/fpm/pool.d/www.conf
      echo "replace pm.max_spare_servers @ php5-fpm"
      grep 'max_spare_servers =' /etc/php5/fpm/pool.d/www.conf
  else
      sed -i -e '$a\pm.max_spare_servers = 5'  /etc/php5/fpm/pool.d/www.conf
      echo "add pm.max_spare_servers @ php5-fpm" 
  fi

  config_info=$(grep '^pm.max_requests = ' /etc/php5/fpm/pool.d/www.conf) 
  if [ ! -z "$config_info" ]; then
      #delete and re-add
      sed -i -e '/^pm.max_requests = /d'  /etc/php5/fpm/pool.d/www.conf
      sed -i -e '$a\pm.max_requests = 10000'  /etc/php5/fpm/pool.d/www.conf
      #sed -i -e 's|;pm.max_requests = 500|pm.max_requests = 10000|' /etc/php5/fpm/pool.d/www.conf
      #sed -i -e 's|pm.max_requests = 2500|pm.max_requests = 10000|' /etc/php5/fpm/pool.d/www.conf
      echo "replace pm.max_requests @ php5-fpm"
      grep 'max_requests =' /etc/php5/fpm/pool.d/www.conf
  else
      sed -i -e '$a\pm.max_requests = 10000'  /etc/php5/fpm/pool.d/www.conf
      echo "add pm.max_requests @ php5-fpm" 
  fi

elif [ -f "/etc/php-fpm.d/www.conf" ]; then
  echo "Make update on /etc/php-fpm.d/www.conf" 
fi
sudo /etc/init.d/php5-fpm restart 