#!/bin/sh
#/etc/mysql/my.cnf
SCRIPT_ROOT=`dirname "$0"`
CONFIG="$SCRIPT_ROOT/../isat_db/install.conf"
. "$CONFIG"

MySql() {
	echo "mysql: $1"
	echo "$1" | mysql -u root -p$MYSQL_ROOT_PASSWORD
	return $?
}
myPrintAlert(){
  echo "\\033[31m$1\\033[0m"
}

if [ -z "$1" ]; then #empty
  echo "usage: (config my.cnf) sudo sh dbmm.sh config <id1/id2/id3>"
  echo "(master)sh dbmm.sh master"
  echo "(slave)sh dbmm.sh slave <IP> <binlog> <log pos>"
  echo "(status)sh dbmm.sh status"
  echo "(stop)sh dbmm.sh stop"
  echo "(slave skip1)sh dbmm.sh slave_start"
  echo "(clean binlog)sh dbmm.sh clean 000020"
  config_info=$(pgrep -l mysql)
  if [ -z "$config_info" ]; then
    sudo stop mysql
    sudo start mysql
  fi
  #MySql "SET GLOBAL LOG_WARNINGS = 1;" #turn off BIN_LOG warning for v5.6.4
  pgrep -l mysql
elif [ "$1" = "stop" ]; then #run stop
    MySql "STOP SLAVE;"
    MySql "STOP MASTER;"
elif [ "$1" = "slave_start" ]; then #run slave only
    MySql "RESET SLAVE;"
    MySql "SET GLOBAL SQL_SLAVE_SKIP_COUNTER = 1;"
    MySql "START SLAVE;"
    MySql "SHOW SLAVE STATUS \G;"
elif [ "$1" = "clean" ]; then #run slave only
    MySql "FLUSH LOGS;"
    MySql "SET GLOBAL binlog_format = 'ROW';"
    if [ ! -z "$2" ]; then
    MySql "PURGE BINARY LOGS TO ‘mysql-bin.$2′;"
    fi
    MySql "STOP SLAVE;RESET SLAVE;"
    MySql "SHOW SLAVE STATUS \G;"
    #MySql "RESET MASTER;"
    #MySql "SHOW MASTER STATUS \G;"

elif [ "$1" = "slave" ]; then #run slave only
    while true; do
        read -p "import data before proceeding!!! Start now [y/n]?" yn
        case $yn in
            [Yy]* ) break;;
            [Nn]* ) exit;;
            * ) echo "Please answer y or n.";;
        esac
    done
    if [ -z "$2" -o -z "$3" -o -z "$4"  ]; then
      echo "usage: sh dbmm.sh slave <IP> <binlog> <log pos>"
      exit
    fi   
    DB_MASTER_IP=$2
    MySql "RESET SLAVE;"
    MySql "FLUSH TABLES WITH READ LOCK;"
    MySql "FLUSH LOGS;"
    #MySql "SET GLOBAL binlog_format = 'MIXED';"
    MySql "SET GLOBAL binlog_format = 'ROW';"
    MySql "FLUSH LOGS;"
    MySql "change MASTER to MASTER_HOST='$DB_MASTER_IP', MASTER_USER='x41db', MASTER_PASSWORD='x41dbRoot', MASTER_LOG_FILE='$3', MASTER_LOG_POS=$4;"
    MySql "START SLAVE;"
MySql "SHOW SLAVE STATUS \G;"

elif [ "$1" = "master" ]; then #run master

    MySql "RESET MASTER;"
    MySql "GRANT REPLICATION SLAVE ON *.* TO 'x41db'@'%' IDENTIFIED BY 'x41dbRoot';"
    #lock table
    MySql "SHOW MASTER STATUS;"
    myPrintAlert "KEEP LOG_FILENAME, POS for slave setting\n"
    MySql "FLUSH PRIVILEGES;FLUSH TABLES WITH READ LOCK;"
    MySql "FLUSH LOGS;"
    #MySql "SET GLOBAL binlog_format = 'MIXED';"
    MySql "SET GLOBAL binlog_format = 'ROW';"
    MySql "FLUSH LOGS;"
    #export db
    sqlFilename=$(echo "`date '+%Y%m%d_%H%M'`.sql")
    sh migrateExport.sh $sqlFilename

    MySql "unlock tables;"
