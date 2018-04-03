if [ -z "$1" ]; then
  mypem="server.pem"
else
  mypem=$1
fi
if [ -z "$2" ]; then
  mycapem="ca.pem"
else
  mycapem=$2
fi
REQ_RESTART=0
SCRIPT_ROOT=`dirname "$0"`
##########SSL install
if [ ! -d "/etc/lighttpd/ssl" ]; then
  mkdir /etc/lighttpd/ssl
fi
if [ -f "$SCRIPT_ROOT/$1" ]; then
  echo "move ${mypem} and ${mycapem} files to /etc/lighttpd/ssl"
  chmod 600 $SCRIPT_ROOT/*.pem
  mv $SCRIPT_ROOT/*.pem /etc/lighttpd/ssl
  #once changed to root access, this file cannot overwrite unless sudo su
  #echo "change to root access for folder /etc/lighttpd/ssl"
  #chown root. /etc/lighttpd/ssl -R
  chown root. /etc/lighttpd/ssl/*.pem
fi
if [ ! -f "/etc/lighttpd/ssl/$mypem" ]; then
  echo "pem file is not existed, leave script!!!!!\n"
  exit
fi
############fastcgi overload issue
#/etc/lighttpd/conf-available/15-fastcgi-php.conf
############php5 enable security
######extra setting, main php.ini is fpm/php.ini
####extra /etc/php5/fpm/pool.d/www.conf
config_info=$(grep 'session.cookie_httponly = 1' /etc/php5/cli/php.ini)
if [ -z "$config_info" ]; then
  sed -i -e 's/session.cookie_httponly =/session.cookie_httponly = 1/' /etc/php5/cli/php.ini
  REQ_RESTART=1
  echo "set session.cookie_httponly 1 for cli php.ini\n"
fi
config_info=$(grep 'session.cookie_secure = 1' /etc/php5/cli/php.ini)
if [ -z "$config_info" ]; then
  sed -i -e 's/;session.cookie_secure =/session.cookie_secure = 1/' /etc/php5/cli/php.ini
  REQ_RESTART=1
  echo "set session.cookie_secure 1 for cli php.ini\n"
fi
config_info=$(grep 'session.cookie_httponly = 1' /etc/php5/cgi/php.ini)
if [ -z "$config_info" ]; then
  sed -i -e 's/session.cookie_httponly =/session.cookie_httponly = 1/' /etc/php5/cgi/php.ini
  REQ_RESTART=1
  echo "set session.cookie_httponly 1 for cgi php.ini\n"
fi
config_info=$(grep 'session.cookie_secure = 1' /etc/php5/cgi/php.ini)
if [ -z "$config_info" ]; then
  sed -i -e 's/;session.cookie_secure =/session.cookie_secure = 1/' /etc/php5/cgi/php.ini
  REQ_RESTART=1
  echo "set session.cookie_secure 1 for cgi php.ini\n"
fi
######end of extra setting
REQ_RESTART_FPM=0
config_info=$(grep 'session.cookie_httponly = 1' /etc/php5/fpm/php.ini)
if [ -z "$config_info" ]; then
  sed -i -e 's/session.cookie_httponly =/session.cookie_httponly = 1/' /etc/php5/fpm/php.ini
  REQ_RESTART_FPM=1
  echo "set session.cookie_httponly 1 for fpm php.ini\n"
fi
config_info=$(grep 'session.cookie_secure = 1' /etc/php5/fpm/php.ini)
if [ -z "$config_info" ]; then
  sed -i -e 's/;session.cookie_secure =/session.cookie_secure = 1/' /etc/php5/fpm/php.ini
  REQ_RESTART_FPM=1
  echo "set session.cookie_secure 1 for fpm php.ini\n"
fi

if [ -z "$(grep "php_admin_value\[error_reporting" /etc/php5/fpm/pool.d/www.conf)" ]; then
  sudo sed -i -e '/php_admin_flag\[log_errors]/a\php_admin_value\[error_reporting] = E_ALL \& ~E_NOTICE \& ~E_WARNING \& ~E_STRICT \& ~E_DEPRECATED' /etc/php5/fpm/pool.d/www.conf
  REQ_RESTART_FPM=1
fi
#grep OR
#config_info=$(grep 'X-Frame-Options\|X-Content-Type-Options\|X-XSS-Protection\|Strict-Transport-Security' /etc/lighttpd/conf-available/10-isat-security.conf)
config_info=$(grep 'Strict-Transport-Security' /etc/lighttpd/conf-available/10-isat-security.conf)
if [ -z "$config_info" ]; then
  cp $SCRIPT_ROOT/10-isat-security.conf /etc/lighttpd/conf-available/
  lighty-enable-mod isat-security
  #/etc/init.d/lighttpd force-reload
  echo "required lighttpd service reload\n"
  REQ_RESTART=1
fi
#fi 

config_info=$(grep '\$SERVER\["socket"\] == "0.0.0.0:443"' /etc/lighttpd/conf-enabled/10-ssl.conf)
if [ -z "$config_info" ]; then
  /bin/su -c "echo '\$SERVER[\"socket\"] == \"0.0.0.0:443\" {
    ssl.engine  = \"enable\"
    ssl.pemfile = \"/etc/lighttpd/ssl/'"$mypem"'\"
    ssl.ca-file = \"/etc/lighttpd/ssl/'"$mycapem"'\"
    ssl.use-sslv2 = \"disable\"
    ssl.use-sslv3 = \"disable\"
    ssl.disable-client-renegotiation = \"enable\"
    ssl.honor-cipher-order = \"enable\"    
    ssl.cipher-list = \"ECDHE-RSA-AES256-GCM-SHA384:ECDHE-RSA-AES128-GCM-SHA256:DHE-RSA-AES256-GCM-SHA384:DHE-RSA-AES128-GCM-SHA256:ECDHE-RSA-AES256-SHA384:ECDHE-RSA-AES128-SHA256:ECDHE-RSA-AES256-SHA:ECDHE-RSA-AES128-SHA:DHE-RSA-AES256-SHA256:DHE-RSA-AES128-SHA256:DHE-RSA-AES256-SHA:DHE-RSA-AES128-SHA:ECDHE-RSA-DES-CBC3-SHA:EDH-RSA-DES-CBC3-SHA:AES256-GCM-SHA384:AES128-GCM-SHA256:AES256-SHA256:AES128-SHA256:AES256-SHA:AES128-SHA:DES-CBC3-SHA:HIGH:!aNULL:!eNULL:!EXPORT:!DES:!MD5:!PSK:!RC4\"
}' > /etc/lighttpd/conf-enabled/10-ssl.conf"
  #create file fail, do move
  if [ ! -f "/etc/lighttpd/conf-enabled/10-ssl.conf" ]; then
    mv $SCRIPT_ROOT/10-ssl.conf /etc/lighttpd/conf-enabled/
  fi
  cp /etc/lighttpd/conf-enabled/10-ssl.conf /etc/lighttpd/conf-available/10-ssl.conf
  echo "SSL setting created on /etc/lighttpd/conf-enabled/10-ssl.conf\n"
  REQ_RESTART=1
else
  echo "SSL was installed on Lighttpd 1.4.28" 
  ########## Patch SSL configuration (for v1.4.35)
  config_info1=$(grep 'ssl.use-sslv2' /etc/lighttpd/conf-enabled/10-ssl.conf)
  if [ -z "$config_info1" ]; then
    sed  -i -e '
    /}/ {
    i\
    ssl.use-sslv2 = "disable"
    }' /etc/lighttpd/conf-enabled/10-ssl.conf || Exit
    REQ_RESTART=1
  fi
#disable sslv3 will cause web error (connections.c.305) SSL:1 error:1408A10B:SSL routines:SSL3_GET_CLIENT_HELLO:wrong version number
#  config_info1=$(grep 'ssl.use-sslv3' /etc/lighttpd/conf-enabled/10-ssl.conf)
#  if [ -z "$config_info1" ]; then
#    sed -i -e '
#    /}/ {
#    i\
#    ssl.use-sslv3 = "disable"
#    }' /etc/lighttpd/conf-enabled/10-ssl.conf || Exit
#  fi
  sed -i -e ''  /etc/lighttpd/conf-enabled/10-ssl.conf
  config_info1=$(grep 'ssl.disable-client-renegotiation' /etc/lighttpd/conf-enabled/10-ssl.conf)
  if [ -z "$config_info1" ]; then
    sed -i -e '
    /}/ {
    i\
    ssl.disable-client-renegotiation = "enable"
    }' /etc/lighttpd/conf-enabled/10-ssl.conf || Exit
    REQ_RESTART=1
  fi
  config_info1=$(grep 'ssl.honor-cipher-order' /etc/lighttpd/conf-enabled/10-ssl.conf)
  if [ -z "$config_info1" ]; then
    sed -i -e '
    /}/ {
    i\
    ssl.honor-cipher-order = "enable"
    }' /etc/lighttpd/conf-enabled/10-ssl.conf || Exit
    REQ_RESTART=1
  fi
  ##########or Patch SSL cipher list
  config_info1=$(grep 'ssl.cipher-list' /etc/lighttpd/conf-enabled/10-ssl.conf)
  if [ -z "$config_info1" ]; then
    sed -i -e '
  /}/ {
  i\
  ssl.cipher-list = "ECDHE-RSA-AES256-GCM-SHA384:ECDHE-RSA-AES128-GCM-SHA256:DHE-RSA-AES256-GCM-SHA384:DHE-RSA-AES128-GCM-SHA256:ECDHE-RSA-AES256-SHA384:ECDHE-RSA-AES128-SHA256:ECDHE-RSA-AES256-SHA:ECDHE-RSA-AES128-SHA:DHE-RSA-AES256-SHA256:DHE-RSA-AES128-SHA256:DHE-RSA-AES256-SHA:DHE-RSA-AES128-SHA:ECDHE-RSA-DES-CBC3-SHA:EDH-RSA-DES-CBC3-SHA:AES256-GCM-SHA384:AES128-GCM-SHA256:AES256-SHA256:AES128-SHA256:AES256-SHA:AES128-SHA:DES-CBC3-SHA:HIGH:!aNULL:!eNULL:!EXPORT:!DES:!MD5:!PSK:!RC4"
  }' /etc/lighttpd/conf-enabled/10-ssl.conf || Exit
  echo "Add cipher list to /etc/lighttpd/conf-enabled/10-ssl.conf\n"
  REQ_RESTART=1
#  else  #replace existing suite
#      sed -i -e 's/ssl.cipher-list/#ssl.cipher-list/' /etc/lighttpd/conf-enabled/10-ssl.conf
#      sed -i -e '
#  /ssl.cipher-list/ {
#  i\
#  ssl.cipher-list = "ECDHE-RSA-AES256-GCM-SHA384:ECDHE-RSA-AES128-GCM-SHA256:DHE-RSA-AES256-GCM-SHA384:DHE-RSA-AES128-GCM-SHA256:ECDHE-RSA-AES256-SHA384:ECDHE-RSA-AES128-SHA256:ECDHE-RSA-AES256-SHA:ECDHE-RSA-AES128-SHA:DHE-RSA-AES256-SHA256:DHE-RSA-AES128-SHA256:DHE-RSA-AES256-SHA:DHE-RSA-AES128-SHA:ECDHE-RSA-DES-CBC3-SHA:EDH-RSA-DES-CBC3-SHA:AES256-GCM-SHA384:AES128-GCM-SHA256:AES256-SHA256:AES128-SHA256:AES256-SHA:AES128-SHA:DES-CBC3-SHA:HIGH:!aNULL:!eNULL:!EXPORT:!DES:!MD5:!PSK:!RC4"
#  }' /etc/lighttpd/conf-enabled/10-ssl.conf || Exit
#  echo "replace cipher list\n"
  fi
fi
################# Patch http error code ##########################
if [ -z "$(grep server.max-fds /etc/lighttpd/lighttpd.conf)" ]; then
  #read-idle 60,write-idle 360, max-keep-alive-idle 5,max-keep-alive-requests 16
  #server.max-fds/server.max-connections default 1024, suggest 2048
  sed -i -e '$a\server.max-fds = 8192' /etc/lighttpd/lighttpd.conf
  sed -i -e '$a\server.max-connections = 4096' /etc/lighttpd/lighttpd.conf
  #server.max_request-size supports in 1.4.42 (now 1.4.33 default 64K) 
  echo "Add max-fds/max-connections to /etc/lighttpd/lighttpd.conf\n"
else    #if web_redirecterror.sh was execute first
  maxfds_count=0
  if [ ! -z "$(grep ':5544' /etc/lighttpd/lighttpd.conf)" ]; then
    maxfds_count=$((maxfds_count+1))
  fi
  if [ ! -z "$(grep ':81' /etc/lighttpd/lighttpd.conf)" ]; then
    maxfds_count=$((maxfds_count+1))
  fi
  config_info=$(grep 'server.max-fds' /etc/lighttpd/lighttpd.conf | wc -l)
  total_count=`expr $config_info + 0`  #string to number
  echo "current hold $total_count, other port hold $maxfds_count"
  if [ $total_count -le $maxfds_count  ]; then
  sed -i -e '$a\server.max-fds = 8192' /etc/lighttpd/lighttpd.conf
  sed -i -e '$a\server.max-connections = 4096' /etc/lighttpd/lighttpd.conf
  echo "Update max-idle/max-fds/max-connections to /etc/lighttpd/lighttpd.conf\n"
  REQ_RESTART=1
  else
    echo "max-fds has added" 
  fi
fi

config_info=$(grep 'server.errorfile-prefix' /etc/lighttpd/lighttpd.conf)
if [ -z "$config_info" ]; then
  sed -i -e '$a\server.errorfile-prefix = "/var/www/SAT-CLOUDNVR/error-"' /etc/lighttpd/lighttpd.conf
  #sed -i -e '$a\server.errorfile-handler = "/var/www/SAT-CLOUDNVR/error.html"' /etc/lighttpd/lighttpd.conf
  #sed -i -e '/server.errorfile-handler/d' /etc/lighttpd/lighttpd.conf
  echo "Add errorfile-handler to /etc/lighttpd/lighttpd.conf\n"
  REQ_RESTART=1
fi
if [ ! -f "/var/www/SAT-CLOUDNVR/error-404.html" ]; then
cp $SCRIPT_ROOT/error*.html /var/www/SAT-CLOUDNVR/
echo "cp error response html to www root folder"
fi
################# Replace old pem file ##########################
if [ -z "$(grep /etc/lighttpd/ssl/$1 /etc/lighttpd/conf-enabled/10-ssl.conf)" ]; then
echo "replace SSL files:"
sh $SCRIPT_ROOT/rssl.sh $1 $2
fi
####################################
if [ $REQ_RESTART_FPM -eq 1 ]; then
  /etc/init.d/php5-fpm restart
fi
if [ $REQ_RESTART -eq 1 ]; then
echo "reStart lighttpd service by sudo /etc/init.d/lighttpd restart"
sleep 3
/etc/init.d/lighttpd restart
#/etc/init.d/lighttpd force-reload
fi
 