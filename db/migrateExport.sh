#ISAT_DB_NAME="isat" #was include in the install.conf
CUSTOMER_DB_NAME="customerservice" #T04, P04
CAMLIC_DB_NAME="licservice"

SCRIPT_ROOT=`dirname "$0"`
CONFIG="$SCRIPT_ROOT/../isat_db/install.conf"
. "$CONFIG"


MySql() {
	echo "mysql: $1"
	echo "$1" | mysql -u root -p$MYSQL_ROOT_PASSWORD
	return $?
}

echo "USAGE: sh $0 xpress.sql /var/lib/mysql(event folder path)"
if [ ! -z "$1" ]; then
    if [ ! -z "$2" ]; then
      echo "mysql: export $ISAT_DB_USER.event_log database to $2"
      mysqldump --no-create-info -u $ISAT_DB_USER -p$ISAT_DB_PWD $ISAT_DB_NAME event_log > $2/$ISAT_DB_NAME.event_log.$1
      echo "mysql: export $ISAT_DB_USER.user_log database to $2"
      mysqldump --no-create-info -u $ISAT_DB_USER -p$ISAT_DB_PWD $ISAT_DB_NAME user_log > $2/$ISAT_DB_NAME.user_log.$1
    else
    echo "mysql: export $ISAT_DB_USER.event_log database"
    mysqldump --no-create-info -u $ISAT_DB_USER -p$ISAT_DB_PWD $ISAT_DB_NAME event_log > $ISAT_DB_NAME.event_log.$1
    echo "mysql: export $ISAT_DB_USER.user_log database"
    mysqldump --no-create-info -u $ISAT_DB_USER -p$ISAT_DB_PWD $ISAT_DB_NAME user_log > $ISAT_DB_NAME.user_log.$1
    fi
    echo "mysql: export $ISAT_DB_USER database without user_log, event_log"
    mysqldump -u $ISAT_DB_USER -p$ISAT_DB_PWD $ISAT_DB_NAME --ignore-table=$ISAT_DB_NAME.event_log --ignore-table=$ISAT_DB_NAME.user_log > $ISAT_DB_NAME.$1
    
    echo "mysql: export qlync database"
    mysqldump -u $ISAT_DB_USER -p$ISAT_DB_PWD qlync > qlync.$1
    
    dbExist=$(mysql -u $ISAT_DB_USER -p$ISAT_DB_PWD -e "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME ='$CUSTOMER_DB_NAME';" | grep $CUSTOMER_DB_NAME )
    if [ ! -z "$dbExist" ]; then
      echo "mysql: export $CUSTOMER_DB_NAME database without share_log,api_log,gis_log"
      mysqldump -u $ISAT_DB_USER -p$ISAT_DB_PWD $CUSTOMER_DB_NAME --ignore-table=$CUSTOMER_DB_NAME.share_log --ignore-table=$CUSTOMER_DB_NAME.share_log_old  --ignore-table=$CUSTOMER_DB_NAME.api_log --ignore-table=$CUSTOMER_DB_NAME.gis_log > $CUSTOMER_DB_NAME.$1
  
      shareExist=$(mysql -u $ISAT_DB_USER -p$ISAT_DB_PWD -e "SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'share_log';" | grep share_log)
      if [ ! -z "$shareExist" ]; then
      echo "mysql: export $CUSTOMER_DB_NAME.share_log database"
      mysqldump --no-create-info -u $ISAT_DB_USER -p$ISAT_DB_PWD $CUSTOMER_DB_NAME share_log > $CUSTOMER_DB_NAME.share_log.$1
      fi
      apiExist=$(mysql -u $ISAT_DB_USER -p$ISAT_DB_PWD -e "SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'api_log';" | grep api_log)
      if [ ! -z "$apiExist" ]; then
      echo "mysql: export $CUSTOMER_DB_NAME.api_log database"
      mysqldump --no-create-info -u $ISAT_DB_USER -p$ISAT_DB_PWD $CUSTOMER_DB_NAME api_log > $CUSTOMER_DB_NAME.api_log.$1
      fi
      gisExist=$(mysql -u $ISAT_DB_USER -p$ISAT_DB_PWD -e "SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'gis_log';" | grep gis_log)
      if [ ! -z "$gisExist" ]; then
      echo "mysql: export $CUSTOMER_DB_NAME.gis_log database"
      mysqldump --no-create-info -u $ISAT_DB_USER -p$ISAT_DB_PWD $CUSTOMER_DB_NAME gis_log > $CUSTOMER_DB_NAME.gis_log.$1
      fi
    fi
    dbExist=$(mysql -u $ISAT_DB_USER -p$ISAT_DB_PWD -e "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME ='$CAMLIC_DB_NAME';" | grep $CAMLIC_DB_NAME )
    if [ ! -z "$dbExist" ]; then    
    echo "mysql: export $CAMLIC_DB_NAME database"
    mysqldump -u $ISAT_DB_USER -p$ISAT_DB_PWD $CAMLIC_DB_NAME > $CAMLIC_DB_NAME.$1
    fi
fi