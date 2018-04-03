ret=0
getent passwd ivedasuper >/dev/null 2>&1 && ret=1
if [ "$ret" -eq 0 ]; then
adduser --gecos "" --disabled-password ivedasuper;echo 'ivedasuper:1qazxdr56yhN' | chpasswd;usermod -a -G sudo ivedasuper
#adduser --gecos "" --disabled-password ivedaadmin;echo 'ivedaadmin:iltwaiveda' | chpasswd;usermod -G sudo ivedaadmin
#userdel ivedaadmin
else
  echo "user ivedasuper exist."
fi 