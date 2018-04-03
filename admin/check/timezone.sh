#read parameter $1 (JST) Asia/Tokyo  (ICT)Asia/Ho_Chi_Minh
config1=$(date | tail -c 9 | head -c 3)
if [ ! -z "$1" ]; then
  len=${#1}
  if [ $len -gt 6 ]; then
    sudo cp /usr/share/zoneinfo/$1 /etc/localtime
    echo $1 | sudo tee /etc/timezone
    sudo /etc/init.d/ntp restart
  fi
else
  echo "usage: sh timezone.sh Asia/Ho_Chi_Minh"
fi

config2=$(date | tail -c 9 | head -c 3)
echo "change timezone from $config1 to $config2"
