myPrintAlert(){
  echo "\\033[31m$1\\033[0m"
}
myPrintInfo(){
  echo "\\033[92m$1\\033[0m"
}
targetDATE='20150420'  #list directry after then this date
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
du -h ${videoFolder}*/${targetDATE} | sort -nr -k 1 
#sh listsize.sh | sort -nr -k 1
myPrintInfo "du -h <folder>/<MAC>/<DATE> | sort -nr -k 1" 