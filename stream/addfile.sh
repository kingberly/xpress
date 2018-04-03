#usage addfile.sh <MACUID> <FILENAME>
if [ -z "$1" -o -z "$2" ]; then
  echo "usage: sudo sh $0 <UID18> <MP4 FILENAME>/all\n"
  exit
fi
#MACUID="Z01CC-001BFE054DB1"
#FILENAME="20170930030001_20170930040001.mp4"

add1file (){
FILENAME=$1
MACUID=$2
filelen=${#FILENAME}
if [ ! $filelen -eq 33 ]; then
  echo "must be 33 digit mp4 file"
  return
fi
FILE=$(echo $FILENAME | head -c 29)
startTime=$(echo $FILE | head -c 14)
DATEFOLDER=$(echo $startTime | head -c 8)
endTime=$(echo $FILE | tail -c 15) 
#sudo mkdir /var/evostreamms/media/Z01CC-001BFE054DB1/
#sudo mkdir /var/evostreamms/media/Z01CC-001BFE054DB1/20170930
#sudo cp 20170930030001_20170930040001.mp4 /var/evostreamms/media/Z01CC-001BFE054DB1/20170930
#sudo chown -R evostreamd /var/evostreamms/media/Z01CC-001BFE054DB1/
#INSERT INTO isat.recording_list (device_uid, stream_server_uid, start, end, path) VALUES ('Z01CC-001BFE054DB1', '94501fe50e9b4177ac5e81f076b88b1c','20170930030001','20170930040001','/vod/Z01CC-001BFE054DB1/20170930/20170930030001_20170930040001.mp4')
if [ ! -d "/var/evostreamms/media/$MACUID/" ]; then
echo "confirm if $MACUID license was uploaded to system before import!"
sudo mkdir /var/evostreamms/media/$MACUID/
fi
if [ ! -d "/var/evostreamms/media/$MACUID/$DATEFOLDER" ]; then
sudo mkdir /var/evostreamms/media/$MACUID/$DATEFOLDER
fi
if [ ! -f "/var/evostreamms/media/$MACUID/$DATEFOLDER/$FILENAME" ]; then
sudo mv $FILENAME /var/evostreamms/media/$MACUID/$DATEFOLDER/
echo "move $FILENAME to /$MACUID/$DATEFOLDER/"
else
  echo "$FILENAME exists @ /var/evostreamms/media/$MACUID/$DATEFOLDER/!!"
  return
fi
sudo chown -R evostreamd /var/evostreamms/media/$MACUID/$DATEFOLDER/

#stream_uid  #32bit 721d9267735b4bd9b829ba34f5c0f856
#first ext4 uid
stream_uid=$(sudo blkid | grep ext4 | head -n 1  | tail -c 51 |head -c 36 | awk -F"-" '{ print $1$2$3$4$5 }')
#stream_uid=$(echo $stream_uid | sed -e 's|-||g')

mysql="INSERT INTO isat.recording_list (device_uid, stream_server_uid, start, end, path) VALUES ('$MACUID', '$stream_uid','$startTime','$endTime','/vod/$MACUID/$DATEFOLDER/$FILENAME')"
python ts2mp4mysql.py "$mysql"
echo "update $FILENAME to database @$MACUID/$DATEFOLDER"
}

MACUID="$1"
#oLang=$LANG
#LANG=$oLang
#LANG=C
maclen=${#MACUID}
if [ ! $maclen -eq 18 ]; then
  echo "1st param MAC UID lengh must be 18 digit"
  exit
fi
if [ "$2" = "all" ]; then
  LIST=$(ls -l *.mp4 | awk '{print$9}')
  for FILENAME in $LIST
    do
      add1file $FILENAME $MACUID
    done
else
  FILENAME="$2"
  add1file
fi
 