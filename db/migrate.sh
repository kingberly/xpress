#rewrite to input extension name with formal prefix
#ISAT_DB_NAME="isat"
CUSTOMER_DB_NAME="customerservice" #T04, P04
CAMLIC_DB_NAME="licservice"

SCRIPT_ROOT=`dirname "$0"`
CONFIG="$SCRIPT_ROOT/../isat_db/install.conf"
. "$CONFIG"

#migration db version, still have debug from 1052 => 2008
ProcessDBMigration()
{
    CURRENT_VERSION=$1
    SCRIPT_ROOT=`dirname "$0"`
    for SCRIPT in ../isat_db/$SCRIPT_ROOT/pre-upgrade/*.sh
    do
    	SCRIPT_VERSION=`basename $SCRIPT | sed -e 's/\..*//'`
    	if [ "$CURRENT_VERSION" -lt "$SCRIPT_VERSION" ]; then
    		echo "\\033[92mUpgrade from $CURRENT_VERSION to $SCRIPT_VERSION.\\033[0m"
    		. "./$SCRIPT"
    	fi
    done
    for SCRIPT in ../isat_db/$SCRIPT_ROOT/upgrade/*.sh
    do
    	SCRIPT_VERSION=`basename $SCRIPT | sed -e 's/\..*//'`
    	if [ "$CURRENT_VERSION" -lt "$SCRIPT_VERSION" ]; then
    		echo "\\033[92mUpgrade from $CURRENT_VERSION to $SCRIPT_VERSION.\\033[0m"
    		. "./$SCRIPT"
    	fi
    done
}

Exit() {
	echo "\\033[91mInstallation failed at '$PROCEDURE', please refer to FAQ or contact support.\\033[0m"
	exit 1
}
MySql() {
	echo "mysql: $1"
	echo "$1" | mysql -u root -p$MYSQL_ROOT_PASSWORD
	return $?
}

#Install new database
echo "Use account / pwd = $ISAT_DB_USER / $ISAT_DB_PWD"
echo "fill 0 for empty parameter, USAGE: $0 (1)isat.sql (2)qlync.sql"
echo "(3)isat.user_log.sql (4)isat.event_log.sql (5)customerservice.sql"
echo "(6)customerservice.share_log.sql (7)customerservice.api_log.sql (8)licservice.sql (9)customerservice.gis_log.sql"

#MySql "GRANT SUPER ON root.* TO '$ISAT_DB_USER'@'%';"
#MySql "GRANT SUPER ON root@'localhost' IDENTIFIED BY '$MYSQL_ROOT_PASSWORD';"
#MySql "GRANT ALL ON $ISAT_DB_NAME.* TO '$ISAT_DB_USER'@'%';"
#MySql "GRANT SUPER ON $ISAT_DB_NAME.* TO '$ISAT_DB_USER'@'%';"
#MySql "GRANT SUPER ON $ISAT_DB_NAME@'localhost' IDENTIFIED BY '$ISAT_DB_PWD';"
MySql "set global log_bin_trust_function_creators=1;"
CURRENTDB_VERSION=`MySql "SELECT value FROM $ISAT_DB_NAME.system_info WHERE name = 'version'" | tail -n +3`

if [ ! -z "$1" -a ! "$1" = "0" ]; then
MySql "CREATE DATABASE IF NOT EXISTS $ISAT_DB_NAME;" || Exit
echo "mysql: import $ISAT_DB_NAME database"
mysql -u $ISAT_DB_USER -p$ISAT_DB_PWD $ISAT_DB_NAME < "$SCRIPT_ROOT/$1"
fi

if [ ! -z "$2" -a ! "$2" = "0" ]; then
MySql "CREATE DATABASE IF NOT EXISTS qlync;" || Exit
echo "mysql: import qlync database"
mysql -u $ISAT_DB_USER -p$ISAT_DB_PWD qlync < "$SCRIPT_ROOT/$2"
fi

if [ ! -z "$3" -a ! "$3" = "0" ]; then
sh archive_newtable.sh user_schema.sql user
MySql "ALTER TABLE isat.user_log AUTO_INCREMENT=0 ENGINE=ARCHIVE;"
echo "mysql: import isat user_log table content only"
mysql -u $ISAT_DB_USER -p$ISAT_DB_PWD $ISAT_DB_NAME < "$SCRIPT_ROOT/$3"
fi

if [ ! -z "$4" -a ! "$4" = "0" ]; then
sh archive_newtable.sh event_schema.sql event
MySql "ALTER TABLE isat.event_log AUTO_INCREMENT=0 ENGINE=ARCHIVE;"
echo "mysql: import isat event_log table content only"
mysql -u $ISAT_DB_USER -p$ISAT_DB_PWD $ISAT_DB_NAME < "$SCRIPT_ROOT/$4"
fi

