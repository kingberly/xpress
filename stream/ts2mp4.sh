#handle ts2mp4mysql.py
#strindex string targetString
strindex()
{
  x="${1%%$2*}"
  [ "$x" = "$1" ] && echo -1 || echo ${#x}
}
#getPwd targetString
getPyValue()
{
  x=$(sed -n '/'"$1"'/p' ts2mp4mysql.py)
  len=${#x} #36
  pwd1pos=$(strindex $x "=")  #19
  pwdlen=$(($len-$pwd1pos-1))
  x=$(echo $x | tail -c $pwdlen)
  pwdlen=$(($pwdlen-2))
  x=$(echo $x | head -c $pwdlen )
  echo $x
}
getConfValue()
{
  x=$(sed -n '/'"$1"'/p' ../isat_stream/stream_server_control.conf | awk '{print $2}')
  echo $x
}
#SCRIPT_ROOT=`dirname "$0"`
#CONFIG="$SCRIPT_ROOT/../isat_stream/stream_server_control.conf"

myUser=$(getPyValue 'ISAT_DB_USER="')
myPwd=$(getPyValue 'ISAT_DB_PWD="')
myHost=$(getPyValue 'MYSQL_HOST="')
myConfUser=$(getConfValue 'mysql_user ')
myConfPwd=$(getConfValue 'mysql_passwd ')
myConfHost=$(getConfValue 'mysql_host ')
#echo "$myUser ? $myConfUser"
if [ ! "$myUser" = "$myConfUser" ]; then
  sed -i -e 's/ISAT_DB_USER="'"$myUser"'"/ISAT_DB_USER="'"$myConfUser"'"/' ts2mp4mysql.py
  echo "updated mysql user info @ ts2mp4mysql.py"
fi
if [ ! "$myPwd" = "$myConfPwd" ]; then
  sed -i -e 's/ISAT_DB_PWD="'"$myPwd"'"/ISAT_DB_PWD="'"$myConfPwd"'"/' ts2mp4mysql.py
  echo "updated mysql pwd info @ ts2mp4mysql.py"
fi
if [ ! "$myHost" = "$myConfHost" ]; then
  sed -i -e 's/MYSQL_HOST="'"$myHost"'"/MYSQL_HOST="'"$myConfHost"'"/' ts2mp4mysql.py
  echo "updated mysql host info @ ts2mp4mysql.py"
fi

#end

processFolder="/var/evostreamms/temp"
#processFolder="."
#convert ts to mp4 file and move to storage
#cd /var/evostreamms/temp/
if [ ! -z "$1" ]; then #M04CC-184E94040E27-20160302112433.ts
  fileType=$(echo $1 | tail -c 4)
  if [ "$fileType" = ".ts"  ]; then
      #.ts file only
      if [ ! -f "$processFolder/$1" ]; then
        tsFile="${1}.done"
      else
        tsFile="$1"  
      fi
      camMAC=$(echo $1 | head -c 18)
      startTime=$(echo $1 | tail -c 18 | head -c 14)
      dateFolder=$(echo $startTime | head -c 8)
      #stat --printf 'ctime: %z\n' M04CC-184E94040E27-20160302112433.ts
      endTime=$(stat --printf '%y'  $processFolder/$tsFile | head -c 19)  
      endTime=$(TZ=UTC date -d "$endTime UTC+8" +"%Y%m%d%H%M%S")
    
      fileName=${startTime}_${endTime}".mp4"
  else  #mp4
    if [ ${#1} = "33" ]; then
      #converted mp4, read MAC $3
      if [ -z "$3" ]; then
        echo "please provide MAC to proceed. sh $0 <filename> db <MAC>"
        exit
      else
        if [ ! ${#3} = "18" ]; then
          echo "wrong MAC length"
          exit
        fi
      fi
      camMAC="$3"
      fileName="$1"
      startTime=$(echo $1 | head -c 14)
      dateFolder=$(echo $startTime | head -c 8)
      endTime=$(echo $1 | tail -c 19 | head -c 14)
    else
      camMAC=$(echo $1 | head -c 18)
      startTime=$(echo $1 | head -c 33 | tail -c 14)
      dateFolder=$(echo $startTime | head -c 8)
      endTime=$(echo $1 | tail -c 19 | head -c 14)  #mp4
      fileName=${startTime}_${endTime}".mp4"
    fi
  fi
if [ "$2" = "mp4" ]; then
  if [ "$1" = "$tsFile" ]; then 
  #convert to mp4
  sudo -u evostreamd /usr/local/bin/ffmpeg -loglevel quiet -f mpegts -i $processFolder/$1 -bsf:a aac_adtstoasc -f mp4 -c:a copy -c:v copy $processFolder/$fileName
  #sudo -u evostreamd mv 20160302112433_20160302114549.mp4 /var/evostreamms/media/M04CC-184E94040E27/20160302/
  if [ ! -d "/var/evostreamms/media/$camMAC/$dateFolder/" ]; then
    sudo -u evostreamd mkdir /var/evostreamms/media/$camMAC/$dateFolder/
  fi
  if [ ! -d "/var/evostreamms/media/$camMAC/$dateFolder/" ]; then
    echo "parent folder not existed, manually input after monuted"
    echo ">> sudo -u evostreamd mv $processFolder/$fileName /var/evostreamms/media/$camMAC/$dateFolder/" 
  else #mount point fail
    if [ -f "$processFolder/$fileName" ]; then
    echo "move $fileName to /var/evostreamms/media/$camMAC/$dateFolder/" 
    sudo -u evostreamd mv $processFolder/$fileName /var/evostreamms/media/$camMAC/$dateFolder/
    else
      echo "$processFolder/$1 trans-coding FAIL: $fileName Not Exist!!"
      exit
    fi
  fi
  sudo -u evostreamd mv $processFolder/$1 "${processFolder}/${1}.done"
  echo "please delete $processFolder/$1.done"
  #rm /var/evostreamms/temp/$1 
  else
    echo "filetype mp4: ts file was converted already."
    echo "move $fileName to /var/evostreamms/media/$camMAC/$dateFolder/"
    sudo -u evostreamd mv $processFolder/$fileName /var/evostreamms/media/$camMAC/$dateFolder/
  fi
elif [ "$2" = "db" ]; then
#first ext4 uid #32bit 721d9267735b4bd9b829ba34f5c0f856
stream_uid=$(sudo blkid | grep ext4 | head -n 1  | tail -c 51 |head -c 36 | awk -F"-" '{ print $1$2$3$4$5 }')
mysql1="select count(*) from isat.recording_list where device_uid='$camMAC' and start='$startTime'"
echo "try \"$mysql1\" on python ts2mp4mysql.py <str>"
pOutput=$(python ts2mp4mysql.py "$mysql1")
if [ "$pOutput" = "1" ]; then
  echo "db record exist"
  exit
fi
#insert to database
mysql="INSERT INTO isat.recording_list (device_uid, stream_server_uid, start, end, path) VALUES ('$camMAC', '$stream_uid','$startTime','$endTime','/vod/$camMAC/$dateFolder/$fileName')"
python ts2mp4mysql.py "$mysql"
echo "exec \"$mysql\" on python ts2mp4mysql.py <str>"
python ts2mp4mysql.py "$mysql1"

elif [ "$2" = "move" ]; then
  cp $1 $fileName
  if [ ! -d "/var/evostreamms/media/$camMAC/" ]; then
    sudo -u evostreamd mkdir /var/evostreamms/media/$camMAC/
  fi
  if [ ! -d "/var/evostreamms/media/$camMAC/$dateFolder/" ]; then
    sudo -u evostreamd mkdir /var/evostreamms/media/$camMAC/$dateFolder/
  fi
  echo "copy to /var/evostreamms/media/$camMAC/$dateFolder/$fileName"
  sudo -u evostreamd cp $processFolder/$1 /var/evostreamms/media/$camMAC/$dateFolder/$fileName
  
else
  echo "sh ts2mp4.sh <camera uid M04CC-184E94040xxx_XXX.ts / mp4> <mp4/db/move>" 
fi


fi