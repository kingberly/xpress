SCRIPT_ROOT=`dirname "$0"`
CONFIG="$SCRIPT_ROOT/../isat_db/install.conf"
. "$CONFIG"
MySql() {
	echo "mysql: $1"
	echo "$1" | mysql -u root -p$MYSQL_ROOT_PASSWORD
	return $?
}
PARTNER_DB_NAME="qlync"

#MySql "SELECT COLUMN_NAME,character_set_name FROM information_schema.`COLUMNS`  WHERE table_schema='qlync' and table_name='account';"
#(latin1)select ID,Email,SUBSTR(Email,1,5) as S,DECODE(Password,SUBSTR(Email,1,5))as PPWD from qlync.account;
#(utf8)select ID,Email,SUBSTR(Email,1,5) as S,CONVERT(CAST(DECODE(Password,SUBSTR(Email,1,5))as BINARY)USING UTF8) as PPWD from qlync.account;

  echo "EXPORT qlync.account to xxx.sql by exportdb.php and IMPORT: sh migrate.php 0 xxx.sql"
#MySql "ALTER TABLE $PARTNER_DB_NAME.account ENGINE=InnoDB;"
#MySql "ALTER TABLE $PARTNER_DB_NAME.account CONVERT TO CHARACTER SET utf8;"

MySql "ALTER TABLE $PARTNER_DB_NAME.account_device CONVERT TO CHARACTER SET utf8;"
MySql "ALTER TABLE $PARTNER_DB_NAME.account_sales ENGINE=InnoDB;"
MySql "ALTER TABLE $PARTNER_DB_NAME.account_sales CONVERT TO CHARACTER SET utf8;"

MySql "ALTER TABLE $PARTNER_DB_NAME.end_user_list CONVERT TO CHARACTER SET utf8;"
MySql "ALTER TABLE $PARTNER_DB_NAME.license ENGINE=InnoDB;"

MySql "ALTER TABLE $PARTNER_DB_NAME.login_log ENGINE=InnoDB;"

MySql "ALTER TABLE $PARTNER_DB_NAME.menu ENGINE=InnoDB;"

MySql "ALTER TABLE $PARTNER_DB_NAME.oem_info ENGINE=InnoDB;"
MySql "ALTER TABLE $PARTNER_DB_NAME.oem_info CONVERT TO CHARACTER SET utf8;"

MySql "ALTER TABLE $PARTNER_DB_NAME.push_log CONVERT TO CHARACTER SET utf8;"
MySql "ALTER TABLE $PARTNER_DB_NAME.right_tree ENGINE=InnoDB;"

MySql "ALTER TABLE $PARTNER_DB_NAME.service_log CONVERT TO CHARACTER SET utf8;"

MySql "ALTER TABLE $PARTNER_DB_NAME.sys_log CONVERT TO CHARACTER SET utf8;"