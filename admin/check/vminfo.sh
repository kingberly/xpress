apt-get -y --force-yes install python-paramiko
#patch check vminfo.py
config_info=$(sed -n '/$oem=/p' /var/www/qlync_admin/doc/config.php | tail -c 7)
oemid=$(echo $config_info | head -c 5) #"X02"

#must run at /home/<user>/admin/check
currDIR=${PWD} #ex => /home/xp41admin/admin
#basedir ex => /home/xp41admin/
BASEDIR=$(echo $currDIR | rev | cut -c 12- | rev) #remove 12 char /admin/check
#echo "curr=$currDIR, base=$BASEDIR"
if [ "${currDIR}" != "${BASEDIR}admin/check" ]; then
  #while sudo, $USER is root
  config_info=$(pwd | grep 'ivedasuper')
  if [ ! -z "$config_info" ]; then #ivedasuper only
  sed -i -e 's/oem=""/oem='"$oemid"'/' /home/ivedasuper/admin/check/vminfo.py 
  echo "update check tool oem cid @ vminfo.py"
  else
  echo "please go to admin/check to execute vminfo.sh"
  fi
else
  sed -i -e 's/oem=""/oem='"$oemid"'/' vminfo.py
  echo "update check tool oem cid at check folder"
fi