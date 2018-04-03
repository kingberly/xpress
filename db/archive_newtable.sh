
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
myPrintInfo(){
  echo "\\033[92m$1\\033[0m"
}

checkCUSTOMERExist(){
  aExist=$(mysql -u $ISAT_DB_USER -p$ISAT_DB_PWD -e "SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = '$1';" | grep '$1')
  if [ ! -z "$aExist" ]; then
      myPrintInfo "Check $1 table"
      MySql "Show columns from $CUSTOMER_DB_NAME.$1;"
      myPrintInfo "Current rows in table $1"
      MySql "SELECT count(*) from $CUSTOMER_DB_NAME.$1;"
  fi
}
echo "USAGE: archive_newtable.sh schema.sql event/user/share"
if [ ! -z "$1" ]; then
  if [ "$2" = "event" ]; then
    echo "grant user $ISAT_DB_USER permission"
    MySql "GRANT ALL ON $ISAT_DB_NAME.* TO '$ISAT_DB_USER'@'%';" || Exit
    echo "drop event_log"
    MySql "DROP TABLE IF EXISTS $ISAT_DB_NAME.event_log;" || Exit
    echo "mysql: import $ISAT_DB_NAME $1"
    mysql -u $ISAT_DB_USER -p$ISAT_DB_PWD $ISAT_DB_NAME < $1
  elif [ "$2" = "user" ]; then
    echo "grant user $ISAT_DB_USER permission"
    MySql "GRANT ALL ON $ISAT_DB_NAME.* TO '$ISAT_DB_USER'@'%';" || Exit
    echo "drop user_log"
    MySql "DROP TABLE IF EXISTS $ISAT_DB_NAME.user_log;" || Exit
    echo "mysql: import $ISAT_DB_NAME $1"
    mysql -u $ISAT_DB_USER -p$ISAT_DB_PWD $ISAT_DB_NAME < $1
  elif [ "$2" = "share" ]; then
    echo "grant user $ISAT_DB_USER permission"
    MySql "GRANT ALL ON $CUSTOMER_DB_NAME.* TO '$ISAT_DB_USER'@'%';" || Exit
    echo "drop share_log"
    MySql "DROP TABLE IF EXISTS $CUSTOMER_DB_NAME.share_log;" || Exit    
    echo "mysql: import $CUSTOMER_DB_NAME $1"
    mysql -u $ISAT_DB_USER -p$ISAT_DB_PWD $CUSTOMER_DB_NAME < $1
  elif [ "$2" = "api" ]; then
    echo "grant user $ISAT_DB_USER permission"
    MySql "GRANT ALL ON $CUSTOMER_DB_NAME.* TO '$ISAT_DB_USER'@'%';" || Exit
    echo "drop api_log"
    MySql "DROP TABLE IF EXISTS $CUSTOMER_DB_NAME.api_log;" || Exit    
    echo "mysql: import $CUSTOMER_DB_NAME $1"
    mysql -u $ISAT_DB_USER -p$ISAT_DB_PWD $CUSTOMER_DB_NAME < $1
  elif [ ! -z "$2" ]; then
    myPrintInfo "Required specified table name parameter"
  fi
else
  myPrintInfo "No database/table schema .sql!!"

  myPrintInfo "Check event_log table"
  MySql "Show columns from $ISAT_DB_NAME.event_log;"
  myPrintInfo "Current rows in table event_log"
  MySql "SELECT count(*) from $ISAT_DB_NAME.event_log;"
  
  myPrintInfo "Check user_log table"
  MySql "Show columns from $ISAT_DB_NAME.user_log;"
  myPrintInfo "Current rows in table user_log"
  MySql "SELECT count(*) from $ISAT_DB_NAME.user_log;"
  
  #shareExist=$(mysql -u $ISAT_DB_USER -p$ISAT_DB_PWD -e "SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'share_log';" | grep 'share_log')
  #if [ ! -z "$shareExist" ]; then
  #    myPrintInfo "Check share_log table"
  #    MySql "Show columns from $CUSTOMER_DB_NAME.share_log;"
  #    myPrintInfo "Current rows in table share_log"
  #    MySql "SELECT count(*) from $CUSTOMER_DB_NAME.share_log;"
  #fi
  checkCUSTOMERExist "share_log"
  checkCUSTOMERExist "api_log"

fi
