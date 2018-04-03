




if [ "$1" = "server" ]; then
  if [ -z "$2" ]; then 
    HOST="192.168.1.200"
  else
    HOST=$2
  fi
  grep 'host'  /var/www/SAT-CLOUDNVR/Mailer/configuration.php
  sudo sed -i -e '/public \$host =/c\    public \$host = \x27'"$HOST"'\x27;'  /var/www/SAT-CLOUDNVR/Mailer/configuration.php
  echo "replace to $HOST"
  if [ -z "$3" ]; then 
    PORT="587"
  else
    PORT=$3
  fi
  grep 'port'  /var/www/SAT-CLOUDNVR/Mailer/configuration.php
  sudo sed -i -e '/public \$port =/c\    public \$port = \x27'"$PORT"'\x27;'  /var/www/SAT-CLOUDNVR/Mailer/configuration.php
  echo "replace to $PORT"
  if [ -z "$2" ]; then 
    USERNAME=""
  else
    USERNAME=$4
  fi
  grep 'username'  /var/www/SAT-CLOUDNVR/Mailer/configuration.php
  sudo sed -i -e '/public \$username =/c\    public \$username = \x27'"$USERNAME"'\x27;'  /var/www/SAT-CLOUDNVR/Mailer/configuration.php
  echo "replace to $USERNAME"
  if [ -z "$2" ]; then 
    PASSWD=""
  else
    PASSWD=$5
  fi
  grep 'password'  /var/www/SAT-CLOUDNVR/Mailer/configuration.php
  sudo sed -i -e '/public \$password =/c\    public \$password = \x27'"$PASSWD"'\x27;'  /var/www/SAT-CLOUDNVR/Mailer/configuration.php
  echo "replace to $PASSWD"
  config_info=$(grep '10.'  /var/www/SAT-CLOUDNVR/Mailer/configuration.php)
  config_info2=$(grep '192.'  /var/www/SAT-CLOUDNVR/Mailer/configuration.php)
  if [ ! -z "$config_info" -o ! -z "$config_info2" ]; then
    sudo sh patch_internalSMTP.sh 1
  fi
fi


if [ "$1" = "from_mail" ]; then
if [ -z "$2" ]; then 
  FROM_MAIL="alarm@tdi-megasys.com"
else
  FROM_MAIL=$2
fi
grep 'from_mail'  /var/www/SAT-CLOUDNVR/Mailer/configuration.php
sudo sed -i -e '/public \$from_mail =/c\    public \$from_mail = \x27'"$FROM_MAIL"'\x27;'  /var/www/SAT-CLOUDNVR/Mailer/configuration.php
echo "replace to $FROM_MAIL"
fi

if [ "$1" = "from_name" ]; then
if [ -z "$2" ]; then
FROM_NAME="IvedaXpress Event Notification"
else
  FROM_NAME=$2
fi
grep 'from_name'  /var/www/SAT-CLOUDNVR/Mailer/configuration.php
sudo sed -i -e '/public \$from_name =/c\    public \$from_name = \x27'"$FROM_NAME"'\x27;'  /var/www/SAT-CLOUDNVR/Mailer/configuration.php
echo "replace to $FROM_NAME"
fi

if [ "$1" = "interval" ]; then
if [ -z "$2" ]; then
INTERVAL="0"
else
  INTERVAL=$2
fi
grep 'interval'  /var/www/SAT-CLOUDNVR/Mailer/configuration.php
sudo sed -i -e '/public \$interval =/c\    public \$interval = \x27'"$INTERVAL"'\x27;'  /var/www/SAT-CLOUDNVR/Mailer/configuration.php
echo "replace to $INTERVAL"
fi

echo "usage: sh setLocalMailer.sh <config> <paramter>\nconfig: server, from_mail, from_name, interval. param: string1 string2 string3 string4"
