#!/bin/sh
SCRIPT_ROOT=`dirname "$0"`
CONFIG="$SCRIPT_ROOT/../isat_db/install.conf"
. "$CONFIG"

Exit() {
	echo "\\033[91mInstallation failed at '$PROCEDURE', please refer to FAQ or contact support.\\033[0m"
	exit 1
}
MySql() {
	echo "mysql: $1"
	echo "$1" | mysql -u root -p$MYSQL_ROOT_PASSWORD
	return $?
}

#--repair
#--analyze
if [ "$1" = "check" ]; then
mysqlcheck -u $ISAT_DB_USER -p$ISAT_DB_PWD --databases isat
mysqlcheck -u $ISAT_DB_USER -p$ISAT_DB_PWD --databases qlync      
mysqlcheck -u $ISAT_DB_USER -p$ISAT_DB_PWD --databases customerservice
#mysqlcheck --analyze--databases isat
#mysqlcheck --all-databases
elif [ "$1" = "count" ]; then
MySql "SELECT COUNT(*) as DB FROM information_schema.SCHEMATA;" || Exit
MySql "SELECT COUNT(*) as isat_TABLE FROM information_schema.tables WHERE table_schema = 'isat';" || Exit
MySql "SELECT COUNT(*) as qlync_TABLE FROM information_schema.tables WHERE table_schema = 'qlync';" || Exit
MySql "SELECT COUNT(*) as customerservice_TABLE  FROM information_schema.tables WHERE table_schema = 'customerservice';" || Exit
MySql "select table_name as Views_TABLE from information_schema.views;" || Exit
elif [ "$1" = "utf8" ]; then
  MySql "SELECT COLUMN_NAME,character_set_name FROM information_schema.COLUMNS  WHERE table_schema='qlync' and table_name='account';"
elif [ "$1" = "reset" ]; then
echo "check max index of table isat.stream_server"
#MySql "ALTER TABLE isat.stream_server AUTO_INCREMENT = 469;"
#For InnoDB you cannot set the auto_increment value lower or equal to the highest current index. 
else
  echo "usage: sh checkdb.sh <check/count>"
fi

