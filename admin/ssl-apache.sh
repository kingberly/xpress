#usage ssl-apache.sh off
#/etc/apache2/sites-available/000-default.conf
#/etc/apache2/ports.conf remove all ssl
REQ_RESTART=0
HTTPPORT="8081"
#load LISTEN_PORT
SCRIPT_ROOT=`dirname "$0"`
CONFIG="$SCRIPT_ROOT/../isat_partner/install.conf"

if [ "$1" = "removehttp" ]; then
  sed -i -e 's|Listen '"$HTTPPORT"'|Listen '"$LISTEN_PORT"'|'  /etc/apache2/ports.conf
  sed -i -e 's|Listen '"$LISTEN_PORT"'|#Listen 443|g'  /etc/apache2/ports.conf
  sudo service apache2 restart
  exit
fi
if [ "$1" = "http" ]; then
  config_info=$(sudo netstat -lntp | grep $LISTEN_PORT)
  if [ ! -z "$config_info" ]; then
    echo "port $LISTEN_PORT is taken!\n"
  fi
   config_info=$(grep "Listen 443" /etc/apache2/ports.conf)
   if [ -z "$config_info" ]; then
      sed -i -e 's|Listen 443|Listen '"$LISTEN_PORT"'|g'  /etc/apache2/ports.conf
      echo "replace 443 to $LISTEN_PORT @/etc/apache2/ports.conf\n" 
   fi
    sudo service apache2 restart
    exit  
elif [ "$1" = "http8081" ]; then
  config_info=$(sudo netstat -lntp | grep $HTTPPORT)
  if [ ! -z "$config_info" ]; then
    echo "port $HTTPPORT is taken!\n"
  fi
#godwatch app feature
   config_info=$(grep $HTTPPORT /etc/apache2/sites-available/000-default.conf)
  if [ -z "$config_info" ]; then
sed -i -e '$a\<VirtualHost *:'"$HTTPPORT"'>\nDocumentRoot \/var\/www\/qlync_admin\nErrorLog ${APACHE_LOG_DIR}\/error'"$HTTPPORT"'.log\nCustomLog ${APACHE_LOG_DIR}\/access'"$HTTPPORT"'.log combined\n</VirtualHost>' /etc/apache2/sites-available/000-default.conf 
      echo "Add HTTP $HTTPPORT @/etc/apache2/sites-available/000-default.conf\n"
  fi

   config_info=$(grep $HTTPPORT /etc/apache2/ports.conf)
   if [ -z "$config_info" ]; then
      sed -i -e 's|Listen '"$LISTEN_PORT"'|Listen '"$HTTPPORT"'|'  /etc/apache2/ports.conf
      echo "Add HTTP $HTTPPORT access @/etc/apache2/ports.conf\n"
      sed -i -e 's|#Listen 443|Listen '"$LISTEN_PORT"'|g'  /etc/apache2/ports.conf
      echo "replace 443 to $LISTEN_PORT @/etc/apache2/ports.conf\n" 
   fi

    sudo service apache2 restart
    exit
fi

#patch for one PM apache in case of admin install fail
#alter sudo; 8983/lighttpd or 4405/apache2
config_info=$(netstat -lntp | grep 443 | awk {print'$7'})
for w in $(echo $config_info | tr "/" " ") ; do webservice=$w; done
if [ "$webservice" != "apache2" ]; then
    config_info=$(grep 'Listen ' /etc/apache2/ports.conf)
    adminPort=$(grep "LISTEN_PORT=" ../isat_partner/install.conf)
    echo "confirm $config_info @/etc/apache2/ports.conf and $adminPort is the same!"
    if [ ! -z "$adminPort" ]; then
      config_info=$(grep '#Listen 443' /etc/apache2/ports.conf)
      if [ -z "$config_info" ]; then
      config_info=$(grep 'Listen 443' /etc/apache2/ports.conf)
      if [ ! -z "$config_info" ]; then
          sed -i -e 's|Listen 443|#Listen 443|' /etc/apache2/ports.conf
          echo "remove Listen 443 @/etc/apache2/ports.conf"
          REQ_RESTART=1
      fi
      else
        echo "Listen 443 port is already remarked @/etc/apache2/ports.conf"
      fi
    fi
fi
#usage ssl-apache.sh mypem mycapem mykey 8080

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
if [ -z "$3" ]; then
  mykey="server.key"
else
  mykey=$3
fi

