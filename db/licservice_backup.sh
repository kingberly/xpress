SCRIPT_ROOT=`dirname "$0"`
CONFIG="$SCRIPT_ROOT/../isat_db/install.conf"
. "$CONFIG"

find /home/ivedasuper/db/sql/  -type f -mtime +30 -exec rm {} +
find  /home/ivedasuper/db/sql/  -type f -mtime +7 -exec gzip {} +
DATEVAR=$(date +%Y-%m-%d)
mysqldump -u $ISAT_DB_USER -p$ISAT_DB_PWD licservice> /home/ivedasuper/db/sql/xpress_licservice"$DATEVAR".sql