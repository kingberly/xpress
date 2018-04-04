TARGET_FOLDER="/var/www/SAT-CLOUDNVR/map" 
TARGET_FOLDERA="/var/www/qlync_admin/plugin/rpic"
if [ -d "/var/www/qlync_admin/" ]; then
	TARGET_FOLDER= $TARGET_FOLDERA 
fi
if [ "$1" = "remove" ]; then
    config_info=$(grep 'rpic_file.php' /etc/crontab)
    if [ ! -z "$config_info" ]; then
        sed -i -e '/rpic_file.php/d'  /etc/crontab
				rm -rf  ${TARGET_FOLDER}/rpic*
				rm -rf  ${TARGET_FOLDER}/image/
        echo "cleanup rpic map plugin"
    fi
    exit   
fi
if [ ! -d "$TARGET_FOLDER" ]; then
	mkdir $TARGET_FOLDER
fi
cp ../rpic.inc $TARGET_FOLDER
cp rpic* $TARGET_FOLDER
cp -avr image $TARGET_FOLDER

config_info=$(grep 'rpic_file.php' /etc/crontab)
if [ -z "$config_info" ]; then
	if [ -d "/var/www/qlync_admin/" ]; then
		sed -i -e '$a\*/5 * * * * root  /usr/bin/php5  "/var/www/qlync_admin/plugin/rpic/rpic_file.php" N99' /etc/crontab
	else
		sed -i -e '$a\*/5 * * * * root  /usr/bin/php5  "/var/www/SAT-CLOUDNVR/map/rpic_file.php" N99' /etc/crontab
	fi
fi