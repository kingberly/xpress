
config=$(cat /etc/lsb-release  | grep DISTRIB_RELEASE | tail -c 6)
if [ "$config" = "12.04" ]; then
  exit
fi
if [ -z "$1" ]; then
  conn="250"
elif [ "$1" = "remove" ]; then
  sudo sed -i -e '/limit_conn_zone/d' /etc/nginx/sites-available/nginx-splitter
  sudo sed -i -e '/limit_conn perserver/d' /etc/nginx/sites-available/nginx-splitter
  echo "remove limit_conn from /etc/nginx/sites-available/nginx-splitter"
  sudo service nginx restart 
  exit  
else
  conn=$1
fi
config=$(grep limit_conn_zone /etc/nginx/sites-available/nginx-splitter)
if [ -z "$config" ]; then
  sudo sed -i -e '/server {/i\limit_conn_zone $server_name zone=perserver:10m;' /etc/nginx/sites-available/nginx-splitter
fi
config=$(grep limit_conn /etc/nginx/sites-available/nginx-splitter)
if [ -z "$config" ]; then
  sudo sed -i -e '/server_name localhost;/i\\tlimit_conn perserver '"$conn"';' /etc/nginx/sites-available/nginx-splitter
  echo "add limit_conn $conn"
else
  sudo sed -i -e '/limit_conn perserver/d' /etc/nginx/sites-available/nginx-splitter
  sudo sed -i -e '/server_name localhost;/i\\tlimit_conn perserver '"$conn"';' /etc/nginx/sites-available/nginx-splitter
  echo "replace limit_conn $conn"
fi
sudo service nginx restart 