############# security browsable to off  for apache2.4
if [ -f "/etc/apache2/apache2.conf" ]; then
#replace Options Indexes FollowSymLinks
  config_info=$(sed -n '/<Directory \/var\/www\/>/, /<\/Directory>/p' /etc/apache2/apache2.conf | grep 'Indexes FollowSymLinks')
  if [ ! -z "$config_info" ]; then
    sed -i -e '/<Directory \/var\/www\/>/, /<\/Directory>/ s/Indexes FollowSymLinks/FollowSymLinks/' /etc/apache2/apache2.conf
    REQ_RESTART=1
    echo "remove browsable config\n"
  fi
  #remark folder
  config_info=$(grep '#<Directory /usr/share' /etc/apache2/apache2.conf)
  if [ -z "$config_info" ]; then 
    sed -i -e '/<Directory \/usr\/share>/, /<\/Directory>/ s/^/#/' /etc/apache2/apache2.conf
    REQ_RESTART=1
    echo "remove share folder access\n"
  fi
  config_info=$(grep '#<Directory /srv' /etc/apache2/apache2.conf)
  if [ -z "$config_info" ]; then 
    sed -i -e '/<Directory \/srv\/>/, /<\/Directory>/ s/^/#/' /etc/apache2/apache2.conf
    REQ_RESTART=1
    echo "remove srv folder access\n"
  fi
  echo "Restart apache server is required if apache2.conf changed\n"
fi
#########paramter 4 change port to target port if it is 80, feature not required after v2.3.x
#if [ ! -z "$4" ]; then  #port setting
#  if [ "$4" != "80" ]; then
#    config_info=$(grep 'Listen 80' /etc/apache2/ports.conf)
#    if [ -z "$config_info" ]; then
#     sed -i -e 's/Listen 80/Listen '"$4"'/'  /etc/apache2/ports.conf
#      echo "replace Listen 80 to $4 @ports.conf"
#    fi
#    config_info=$(grep '<VirtualHost \*:80>' /etc/apache2/sites-available/000-default.conf)
#    if [ -z "$config_info" ]; then
#     sed -i -e 's/<VirtualHost \*:80>/<VirtualHost \*:'"$4"'>/' /etc/apache2/sites-available/000-default.conf
#      echo "replace Listen 80 to $4 @000-default.conf" 
#    fi
#  fi
#fi
cp $SCRIPT_ROOT/error.html /var/www/qlync_admin
if [ -f "/var/www/qlync_admin/error.html" ]; then
if [ -z "$(grep '/error.html' /etc/apache2/sites-available/000-default.conf)" ]; then
sed -i -e 's| "Error"| "/error.html"|g' /etc/apache2/sites-available/000-default.conf
REQ_RESTART=1
fi
fi 
####check php security
config_info=$(grep 'session.cookie_secure = 1' /etc/php5/apache2/php.ini)
if [ -z "$config_info" ]; then
  sed -i -e 's/;session.cookie_secure =/session.cookie_secure = 1/' /etc/php5/apache2/php.ini
  REQ_RESTART=1
  echo "set session.cookie_secure 1 on apache2 php.ini, restart apache required.\n"  
fi 
config_info=$(grep 'session.cookie_httponly = 1' /etc/php5/cgi/php.ini)
if [ -z "$config_info" ]; then
  sed -i -e 's/session.cookie_httponly =/session.cookie_httponly = 1/' /etc/php5/cgi/php.ini
  REQ_RESTART=1
  echo "set session.cookie_httponly 1 on cgi php.ini, restart apache required.\n"
fi
config_info=$(grep 'session.cookie_secure = 1' /etc/php5/cgi/php.ini)
if [ -z "$config_info" ]; then
  sed -i -e 's/;session.cookie_secure =/session.cookie_secure = 1/' /etc/php5/cgi/php.ini
  REQ_RESTART=1
  echo "set session.cookie_secure 1 on cgi php.ini, restart apache required.\n"  
fi 
config_info=$(grep 'session.cookie_httponly = 1' /etc/php5/cli/php.ini)
if [ -z "$config_info" ]; then
  sed -i -e 's/session.cookie_httponly =/session.cookie_httponly = 1/' /etc/php5/cli/php.ini
  REQ_RESTART=1
  echo "set session.cookie_httponly 1 on cli php.ini, restart apache required.\n"
fi
config_info=$(grep 'session.cookie_secure = 1' /etc/php5/cli/php.ini)
if [ -z "$config_info" ]; then
  sed -i -e 's/;session.cookie_secure =/session.cookie_secure = 1/' /etc/php5/cli/php.ini
  REQ_RESTART=1
  echo "set session.cookie_secure 1 on cli php.ini, restart apache required.\n"  
