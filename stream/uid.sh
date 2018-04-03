#!update again on 2017/Jul/10
if [ -z "$1" ]; then
  echo "required userid parameter!"
  exit
fi
#strindex "$str" "$findme"
#${string:position} and ${string:position:length}
#echo ${aaa#*$tt}  #Stripping the shortest match from front
#echo ${aaa%$tt*}  #Stripping the shortest match from back
#echo ${aaa%%$tt*}  #Stripping the longest match from back
#strindex() 
#{
#  x="${1%%$2*}"
#  [ "$x" = "$1" ] && echo -1 || echo ${#x}
#}
evostreamduserid=$1
#user must all under same userid 109 for evostreamd cat /etc/passwd
config_info=$(grep evostreamd:x /etc/passwd) #stream installed?
if [ -z "$config_info" ]; then #-z test if its empty string
  echo "evostreamd user id not existed!!"
  exit
else
	echo "evostreamd userid currently=$config_info"
fi
config_info=$(grep evostreamd:x:$evostreamduserid /etc/passwd)
if [ -z "$config_info" ]; then #-z test if its empty string
  echo "evostreamd user id is not $evostreamduserid"
  config_info=$(grep x:$evostreamduserid /etc/passwd)
#update evostremd
  if [ -z "$config_info" ]; then #-z test if its empty string
    /etc/init.d/stream_server_control stop
    service nginx stop
    config_info1=$(ps -ef | awk '/[s]tream_server/{print $2}')
    if [ ! -z "$config_info1" ]; then # ! -z if it is not empty
      echo "kill evostreamd pid"
      kill -9 $config_info1
    fi
    sleep 10
    sudo usermod -u $evostreamduserid evostreamd
    echo "directly update evostreamd user id to $evostreamduserid"  
  else    #move user id
		#user107line=$(sed -n '/x:'$evostreamduserid'/p' /etc/passwd)
targetuser="x:${evostreamduserid}"
user107line=$(sed -n '/'$targetuser'/p' /etc/passwd)
user107=$(echo ${user107line%%$targetuser*})
user107len=$(echo ${#user107})
user107len=$(($user107len-1))
user107=$(echo $user107 | head -c $user107len)
    #add first char []
    user107head=$(echo $user107 | head -c 1)
    user107head=$(echo "[${user107head}]")
    user107q=$(echo $user107 | tail -c $user107len)
    user107q=$(echo $user107head$user107q)
set -x  #ensure $2 is not varible
    config_info1=$(ps -ef | awk '/'$user107q'/{print $2}')
set +x
    if [ ! -z "$config_info1" ]; then # ! -z if it is not empty
      echo "kill other pid"
      kill -9 $config_info1
    fi
   usermod -u 1001 $user107
   echo "update other user id to 1001"
    config_info1=$(ps -ef | awk '/[s]tream_server/{print $2}')
    if [ ! -z "$config_info1" ]; then # ! -z if it is not empty
      echo "kill evostreamd pid"
      kill -9 $config_info1
    fi
   
   echo "update evostreamd user id to $evostreamduserid"
   usermod -u $evostreamduserid evostreamd
  fi
fi
config_info=$(grep evostreamd:x:$evostreamduserid /etc/passwd)
if [ -z "$config_info" ]; then #-z test if its empty string
  echo "evostreamd user id update fail"
  exit
else
  echo "evostreamd user id is $evostreamduserid" 
fi
