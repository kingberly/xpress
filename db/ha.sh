#!/bin/sh
#/etc/mysql/my.cnf
DB_MASTER_IP="192.168."

#strindex string targetString
strindex()
{
  x="${1%%$2*}"
  [ "$x" = "$1" ] && echo -1 || echo ${#x}
}
#getPwd targetString
getPwd()
{
  x=$(sed -n '/'"$1"'/p' ../isat_db/install.conf)
  len=${#x} #36
  pwd1pos=$(strindex $x "=")  #19
  pwdlen=$(($len-$pwd1pos-1))
  x=$(echo $x | tail -c $pwdlen)
  pwdlen=$(($pwdlen-2))
  x=$(echo $x | head -c $pwdlen )
  echo $x
}

mysqlRootPwd=$(getPwd 'MYSQL_ROOT_PASSWORD="')
#echo $mysqlRootPwd
if [ "$mysqlRootPwd" = "mysql_root_password" -o -z $mysqlRootPwd ]; then
  #MYSQL_ROOT_PASSWORD="S3V2A3VgrFCF6xiqdBcH"  #for Z02
  MYSQL_ROOT_PASSWORD="ivedaMysqlRoot"
else
  MYSQL_ROOT_PASSWORD=$mysqlRootPwd
fi

MySql() {
	echo "mysql: $1"
	echo "$1" | mysql -u root -p$MYSQL_ROOT_PASSWORD
	return $?
}

if [ -z "$1" ]; then #empty
  echo "usage: (master db)sh ha.sh master\n(slave db)sh ha.sh slave 192.168.1.141\n(stop)sh ha.sh stop"
  config_info=$(pgrep -l mysql)
  if [ -z "$config_info" ]; then
    sudo stop mysql
    sudo start mysql
  fi
  pgrep -l mysql
  MySql "show master status \G;"
  MySql "show slave status \G;"
elif [ "$1" = "stop" ]; then #run stop
    MySql "STOP SLAVE;RESET SLAVE"
    MySql "STOP MASTER;RESET MASTER"
    #replicate-same-server-id = 0
    sed -i -e '/auto-increment-increment/d' /etc/mysql/my.cnf
    sed -i -e '/auto-increment-offset/d' /etc/mysql/my.cnf
    sudo stop mysql
    sudo start mysql
    #auto_increment_increment= 2
    #master1/slave2
    #auto_increment_offset   = 1
    #master2/slave1
    #auto_increment_offset   = 2
elif [ "$1" = "master" ]; then #run master
    echo "run master database setting in my.cnf"
    config_info=$(grep '#server-id' /etc/mysql/my.cnf)
    if [ ! -z "$config_info" ]; then
       sed -i -e '/#server-id/d' /etc/mysql/my.cnf
       sed -i -e '/expire_logs_days/ i\server-id              = 1' /etc/mysql/my.cnf
       echo "delete #server-id and add server-id 2 to my.cnf"
    else
       config_info1=$(grep 'server-id' /etc/mysql/my.cnf)
       if [ ! -z "$config_info1" ]; then
         sed -i -e '/server-id/d' /etc/mysql/my.cnf
         sed -i -e '/expire_logs_days/ i\server-id              = 1' /etc/mysql/my.cnf
         echo "delete and add server-id to my.cnf" 
       else
         sed -i -e '/expire_logs_days/ i\server-id              = 1' /etc/mysql/my.cnf
         echo "add server-id to my.cnf"
       fi 
    fi
    #log_bin                        = /var/log/mysql/mysql-bin.log
    config_info=$(grep '#log_bin' /etc/mysql/my.cnf)
    if [ ! -z "$config_info" ]; then
       sed -i -e 's|^#log_bin                        = /var/log/mysql/mysql-bin.log|log_bin                        = /var/log/mysql/mysql-bin.log|' /etc/mysql/my.cnf   
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
    echo "confirm max_binlog_size is 100M => $config_info "
    
    MySql "GRANT REPLICATION SLAVE ON *.* TO 'x41db'@'%' IDENTIFIED BY 'x41dbRoot';"
    #MySql "FLUSH PRIVILEGES;FLUSH TABLES WITH READ LOCK;"
    #MySql "unlock tables;"
    MySql "RESET MASTER;"
    sudo stop mysql
    sudo start mysql
    MySql "show master status \G;"
    #>select host, user, password from mysql.user;
    #grep mysql /var/log/syslog 
elif [ "$1" = "slave" ]; then #run slave
  if [ ! -z "$2" ]; then #2nd parameter ip
    echo "run slave database setting in my.cnf.\nPlease import database data before setting slave."
    DB_MASTER_IP=$2
    config_info=$(grep '#server-id' /etc/mysql/my.cnf)
    if [ ! -z "$config_info" ]; then
       sed -i -e '/#server-id/d' /etc/mysql/my.cnf
       sed -i -e '/expire_logs_days/ i\server-id              = 2' /etc/mysql/my.cnf
       echo "delete #server-id and add server-id 2 to my.cnf"
    else
       config_info1=$(grep 'server-id' /etc/mysql/my.cnf)
       if [ ! -z "$config_info1" ]; then
         sed -i -e '/server-id/d' /etc/mysql/my.cnf
         sed -i -e '/expire_logs_days/ i\server-id              = 2' /etc/mysql/my.cnf
         echo "delete and add server-id 2 to my.cnf" 
       else
         sed -i -e '/expire_logs_days/ i\server-id              = 2' /etc/mysql/my.cnf
         echo "add server-id 2 to my.cnf"
       fi 
    fi

    MySql "change MASTER to master_host='$DB_MASTER_IP', master_user='x41db', master_password='x41dbRoot';"
    MySql "STOP SLAVE;RESET SLAVE"
    MySql "START SLAVE;"
    sudo stop mysql
    sudo start mysql
    MySql "SHOW SLAVE STATUS \G;"
  else
    echo "No DB_MASTER_IP parameter!!"
    MySql "SHOW SLAVE STATUS \G;"
  fi

fi
  