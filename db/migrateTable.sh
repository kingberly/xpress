SCRIPT_ROOT=`dirname "$0"`
CONFIG="$SCRIPT_ROOT/../isat_db/install.conf"
. "$CONFIG"

MySql() {
	echo "mysql: $1"
	echo "$1" | mysql -u root -p$MYSQL_ROOT_PASSWORD
	return $?
}
echo "USAGE: sh migrateTable.sh <1 import/export> <2 xxx.sql> <3 dbname> <4 tablename> <5 --no-create-info>"
if [ ! -z "$4" -a ! -z "$2" -a ! -z "$3" ]; then
    dbExist=$(mysql -u $ISAT_DB_USER -p$ISAT_DB_PWD -e "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME ='$3';" | grep $3 )
  if [ "$1" = "export" ]; then
    if [ ! -z "$dbExist" ]; then
      if [ ! -z "$5" ]; then 
        mysqldump --no-create-info -u $ISAT_DB_USER -p$ISAT_DB_PWD $3 $4 > $3.$4.$2
      else
        mysqldump -u $ISAT_DB_USER -p$ISAT_DB_PWD $3 $4 > $3.$4.$2
      fi
      echo "mysql: export $3.$4 data to file $3.$4.$2"
    fi
  elif [ "$1" = "import" ]; then
    MySql "CREATE DATABASE IF NOT EXISTS $3;" || Exit
    mysql -u $ISAT_DB_USER -p$ISAT_DB_PWD $3 < $2
    echo "mysql: import $3.$4 data from file $2"
  fi
fi