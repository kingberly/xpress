#systemd status apt-daily.service
#grep exact word systemd
#dpkg -l | grep -w '\ssystemd\s'

FILE="/etc/apt/apt.conf.d/10periodic"
#APT::Periodic::Update-Package-Lists "0";
#APT::Periodic::Download-Upgradeable-Packages "0";
if [ ! -z "$FILE" ]; then
  echo "$FILE status:"
  cat $FILE
fi

FILE="/etc/apt/apt.conf.d/20auto-upgrades"
if [ ! -z "$FILE" ]; then
  echo "$FILE status:"
  cat $FILE
  sed -i -e 's|APT::Periodic::Unattended-Upgrade "1";|APT::Periodic::Unattended-Upgrade "0";|' $FILE
  echo "Unattended-Upgrade Turn Off!!"
fi 