fi
#sudo a2enmod headers
config_info=$(grep 'X-Content-Type-Options' /etc/apache2/apache2.conf)
if [ -z "$config_info" ]; then
sed -i -e '$a\Header always append X-Content-Type-Options: nosniff' /etc/apache2/apache2.conf
REQ_RESTART=1
echo "set header X-Content-Type-Options: nosniff\n"
fi
config_info=$(grep 'X-XSS-Protection' /etc/apache2/apache2.conf)
if [ -z "$config_info" ]; then
sed -i -e '$a\Header always append X-XSS-Protection: "1; mode=block"' /etc/apache2/apache2.conf
REQ_RESTART=1
echo "set header X-XSS-Protection\n"
fi
config_info=$(grep 'X-Frame-Options' /etc/apache2/apache2.conf)
if [ -z "$config_info" ]; then
sed -i -e '$a\Header always append X-Frame-Options SAMEORIGIN' /etc/apache2/apache2.conf
REQ_RESTART=1
echo "set header X-Frame-Options SAMEORIGIN\n"
fi
#added @2017
config_info=$(grep 'Strict-Transport-Security' /etc/apache2/apache2.conf)
if [ -z "$config_info" ]; then
sed -i -e '$a\Header always set Strict-Transport-Security "max-age=63072000; includeSubDomains; preload"' /etc/apache2/apache2.conf
REQ_RESTART=1
echo "set header Strict-Transport-Security\n"
fi
################# SSL install ################################
if [ ! -d "/etc/apache2/ssl" ]; then
  mkdir /etc/apache2/ssl
