myPrintAlert(){
  echo "\\033[31m$1\\033[0m"
}
myPrintInfo(){
  echo "\\033[92m$1\\033[0m"
}
targetDATE='20150420'  #list directry after then this date
targetDATE2='20150420'  #list directry above then this date
camPrefix="T04MC-" #"M04CC-"  "Z01CC-00"
#read parameter 1
if [ ! -z "$1" ]; then
    targetDATE=$1
    #read parameter 2, if empty, set as parameter1
    if [ ! -z "$2" ]; then
        targetDATE2=$2
    else
        targetDATE2=$1
    fi
else
  myPrintAlert "no date folder yyyymmdd input!\nusage:sh listcount.sh <all/mac/20160301> <blank/camera UID/20160304>"
  targetDATE="all"
fi
#echo $targetDATE
#videoFolder='/var/evostreamms/media/' #default stream storage folder
videoFolder='/media/videos/'   #default web storage folder 

if [ "$targetDATE" = "mac" ]; then
#run mac folder date count
#select id, start, end, path from isat.recording_list where device_uid='M04CC-184E94040D39';
  if [ -z "$targetDATE2" ]; then
    echo "no MAC UID, e.g. M04CC-184E94040D64"
    exit
  fi
  DATEDIRS=$(ls -l $videoFolder$targetDATE2 | grep ^d | awk '{print$9}')
  for DDIR in $DATEDIRS
  do
      countDIR=$(ls -l $videoFolder$targetDATE2/$DDIR | grep mp4 | wc -l)
      echo "${targetDATE2} ${DDIR}\t${countDIR}"
  done

else #run all and date

  DIRS=$(ls -l $videoFolder | grep ^d | grep $camPrefix | awk '{print$9}')
  for DIR in $DIRS
  do
    #echo  ${DIR}
    DATEDIRS=$(ls -l $videoFolder$DIR | grep ^d | awk '{print$9}')
    #echo ${DATEDIRS}
    for DDIR in $DATEDIRS
    do
      if [ "$targetDATE" = "all" ]; then
        countDIR=$(ls -lR $videoFolder$DIR | grep mp4 | wc -l)
        echo "${DIR} \t${countDIR}"
        break
      elif [ ${DDIR} -ge ${targetDATE} -a ${DDIR} -le ${targetDATE2} ]; then
        countDIR=$(ls -l $videoFolder$DIR/$DDIR | grep mp4 | wc -l)
        echo "${DIR} ${DDIR}\t${countDIR}"
      fi
    done
  
  done

fi

#sh listcount.sh | sort -nr -k 2
#myPrintInfo "ls -l ${videoFolder}Z01CC-001BFE05xxxx/$targetDATE2"
#myPrintInfo "Total number at ${targetDATE2} is"
#ls -l ${videoFolder}*/$targetDATE2 | grep ^- | wc -l 