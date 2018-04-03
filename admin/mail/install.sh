#need sudo
DEBIAN_FRONTEND=noninteractive apt-get -y --force-yes install postfix
cp main.cf /etc/postfix/
cp header_check /etc/postfix/
LocalIP=$(ifconfig | grep -Eo 'inet (addr:)?([0-9]*\.){3}[0-9]*' | grep -Eo '([0-9]*\.){3}[0-9]*' | grep -v '127.0.0.1')
#findLineLen=${#LocalIP}
#findLinePos=$(awk -v a="$LocalIP" -v b="." 'BEGIN{print index(a,b)}') #1st .
#findLinePos2=$(($findLineLen - $findLinePos+1))
#LocalIP2=$(echo $LocalIP | tail -c $findLinePos2)  #remove 1st substring
#findLinePos2=$(awk -v a="$LocalIP2" -v b="." 'BEGIN{print index(a,b)}') #2nd .
#findLinePos2nd=$(($findLinePos + $findLinePos2-1)) #1st . length + 2nd . length
#SUBNET=$(echo $LocalIP | head -c $findLinePos2nd) #192.168
#awk seperate -F by . and print out OFS with .
SUBNET2=$(echo $LocalIP | awk -F '.' 'BEGIN { OFS = ".";}{print $1,$2}')
SUBNET=$(echo "$SUBNET2.0.0")
if [ $# -eq 1 ]; then
  if [ ! "$1" = "$SUBNET" ]; then
    sed -i -e 's|^mynetworks = 127.0.0.0/8 \[::ffff:127.0.0.0\]/104 \[::1\]/128|mynetworks = 127.0.0.0\/8 \[::ffff:127.0.0.0\]/104 \[::1\]/128 '"$SUBNET"'/16 '"$1"'/16|' /etc/postfix/main.cf
    echo "set $1, $SUBNET as relay networks."
  else 
    sed -i -e 's|^mynetworks = 127.0.0.0/8 \[::ffff:127.0.0.0\]/104 \[::1\]/128|mynetworks = 127.0.0.0\/8 \[::ffff:127.0.0.0\]/104 \[::1\]/128 '"$1"'/16|' /etc/postfix/main.cf
    echo "set $1 as relay networks."
  fi
elif [ $# -eq 2 ]; then
  sed -i -e 's|^mynetworks = 127.0.0.0/8 \[::ffff:127.0.0.0\]/104 \[::1\]/128|mynetworks = 127.0.0.0\/8 \[::ffff:127.0.0.0\]/104 \[::1\]/128 '"$1"'/16 '"$2"'/16|' /etc/postfix/main.cf
  echo "set $1 , $2 as relay networks."
elif [ $# -eq 3 ]; then
  sed -i -e 's|^mynetworks = 127.0.0.0/8 \[::ffff:127.0.0.0\]/104 \[::1\]/128|mynetworks = 127.0.0.0\/8 \[::ffff:127.0.0.0\]/104 \[::1\]/128 '"$1"'/16 '"$2"'/16 '"$3"'/16|' /etc/postfix/main.cf
  echo "set $1 , $2 , $3 as relay networks."
else
  sed -i -e 's|^mynetworks = 127.0.0.0/8 \[::ffff:127.0.0.0\]/104 \[::1\]/128|mynetworks = 127.0.0.0\/8 \[::ffff:127.0.0.0\]/104 \[::1\]/128 '"$SUBNET"'/16|' /etc/postfix/main.cf
  echo "set $SUBNET as relay networks."
fi
if [ -f "/var/www/qlync_admin/doc/config.php" ]; then
#admin oemid
config_info=$(sed -n '/$oem=/p' /var/www/qlync_admin/doc/config.php | tail -c 7)
oemid=$(echo $config_info | head -c 5) #"P04"
else
#web oemid
oemid=$(sed -n '/OEM_ID=/p' ../../iSVW_install_scripts/install.conf | tail -c 6)
fi 
if [ "$oemid" = '"P04"' ]; then
    #sed -i -e 's|^relayhost =|relayhost = 10.31.20.80|' /etc/postfix/main.cf
    #/etc/init.d/postfix check
    #echo "set PLDT relayhost to PLDT mail server 10.31.20.80"
    echo "NOT SET PLDT relayhost, use mail server 192.168.0.14 instead"
fi

#enable port 587 / 25
sed -i -e 's|smtp      inet  n       -       -       -       -       smtpd|587      inet  n       -       n       -       -       smtpd|' /etc/postfix/master.cf
echo "add local port 587 as email port"
sed -i -e '/^587      inet/i\25      inet  n       -       n       -       -       smtpd' /etc/postfix/master.cf
echo "add local port 25 as email port"
sudo /etc/init.d/postfix reload   