if [ ! -z "$5" -a ! "$5" = "0" ]; then
MySql "GRANT ALL ON $CUSTOMER_DB_NAME.* TO '$ISAT_DB_USER'@'%';" || Exit
MySql "CREATE DATABASE IF NOT EXISTS $CUSTOMER_DB_NAME;" || Exit
echo "mysql: import $CUSTOMER_DB_NAME database"
mysql -u $ISAT_DB_USER -p$ISAT_DB_PWD $CUSTOMER_DB_NAME < "$SCRIPT_ROOT/$5"
fi

if [ ! -z "$6" -a ! "$6" = "0" ]; then
MySql "GRANT ALL ON $CUSTOMER_DB_NAME.* TO '$ISAT_DB_USER'@'%';" || Exit
MySql "CREATE DATABASE IF NOT EXISTS $CUSTOMER_DB_NAME;" || Exit
#echo "any error occurred, please install $CUSTOMER_DB_NAME via install.sh xx"
fieldExist=$(mysql -u $ISAT_DB_USER -p$ISAT_DB_PWD -e "SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '$CUSTOMER_DB_NAME' AND TABLE_NAME = 'share_log' AND COLUMN_NAME = 'user_agent';" | grep user_agent )
if [ -z "$fieldExist" ]; then
  echo "import T04"
  sh archive_newtable.sh T04_customerservice.sql share
else
  echo "import T05"
  sh archive_newtable.sh T05_customerservice.sql share
fi

MySql "ALTER TABLE $CUSTOMER_DB_NAME.share_log AUTO_INCREMENT=0 ENGINE=ARCHIVE;"
echo "mysql: import $CUSTOMER_DB_NAME.share_log table content only"
MySql "DROP TABLE IF EXISTS $CUSTOMER_DB_NAME.share_log_old;" || Exit
mysql -u $ISAT_DB_USER -p$ISAT_DB_PWD $CUSTOMER_DB_NAME < "$SCRIPT_ROOT/$6"
fi

#ALTER TABLE customerservice.share_log MODIFY id INT KEY;
#ALTER TABLE customerservice.share_log MODIFY id INT;
#ALTER TABLE customerservice.share_log DROP PRIMARY KEY;
#ALTER TABLE customerservice.share_log MODIFY id INT NOT NULL PRIMARY KEY AUTO_INCREMENT;

if [ ! -z "$7" -a ! "$7" = "0" ]; then
sh archive_newtable.sh V04_customerservice.sql api
MySql "ALTER TABLE $CUSTOMER_DB_NAME.api_log AUTO_INCREMENT=0 ENGINE=ARCHIVE;"
echo "mysql: import $CUSTOMER_DB_NAME api_log table content only"
mysql -u $ISAT_DB_USER -p$ISAT_DB_PWD $CUSTOMER_DB_NAME < "$SCRIPT_ROOT/$7"
fi

if [ ! -z "$8" -a ! "$8" = "0" ]; then
MySql "GRANT ALL ON $CAMLIC_DB_NAME.* TO '$ISAT_DB_USER'@'%';" || Exit
MySql "CREATE DATABASE IF NOT EXISTS $CAMLIC_DB_NAME;" || Exit
echo "mysql: import $CAMLIC_DB_NAME database"
mysql -u $ISAT_DB_USER -p$ISAT_DB_PWD $CAMLIC_DB_NAME < "$SCRIPT_ROOT/$8"
fi

if [ ! -z "$9" -a ! "$9" = "0" ]; then
sh archive_newtable.sh X02_customerservice.sql api
MySql "ALTER TABLE $CUSTOMER_DB_NAME.gis_log AUTO_INCREMENT=0 ENGINE=ARCHIVE;"
echo "mysql: import $CUSTOMER_DB_NAME gis_log table content only"
mysql -u $ISAT_DB_USER -p$ISAT_DB_PWD $CUSTOMER_DB_NAME < "$SCRIPT_ROOT/$9"
fi

IMPORT_VERSION=`MySql "SELECT value FROM $ISAT_DB_NAME.system_info WHERE name = 'version'" | tail -n +3`  
#upgrade from IMPORT_VERSION to CURRENTDB_VERSION
if [ "$IMPORT_VERSION" -lt "$CURRENTDB_VERSION" ]; then
  if [ -n "$CURRENTDB_VERSION" ]
  then
  	MySql "REPLACE INTO $ISAT_DB_NAME.system_info (name, value) values ('version', '$CURRENTDB_VERSION');"
  fi
  ProcessDBMigration $IMPORT_VERSION  
fi
