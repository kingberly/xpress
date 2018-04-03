#chown -R evostreamd /var/evostreamms/temp/*.ts
#M04CC-184E94040xxx
#sh ts2mp4batch.sh <camera uid M04CC-184E94040xxx>
videoFolder='/var/evostreamms/temp'
if [ ! -z "$1" ]; then
  if [ "$1" = "camera" ]; then
    LIST=$(ls -l $videoFolder/*.ts | grep CC- | awk '{print$9}')
  elif [ "$1" = "ivedamobile" ]; then
    LIST=$(ls -l $videoFolder/*.ts | grep MC- | awk '{print$9}')
  elif [ "$1" = "oem" ]; then
    echo -n "OEM ID [Xnn]= "
    read oem
    oemlen=${#oem}
    oem=`echo $oem | tr [:lower:] [:upper:]`  ##bash oem=${oem^^}
    if [ ! "$oemlen" -eq 3 ]; then
      echo "oem id error"
      exit
    fi 
    LIST=$(ls -l $videoFolder/*.ts | grep $oemCC- | awk '{print$9}')
  elif [ "$1" = "mp4" ]; then
    LIST=$(ls -l $videoFolder/*.mp4 | awk '{print$9}')
    for FILE in $LIST
    do
    if [ ! ${#2} = "18" ]; then
    echo "$FILE (date=$(date -r $FILE +%Y%m%d)), \nplease get uploaded UID(18) to proceed manually"
    else
    FILE=$(echo $FILE | tail -c 34)
    sh ts2mp4.sh $FILE mp4 $2
    sh ts2mp4.sh $FILE db $2
    fi
    done
    exit
  else
    LIST=$(ls -l $videoFolder/$1*.ts | grep CC- | awk '{print$9}')
  fi
 
for FILE in $LIST
  do
    if [ ! -z "$FILE" ]; then
      FILE=$(echo $FILE | tail -c 37)
      echo "convert $FILE:"
      if [ ! -z "$2" ]; then
        sh ts2mp4.sh $FILE mp4
        sh ts2mp4.sh $FILE db
      else
          while true; do
              read -p "Do you want to convert [y/n]?" yn
              case $yn in
                  [Yy]* ) sh ts2mp4.sh $FILE mp4;sh ts2mp4.sh $FILE db; break;;
                  [Nn]* ) exit;;
                  * ) echo "Please answer y or n.";;
              esac
          done
      fi   
      echo "inserted db for $FILE."
    fi
  done
  if [ ! -z "$2" ]; then
    if [ "$1" = "camera" ]; then
      sudo rm $videoFolder/???CC*.ts.done
    elif [ "$1" = "ivedamobile" ]; then
      sudo rm $videoFolder/???MC*.ts.done
    elif [ "$1" = "oem" ]; then
      sudo rm $videoFolder/$oemCC*.ts.done
    else
      sudo rm $videoFolder/$1*.ts.done
    fi
  else
    ls -l $videoFolder/*.ts.done
    echo "sudo rm $videoFolder/*.ts.done"
  fi
else
   echo "move files to /var/evostreamms/temp/"
   echo "sh ts2mp4batch.sh <camera uid M04CC-184E94040xxx> <auto>"
   echo "sh ts2mp4batch.sh <camera/ivedamobile/oem> <auto>"
   echo "sh ts2mp4batch.sh mp4 <MAC18>"
fi