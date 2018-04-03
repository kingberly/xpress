#php5 cleanup fail command
SESSION_DIR="/var/lib/php5/sessions"
#WSESSION_DIR="/var/lib/php5"
if [ ! -d "$SESSION_DIR" ]; then
echo "$SESSION_DIR NOT EXIST"
echo "check php5 crontab:"
cat /etc/cron.d/php5
exit 
fi
if [ "$1" = "new" ]; then
#check limitation open files
#ulimit -a | grep open
#/etc/cron.hourly/plesk-php-cleanuper
#ulimit -n 30480
	if [ -d "$SESSION_DIR" ]; then
	sudo rm -rf  $SESSION_DIR
	sudo mkdir  $SESSION_DIR
	sudo chmod 1733  $SESSION_DIR 
	fi
  exit  
else
  echo "to rebuild $SESSION_DIR, type parameter >> new"
fi

#cat /etc/cron.d/php5
config=$(cat /etc/cron.d/php5 | grep $SESSION_DIR)
if [ -z "$config" ]; then
	snum=$(ls -l $SESSION_DIR | wc -l)
	echo "php5 clean session not exist, current sessions: $snum"
	#/etc/cron.d/php5
	sed -i -e '$a\
	09,39 *     * * *     root   [ -x /usr/lib/php5/maxlifetime ] && [ -x /usr/lib/php5/sessionclean ] && [ -d /var/lib/php5/sessions ] && /usr/lib/php5/sessionclean /var/lib/php5/sessions $(/usr/lib/php5/maxlifetime)' /etc/cron.d/php5
fi
config=$(cat /etc/cron.d/php5 | grep $SESSION_DIR)
if [ -z "$config" ]; then
	echo "php5 clean session crontab ADD FAIL"
else
	echo "php5 clean session crontab ADD SUCCESS"
fi
snum=$(ls -l $SESSION_DIR | wc -l)
echo "current sessions: $snum"

 