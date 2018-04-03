#!/bin/sh
if [ "$1" = "remove" ]; then
    sudo mysql stop
    sudo /usr/local/bin/isat_db.py stop
    sudo rm /usr/local/bin/isat_db.py
    sudo rm /etc/isat_db.conf
    echo "stop mysql service and remove isat_db files."
    exit  
else
  echo "to remove server usage: sudo sh patch.sh remove"
fi

#sed -i -e "\$a1 1 * * * root  sh /home/ivedasuper/db/mybackup.sh" /etc/crontab
config_info=$(grep 'mybackup.sh' /etc/crontab)
if [ -z "$config_info" ]; then #-z test if its empty string
if [ -f "mybackup.sh" ]; then
curPATH=$(readlink -f mybackup.sh)
sed -i -e "\$a1 1 * * * root  sh $curPATH" /etc/crontab
echo "add crontab backup db to megasys ftp @1:1"
fi
fi 
#every month check user_log performance
#select count(*) from isat.user_log;
#sh archive_newtable.sh user_schema.sql user

#ISAT_DB_NAME="isat"
CUSTOMER_DB_NAME="customerservice"
CAMLIC_DB_NAME="licservice"
GODWATCH_DB_NAME="godwatch"
TRACCAR_DB_NAME="traccar"

SCRIPT_ROOT=`dirname "$0"`
CONFIG="$SCRIPT_ROOT/../isat_db/install.conf"
. "$CONFIG"
 
Start() {
	echo "\\033[92m$1\\033[0m"
	PROCEDURE="$1"
}
MySql() {
	echo "mysql: $1"
	echo "$1" | mysql -u root -p$MYSQL_ROOT_PASSWORD
	return $?
}

grantDB () {
  dbExist=$(mysql -u root -p$MYSQL_ROOT_PASSWORD -e "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME ='$1';" | grep $1 )
  if [ ! -z "$dbExist" ]; then   
  Start "grant user $ISAT_DB_USER permission"
  MySql "GRANT ALL ON $1.* TO '$ISAT_DB_USER'@'%';" || Exit
  fi
}
#first time manual install special db request
#Start "If Database is not existed, perform installation"
#installDB=$(MySql "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$CUSTOMER_DB_NAME'")
#if [ "${installDB}" != "" ]; then
#  sh install.sh
#fi
#patch bug from 2014 to 2015
PARTNER_DB_NAME="qlync"
DB_VERSION=`MySql "SELECT value FROM $ISAT_DB_NAME.system_info WHERE name = 'version'" | tail -n +3`
INSTALL_VERSION=$(cat ../isat_db/version)
if [ "$DB_VERSION" = "2014" -a "$INSTALL_VERSION" = "2015" ]; then
  fieldExist=$(mysql -u $ISAT_DB_USER -p$ISAT_DB_PWD -e "SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '$PARTNER_DB_NAME' AND TABLE_NAME = 'account' AND COLUMN_NAME = 'AID';" | grep 'AID' )
  fieldExist2=$(mysql -u $ISAT_DB_USER -p$ISAT_DB_PWD -e "SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '$PARTNER_DB_NAME' AND TABLE_NAME = 'right_tree' AND COLUMN_NAME = 'AID';" | grep 'AID' )
  if [ -z "$fieldExist" -o -z "$fieldExist2" ]; then
    . ./pre2015.sh
    echo "patch AID"
    cd ../isat_db
    sudo sh install.sh
    cd ..
  fi 
fi

grantDB $CUSTOMER_DB_NAME

grantDB $CAMLIC_DB_NAME

grantDB $GODWATCH_DB_NAME

grantDB $TRACCAR_DB_NAME 

Start "flush privileges"
MySql "FLUSH PRIVILEGES;" || Exit