elif [ "$1" = "config" ]; then
    #replicate-same-server-id = 0
    #sed -i -e '/auto-increment-increment/d' /etc/mysql/my.cnf
    #sed -i -e '/auto-increment-offset/d' /etc/mysql/my.cnf
    #log_bin                        = /var/log/mysql/mysql-bin.log
    config_info=$(grep '#log_bin' /etc/mysql/my.cnf)
    if [ ! -z "$config_info" ]; then
       sed -i -e 's|^#log_bin|log_bin|' /etc/mysql/my.cnf
       echo "update log_bin to my.cnf"
    else
       config_info1=$(grep 'log_bin' /etc/mysql/my.cnf)
       if [ ! -z "$config_info1" ]; then
         sed -i -e '/log_bin/d' /etc/mysql/my.cnf
         sed -i -e '/expire_logs_days/ i\log_bin                        = /var/log/mysql/mysql-bin.log' /etc/mysql/my.cnf
         echo "delete and add log_bin to my.cnf" 
       else
         sed -i -e '/expire_logs_days/ i\log_bin                        = /var/log/mysql/mysql-bin.log' /etc/mysql/my.cnf
         echo "add log_bin to my.cnf" 
       fi
    fi
    #expire_logs_days        = 10
    config_info=$(grep 'expire_logs_days' /etc/mysql/my.cnf)
    if [ ! -z "$config_info" ]; then
       sed -i -e '/expire_logs_days/d' /etc/mysql/my.cnf
       sed -i -e '/max_binlog_size/ i\expire_logs_days        = 200' /etc/mysql/my.cnf
       #sed -i -e 's|^expire_logs_days        = 10|expire_logs_days        = 200|' /etc/mysql/my.cnf   
       echo "update expire_logs_days to my.cnf"
    else
         sed -i -e '/max_binlog_size/ i\expire_logs_days        = 200' /etc/mysql/my.cnf
         echo "add expire_logs_days to my.cnf"
    fi
    
    #max_binlog_size         = 100M
    config_info=$(grep 'max_binlog_size' /etc/mysql/my.cnf)
    echo "confirm max_binlog_size is 100M => $config_info @/etc/mysql/my.cnf"
    #add innodb with transaction
    config_info=$(grep 'innodb_flush_log_at_trx_commit' /etc/mysql/my.cnf)
    if [ ! -z "$config_info" ]; then
        sed -i -e '/innodb_flush_log_at_trx_commit/d' /etc/mysql/my.cnf
        sed -i -e '/sync_binlog/d' /etc/mysql/my.cnf
        sed -i -e '$a\innodb_flush_log_at_trx_commit = 1'  /etc/mysql/my.cnf
        sed -i -e '$a\sync_binlog = 1'  /etc/mysql/my.cnf
        echo "update innodb_flush_log_at_trx_commit/sync_binlog to my.cnf"
    else  
        sed -i -e '$a\innodb_flush_log_at_trx_commit = 1'  /etc/mysql/my.cnf
        sed -i -e '$a\sync_binlog = 1'  /etc/mysql/my.cnf
        echo "add innodb_flush_log_at_trx_commit/sync_binlog to my.cnf"
    fi
    #skip duplcate entry error
    config_info=$(grep 'slave-skip-error' /etc/mysql/my.cnf)
    if [ -z "$config_info" ]; then
         sed -i -e '$a\slave-skip-errors = 1062' /etc/mysql/my.cnf
         #sed -i -e '/slave-skip-errors/d' /etc/mysql/my.cnf
         echo "add slave-skip-errors =1062 to my.cnf"
    fi
    DB_MASTER_IP=$3

    if [ "$2" = "id3" ]; then
    config_info=$(grep '#server-id' /etc/mysql/my.cnf)
    if [ ! -z "$config_info" ]; then
       sudo sed -i -e '/#server-id/d' /etc/mysql/my.cnf
       sudo sed -i -e '/expire_logs_days/ i\server-id              = 3' /etc/mysql/my.cnf
       echo "delete #server-id and add server-id 3 to my.cnf"
    else
       config_info1=$(grep 'server-id' /etc/mysql/my.cnf)
       if [ ! -z "$config_info1" ]; then
         echo "server-id exist: $config_info1, please check /etc/mysql/my.cnf"
         exit 
       else
         sed -i -e '/expire_logs_days/ i\server-id              = 3' /etc/mysql/my.cnf
         echo "add server-id 3 to my.cnf"
       fi 
    fi

    elif [ "$2" = "id2" ]; then
    config_info=$(grep '#server-id' /etc/mysql/my.cnf)
    if [ ! -z "$config_info" ]; then
       sudo sed -i -e '/#server-id/d' /etc/mysql/my.cnf
       sudo sed -i -e '/expire_logs_days/ i\server-id              = 2' /etc/mysql/my.cnf
       echo "delete #server-id and add server-id 2 to my.cnf"
    else
       config_info1=$(grep 'server-id' /etc/mysql/my.cnf)
       if [ ! -z "$config_info1" ]; then
         #sed -i -e '/server-id/d' /etc/mysql/my.cnf
         #sed -i -e '/expire_logs_days/ i\server-id              = 2' /etc/mysql/my.cnf
         echo "server-id exist: $config_info1, please check /etc/mysql/my.cnf"
         exit
       else
         sed -i -e '/expire_logs_days/ i\server-id              = 2' /etc/mysql/my.cnf
         echo "add server-id 2 to my.cnf"
       fi 
    fi

    elif [ "$2" = "id1" ]; then
    config_info=$(grep '#server-id' /etc/mysql/my.cnf)
    if [ ! -z "$config_info" ]; then
       sudo sed -i -e '/#server-id/d' /etc/mysql/my.cnf
       sudo sed -i -e '/expire_logs_days/ i\server-id              = 1' /etc/mysql/my.cnf
       echo "delete #server-id and add server-id 1 to my.cnf"
    else
       config_info1=$(grep 'server-id' /etc/mysql/my.cnf)
       if [ ! -z "$config_info1" ]; then
         #sed -i -e '/server-id/d' /etc/mysql/my.cnf
         #sed -i -e '/expire_logs_days/ i\server-id              = 1' /etc/mysql/my.cnf
         echo "server-id exist: $config_info1, please check /etc/mysql/my.cnf"
         exit 
       else
         sed -i -e '/expire_logs_days/ i\server-id              = 1' /etc/mysql/my.cnf
         echo "add server-id 1 to my.cnf"
       fi 
    fi

    else #no parameter, exit
      echo "usage:sh dbmm.sh config <id1/id2/id3>\n"
      exit
    fi #$2 id1 or 2
    sudo stop mysql;sudo start mysql
    #sudo /etc/init.d/mysql restart #fail to connect back    
elif [ "$1" = "status" ]; then
MySql "SHOW MASTER STATUS \G;"
MySql "SHOW SLAVE STATUS \G;"
fi
MySql "select COUNT(*) from isat.recording_list;"  
#MySql "select COUNT(*) from customerservice.share_log;"
MySql "select COUNT(*) from isat.user_log;"