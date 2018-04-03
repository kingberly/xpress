#!/bin/sh
pkg_install=0
view_install=0
C13_WORKEYE_enable=0
K01_CUSTOMER_enable=0
T05_CUSTOMER_enable=0
X02_CUSTOMER_enable=0
X02_GODWATCH_enable=0
X02_MEGASYS_enable=0
X02_CAMLIC_enable=0
T04_CUSTOMER_enable=0
P04_CUSTOMER_enable=0
V04_CUSTOMER_enable=0
Xnn_SYNC_enable=1
DEFAULT_TABLE="Xnn_sync.sql"

if [ -z "$1" ]; then
  echo "usage: sudo sh install.sh <param>\nparam: "
  echo "P04_CUSTOMER(=>service_camera), V04_CUSTOMER(=>api_log),"
  echo "T05_CUSTOMER (=>share_log), T04_CUSTOMER(=>share_log),"
  echo "X02_CUSTOMER (=>vendor_info), X02_GODWATCH(=>gw_group), X02_CAMLIC(=>licservice.*),C13_WORKEYE(=>workeye.*),"
  echo "PKG_INSTALL, VIEW_RECOVER"
  echo "X02_MEGASYS, REMOVE DATABASE dd, REMOVE TABLE dd.tt"
  echo "DEFAULT INSTALL syncinfo"
  if [ ${Xnn_SYNC_enable} -eq 0 ]; then 
		exit
	fi
fi

CUSTOMER_DB_NAME="customerservice"
GODWATCH_DB_NAME="godwatch"
MEGASYS_DB_NAME="megasys"
CAMLIC_DB_NAME="licservice"
SCRIPT_ROOT=`dirname "$0"`
CONFIG="$SCRIPT_ROOT/../isat_db/install.conf"
. "$CONFIG"

#VERSION=`cat "$SCRIPT_ROOT/version"`
PROCEDURE=
Exit() {
	echo "\\033[91mInstallation failed at '$PROCEDURE', please refer to FAQ or contact support.\\033[0m"
	exit 1
}
Start() {
	echo "\\033[92m$1\\033[0m"
	PROCEDURE="$1"
}
MySql() {
	echo "mysql: $1"
	echo "$1" | mysql -u root -p$MYSQL_ROOT_PASSWORD
	return $?
}
insertDB () {
    Start "create database"
    MySql "CREATE DATABASE IF NOT EXISTS $1;"
    Start "grant user $ISAT_DB_USER permission"
    MySql "GRANT ALL ON $1.* TO '$ISAT_DB_USER'@'%';"
    Start "import $1 tables"
    Start "LIST tables:"
    MySql "SHOW TABLES FROM $1;"
    mysql -u $ISAT_DB_USER -p$ISAT_DB_PWD $1 < "$SCRIPT_ROOT/$2"
}

if [ "$1" = "T05_CUSTOMER" ]; then
T05_CUSTOMER_enable=1
CUSTOMER_TABLE="T05_customerservice.sql"
elif [ "$1" = "C13_WORKEYE" ]; then
C13_WORKEYE_enable=1
CUSTOMER_TABLE="C13_workeye.sql"
elif [ "$1" = "X02_CUSTOMER" ]; then
X02_CUSTOMER_enable=1
CUSTOMER_TABLE="X02_customerservice.sql"
elif [ "$1" = "X02_MEGASYS" ]; then
X02_MEGASYS_enable=1
MEGASYS_TABLE="X02_megasys.sql"
elif [ "$1" = "X02_GODWATCH" ]; then
X02_GODWATCH_enable=1
GODWATCH_TABLE="X02_godwatch.sql"
T05_CUSTOMER_enable=1
elif [ "$1" = "X02_CAMLIC" ]; then
X02_CAMLIC_enable=1
CAMLIC_TABLE="X02_licservice.sql"
elif [ "$1" = "T04_CUSTOMER" ]; then
T04_CUSTOMER_enable=1
CUSTOMER_TABLE="T04_customerservice.sql"
elif [ "$1" = "P04_CUSTOMER" ]; then
P04_CUSTOMER_enable=1
CUSTOMER_TABLE="P04_customerservice.sql"
elif [ "$1" = "V04_CUSTOMER" ]; then
V04_CUSTOMER_enable=1
CUSTOMER_TABLE="V04_customerservice.sql"
elif [ "$1" = "K01_CUSTOMER" ]; then
K01_CUSTOMER_enable=1
CUSTOMER_TABLE="K01_customerservice.sql"
elif [ "$1" = "VIEW_RECOVER" ]; then
view_install=1
TABLEv2="cloudnvr.view.v2.sql"
TABLEv3="cloudnvr.view.v3.sql"
elif [ "$1" = "PKG_INSTALL" ]; then
pkg_install=1

