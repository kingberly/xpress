myPrintAlert(){
  echo "\\033[31m$1\\033[0m"
}
myPrintInfo(){
  echo "\\033[92m$1\\033[0m"
}
targetDATE='20150420'  #list directry after then this date
targetDATE2='20150420'  #list directry above then this date
#read parameter 2, if empty, set as parameter1
if [ ! -z "$2" ]; then
    targetDATE2=$2
elif [ ! -z "$1" ]; then
    targetDATE2=$1
fi
#read parameter 1
if [ ! -z "$1" ]; then
    targetDATE=$1
else
  myPrintAlert "no date folder yyyymmdd input!"
  exit
fi
#echo $targetDATE
#videoFolder='/var/evostreamms/media/' #default stream storage folder
videoFolder='/media/videos/'   #default web storage folder 


DIRS=$(ls -l $videoFolder | grep ^d | grep 0000000000000000 | awk '{print$9}')
#echo ${#DIRS[@]}
#echo ${#DIRS[*]}
for DIR in $DIRS
do
  #echo  ${DIR}
  DATEDIRS=$(ls -l $videoFolder$DIR | grep ^d | awk '{print$9}')
  #echo ${DATEDIRS}
  for DDIR in $DATEDIRS
  do
    if [ ${DDIR} -ge ${targetDATE} -a ${DDIR} -le ${targetDATE2} ]; then
      countDIR=$(ls -l $videoFolder$DIR/$DDIR | grep ^- | wc -l)
      echo "${DIR} ${DDIR}\t${countDIR}"
    fi
  done
done
#sh listcount.sh | sort -nr -k 2
myPrintInfo "ls -l ${videoFolder}Z0000000000000000xx/$targetDATE2"
myPrintInfo "Total number at ${targetDATE2} is"
ls -l ${videoFolder}0000000000000000*/$targetDATE2 | grep ^- | wc -l 