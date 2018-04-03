#echo $targetDATE
videoFolder='/var/evostreamms/media/' #default stream storage folder
#ls -ltr /media/videos/M04CC-184E9404036D | grep '^d' | tail -n 1 | awk '{print $9}'
#videoFolder='/media/videos/'   #default web storage folder 
LIST=$(ls -l $videoFolder/ | grep ^d | awk '{print $9}')
for FILE in $LIST
    do
      if [ ! -z "$FILE" ]; then
         LASTFOLDER=$(ls -ltr $videoFolder$FILE | grep '^d' | tail -n 1 | awk '{print $9}')
         echo "$FILE\t$LASTFOLDER"   
      fi
    done
