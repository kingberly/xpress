#read parameter 1
#eric deploy VM issue 
sudo sed -i -e '/192.168.1.99/d'  /etc/hosts
if [ ! -z "$1" ]; then
config=$(grep $1 /etc/hosts)
  if [ -z "$config" ]; then #substring will also check as exist
    echo $1 > /etc/hostname
    #127.0.1.1       c2
    config_info=$(grep '127.0.1.1' /etc/hosts)
    if [ -z "$config_info" ]; then
      #public IP existed
      sed -i -e '/^127.0.0.1/i\127.0.1.1\t'"$1"'' /etc/hosts
    else 
      sed -i -e '/127.0.1.1/c\127.0.1.1\t'"$1"'' /etc/hosts
      #sed -i -e '/127.0.1.1/c\127.0.1.1\tweb-01' /etc/hosts
    fi
    start hostname
  else
    echo "hostname is already $1"
  fi
else
  echo "no requested hostname input."
fi