elif [ "$1" = "REMOVE" ]; then
	if [ "$2" = "TABLE" ]; then
	  MySql "DROP TABLE IF EXISTS $3;"
	elif [ "$2" = "DATABASE" ]; then
		if [ "$3" = "megasys" -o "$3" = "godwatch" -o "$3" = "customerservice" ]; then
			MySql "DROP DATABASE ${3};"
		else
			echo "Not allow to drop priority database."
		fi
	else
		echo "Pls specify DATABASE or TABLE"
	fi
	exit
fi


if [ ${pkg_install} -eq 1 ]; then
  config=$(uname -a | grep x86_64)
  if [ -z "$config" ]; then
      echo "Required ubuntu 64bit to complete installation"
      exit
  fi
  Start "update apt package cache"
  sudo apt-get update || Exit
  #php install
  sudo apt-get -y install python-software-properties || Exit
  sudo add-apt-repository -y ppa:ondrej/php5 || Exit
  sudo apt-get -y upgrade || Exit
  
  Start "install php-apache2 packages"
  ##sudo apt-get -y install apache2 php5 libapache2-mod-php5 php5-cgi php5-mysql php5-memcache php5-curl php5-xmlrpc openssl libssl1.0.0 memcached || Exit
  sudo apt-get -y install php5 libapache2-mod-php5 php5-cgi php5-mysql php5-curl|| Exit
  
  Start "install mysql server"
  echo "mysql-server mysql-server/root_password password $MYSQL_ROOT_PASSWORD" | sudo debconf-set-selections || Exit
  echo "mysql-server mysql-server/root_password_again password $MYSQL_ROOT_PASSWORD" | sudo debconf-set-selections || Exit
  sudo apt-get -y install mysql-server openssl libssl1.0.0 python-mysqldb python-bcrypt || Exit
  sudo sed -i -e 's/^bind-address/#bind-address/' /etc/mysql/my.cnf
  #new added for database utf8 setting
  config=$(grep '=utf8'  /etc/mysql/my.cnf)
  if [ -z "$config" ]; then
  #sudo sed -i -e '/=utf8/d'  /etc/mysql/my.cnf
  sudo sed -i -e '/\[mysql\]/a\default-character-set=utf8' /etc/mysql/my.cnf
  sudo sed -i -e '/\[mysqld\]/a\character-set-server=utf8' /etc/mysql/my.cnf
  sudo sed -i -e '/\[client\]/a\default-character-set=utf8' /etc/mysql/my.cnf
  fi
  
  Start "restart mysql"
  sudo stop mysql
  sudo start mysql
  sleep 1
  pidof mysqld >/dev/null || Exit
fi

if [ ${view_install} -eq 1 ]; then
    Start "grant user $ISAT_DB_USER permission"
    MySql "GRANT ALL ON $ISAT_DB_NAME.* TO '$ISAT_DB_USER'@'%';"
    Start "import $TABLE tables"
    fieldExist=$(mysql -u $ISAT_DB_USER -p$ISAT_DB_PWD -e "SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '$ISAT_DB_NAME' AND TABLE_NAME = 'query_share' AND COLUMN_NAME = 'enabled';" | grep enabled )
    if [ -z "$fieldExist" ]; then #v2.3.9
      echo "recover v2.x.x view"
      mysql -u $ISAT_DB_USER -p$ISAT_DB_PWD $ISAT_DB_NAME < "$SCRIPT_ROOT/$TABLEv2"
    else
      echo "recover v3.x.x view"
      mysql -u $ISAT_DB_USER -p$ISAT_DB_PWD $ISAT_DB_NAME < "$SCRIPT_ROOT/$TABLEv3"
    fi 
    