fi
if [ -f "$SCRIPT_ROOT/$1" ]; then
  chmod 600 *.pem
  chmod 600 *.key
  mv $SCRIPT_ROOT/*.pem /etc/apache2/ssl
  mv $SCRIPT_ROOT/*.key /etc/apache2/ssl
  echo "move ${mypem}, ${mycapem}, ${mykey} files to /etc/apache2/ssl\n"
  #once changed to root access, this file cannot overwrite unless sudo su
  #echo "change to root access for folder /etc/apache2/ssl"
  #chown root. /etc/apache2/ssl -R
  chown root. /etc/apache2/ssl/*.pem
  chown root. /etc/apache2/ssl/*.key
fi
if [ ! -f "/etc/apache2/ssl/$mypem" ]; then
  echo "pem file is not existed, leave script.\n"
  exit
fi

################# SSL install 2.4.10################################
if [ -f "/etc/apache2/sites-available/000-default.conf" ]; then
 #check on SSL was previously set
 config_info=$(grep 'SSLEngine off' /etc/apache2/sites-available/000-default.conf)
 if [ ! -z "$config_info" ]; then
 #replace off to on
    sed -i -e 's|SSLEngine off|SSLEngine on|' /etc/apache2/sites-available/000-default.conf
    REQ_RESTART=1
    echo "replace SSLEngine off to on @000-default.conf" 
 else
   config_info=$(grep 'SSLEngine on' /etc/apache2/sites-available/000-default.conf)
   if [ -z "$config_info" ]; then #-z test if its empty string
    sed -i -e '
    /<\/VirtualHost>/ {
    i\
    SSLEngine on
    i\
    SSLCertificateFile    \/etc\/apache2\/ssl\/'"$mypem"'
    i\
    SSLCertificateKeyFile \/etc\/apache2\/ssl\/'"$mykey"'
    i\
    SSLCertificateChainFile \/etc\/apache2\/ssl\/'"$mycapem"'
    i\
    SSLHonorCipherOrder on
    i\
    SSLProtocol all -SSLv2 -SSLv3 -TLSv1 -TLSv1.1
    i\
    SSLUseStapling on
    i\
    SSLStaplingResponderTimeout 5
    i\
    SSLStaplingReturnResponderErrors off
    i\
    SSLCompression off
    i\
    SSLInsecureRenegotiation on
    i\
    SSLCipherSuite "EECDH+ECDSA+AESGCM EECDH+aRSA+AESGCM EECDH+ECDSA+SHA384 EECDH+ECDSA+SHA256 EECDH+aRSA+SHA384 EECDH+aRSA+SHA256 EECDH+aRSA+RC4 EECDH EDH+aRSA RC4 !aNULL !eNULL !LOW !3DES !MD5 !EXP !PSK !SRP !DSS"
    i\
    <Location />
    i\
    SetEnvIfExpr "%{HTTPS} == \x27on\x27" no-gzip
    i\
    </Location>
    i\
    ErrorDocument 500 "/error.html"
    i\
    ErrorDocument 400 "/error.html"              
    i\
    ErrorDocument 401 "/error.html"              
    i\
    ErrorDocument 404 "/error.html"         
    i\
    ErrorDocument 301 "/error.html"               
    i\
    ErrorDocument 302 "/error.html"      
    i\
    ErrorDocument 303 "/error.html"             
    i\
    ErrorDocument 304 "/error.html"
    }' /etc/apache2/sites-available/000-default.conf || Exit
    echo "add SSL config. Enable SSL by sudo a2enmod ssl\n"
    REQ_RESTART=1
    sed -i -e '/<VirtualHost \*:'"$LISTEN_PORT"'>/i\SSLStaplingCache "shmcb: ${APACHE_LOG_DIR}/stapling-cache(150000)"' /etc/apache2/sites-available/000-default.conf
    sudo a2enmod ssl
  else
    echo "SSL of apache2.4.10 was installed\n"
    ################# Patch SSL suite ##########################
    config_info1=$(grep 'SSLCipherSuite' /etc/apache2/sites-available/000-default.conf)
    if [ -z "$config_info1" ]; then
        sed -i -e '
        /<\/VirtualHost>/ {
        i\
        SSLCipherSuite "EECDH+ECDSA+AESGCM EECDH+aRSA+AESGCM EECDH+ECDSA+SHA384 EECDH+ECDSA+SHA256 EECDH+aRSA+SHA384 EECDH+aRSA+SHA256 EECDH+aRSA+RC4 EECDH EDH+aRSA RC4 !aNULL !eNULL !LOW !3DES !MD5 !EXP !PSK !SRP !DSS"
        i\
        <Location />
        i\
        SetEnvIfExpr "%{HTTPS} == \x27on\x27" no-gzip
        i\
        </Location>
        }' /etc/apache2/sites-available/000-default.conf || Exit
        echo "Add SSL CipherSuite\n"
        REQ_RESTART=1
      #else  #replace existing suite
      #  sed -i -e 's/SSLCipherSuite/#SSLCipherSuite/' /etc/apache2/sites-available/000-default.conf
      #  sed -i -e '
      #  /SSLCipherSuite/ {
      #  i\
      #  SSLCipherSuite "EECDH+ECDSA+AESGCM EECDH+aRSA+AESGCM EECDH+ECDSA+SHA384 EECDH+ECDSA+SHA256 EECDH+aRSA+SHA384 EECDH+aRSA+SHA256 EECDH+aRSA+RC4 EECDH EDH+aRSA RC4 !aNULL !eNULL !LOW !3DES !MD5 !EXP !PSK !SRP !DSS"
      #  }' /etc/apache2/sites-available/000-default.conf || Exit
     fi #patch ssl 2.4
        ################# Patch http error code ##########################
    if [ -z "$(grep 'ErrorDocument' /etc/apache2/sites-available/000-default.conf)" ]; then
        sed -i -e '
        /<\/VirtualHost>/ {
        i\
        ErrorDocument 500 "/error.html"
        i\
        ErrorDocument 400 "/error.html"              
        i\
        ErrorDocument 401 "/error.html"              
        i\
        ErrorDocument 404 "/error.html"         
        i\
        ErrorDocument 301 "/error.html"               
        i\
        ErrorDocument 302 "/error.html"      
        i\
        ErrorDocument 303 "/error.html"             
        i\
        ErrorDocument 304 "/error.html"
        }' /etc/apache2/sites-available/000-default.conf || Exit
        echo "Add ErrorDocument to /etc/apache2/sites-available/000-default.conf\n"
        REQ_RESTART=1             
    fi
#added @2017
    config_info1=$(grep 'SSLUseStapling' /etc/apache2/sites-available/000-default.conf)
    if [ -z "$config_info1" ]; then
      sed -i -e '/SSLCipherSuite/a \    SSLCompression off\n    SSLUseStapling on\n    SSLStaplingResponderTimeout 5\n    SSLStaplingReturnResponderErrors off\n' /etc/apache2/sites-available/000-default.conf
      sed -i -e '/<VirtualHost \*:'"$LISTEN_PORT"'>/i\SSLStaplingCache "shmcb: ${APACHE_LOG_DIR}/stapling-cache(150000)"' /etc/apache2/sites-available/000-default.conf
      echo "Add SSLUseStapling @2017 >=Apache2.4"
      REQ_RESTART=1 
    fi
#####support after 2.2.15, default is off    
#    config_info=$(grep 'SSLInsecureRenegotiation' /etc/apache2/sites-available/000-default.conf)
#   if [ -z "$config_info1" ]; then
#      sed -i -e '
#      /<\/VirtualHost>/ {
#      i\
#      SSLInsecureRenegotiation off
#      }' /etc/apache2/sites-available/000-default.conf || Exit
#      echo "Add SSLInsecureRenegotiation to /etc/apache2/sites-available/000-default.conf\n"
#    fi    
  fi #new install 2.4 or patch
 fi #if SSLEngine off
fi #apache version if


################# Replace old pem file ##########################
if [ -z "$(grep /etc/apache2/ssl/$1 /etc/apache2/sites-available/000-default.conf)" ]; then
echo "replace SSL files:"
sh rssl.sh $1 $2 $3
fi
###################
if [ $REQ_RESTART -eq 1 ]; then
echo "Restart apache server by sudo service apache2 restart\n"
sudo service apache2 restart
fi
 