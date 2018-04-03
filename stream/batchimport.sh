if [ -z "$1" -o -z "$2" ]; then
  echo "usage: sh $0 <MAC 12> <date 8> <Target UID 18>"
  exit
fi

downloadList(){
MAC="$1"
TDATE="$2"
URL="https://xpress.megasys.com.tw:8080"
API="/html/api/get_videolist.php?id=root&pwd=1qazxdr5&"
wget -O vlist "${URL}${API}mac=$MAC&date=$TDATE"

while read -r line
do
    oneline="$line"
    #echo "read from file - $oneline"
    if [ ! -z "$oneline" ]; then
    if [ -z "$(echo $oneline | grep OK-)" ]; then
    wget -nc $oneline
    onelinelen=${#oneline}
    completeline="OK-$(echo $oneline | tail -c $(expr $onelinelen - 2))"
    sed -i -e 's|'"$oneline"'|'"$completeline"'|' vlist
    else
    echo "$oneline was downloaded"
    fi
    fi
done < vlist
}

resumeList(){
  LIST=$(cat vlist | grep http)
  for oneline in $LIST
  do
    wget -nc $oneline
    onelinelen=${#oneline}
    completeline="OK-$(echo $oneline | tail -c $(expr $onelinelen - 2))"
    sed -i -e 's|'"$oneline"'|'"$completeline"'|' vlist
    echo "$oneline was downloaded"
  done
}
#previous file exist
if [ -f "vlist" ]; then
#all file downloaded?
if [ "$(cat vlist | grep OK- | wc -l)" = "$(grep . vlist | wc -l)" ]; then
  rm vlist
  downloadList $1 $2
else
  echo "resume downloading from previous vlist"
  resumeList
fi
else  #never downloaded
downloadList $1 $2
fi

if [ ! -z "$3" ]; then
  sudo sh addfile.sh "$3" all
else
 echo "manually upload file: sudo sh addfile.sh <UID> all"
fi