fi

if [ ${X02_MEGASYS_enable} -eq 1 ]; then
  insertDB $MEGASYS_DB_NAME $MEGASYS_TABLE
fi
if [ ${X02_GODWATCH_enable} -eq 1 ]; then
  insertDB $GODWATCH_DB_NAME $GODWATCH_TABLE
fi
if [ ${T04_CUSTOMER_enable} -eq 1 ]; then
  #MySql "DROP TABLE IF EXISTS $CUSTOMER_DB_NAME.share_log;" || Exit
  insertDB $CUSTOMER_DB_NAME $CUSTOMER_TABLE
elif [ ${T05_CUSTOMER_enable} -eq 1 ]; then
  dbExist=$(mysql -u $ISAT_DB_USER -p$ISAT_DB_PWD -e "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME ='$CUSTOMER_DB_NAME';" | grep $CUSTOMER_DB_NAME )
  if [ -z "$dbExist" ]; then
      insertDB $CUSTOMER_DB_NAME $CUSTOMER_TABLE
  else #check if old version
    fieldExist=$(mysql -u $ISAT_DB_USER -p$ISAT_DB_PWD -e "SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '$CUSTOMER_DB_NAME' AND TABLE_NAME = 'share_log' AND COLUMN_NAME = 'user_agent';" | grep user_agent )
    if [ -z "$fieldExist" ]; then
    MySql "DROP TABLE IF EXISTS $CUSTOMER_DB_NAME.share_log_old;" || Exit 
    MySql "RENAME TABLE $CUSTOMER_DB_NAME.share_log TO $CUSTOMER_DB_NAME.share_log_old"
    insertDB $CUSTOMER_DB_NAME $CUSTOMER_TABLE
    MySql "ALTER TABLE $CUSTOMER_DB_NAME.share_log AUTO_INCREMENT=0 ENGINE=ARCHIVE;"
    MySql "INSERT INTO $CUSTOMER_DB_NAME.share_log (id, mac,owner_id,owner_name,visitor_id, visitor_name, ts, action, result, ip_addr) SELECT * FROM $CUSTOMER_DB_NAME.share_log_old ORDER BY id;"
    #MySql "DROP TABLE $CUSTOMER_DB_NAME.share_log_old;"
    fi
  fi
elif [ $X02_CUSTOMER_enable -eq 1 -o $C13_WORKEYE_enable -eq 1 -o $P04_CUSTOMER_enable -eq 1 -o $V04_CUSTOMER_enable -eq 1 -o $K01_CUSTOMER_enable -eq 1 ]; then
  insertDB $CUSTOMER_DB_NAME $CUSTOMER_TABLE
#elif [ ${P04_CUSTOMER_enable} -eq 1 ]; then
#  insertDB $CUSTOMER_DB_NAME $CUSTOMER_TABLE
#elif [ ${V04_CUSTOMER_enable} -eq 1 ]; then
#  insertDB $CUSTOMER_DB_NAME $CUSTOMER_TABLE
#elif [ ${K01_CUSTOMER_enable} -eq 1 ]; then
#  insertDB $CUSTOMER_DB_NAME $CUSTOMER_TABLE
fi

if [ ${X02_CAMLIC_enable} -eq 1 ]; then
  insertDB $CAMLIC_DB_NAME $CAMLIC_TABLE

  config_info=$(grep '/home/'$USER'/db/licservice_backup.sh' /etc/crontab)
  if [ -z "$config_info" ]; then #-z test if its empty string
    echo "0 1     * * 0   root  sh /home/$USER/db/licservice_backup.sh" >> /etc/crontab
    echo "set mysqldump on every sunday\n"
  fi
fi

if [ ${Xnn_SYNC_enable} -eq 1 ]; then
	insertDB $CUSTOMER_DB_NAME $DEFAULT_TABLE
fi

Start "flush privileges"
MySql "FLUSH PRIVILEGES;" || Exit

Start "success"
