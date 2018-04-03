#backup everyday 10AM 1MAC=2G=23mins /etc/crontab
TARGET_FOLDER="."
ACCOUNT=""
PASSWD=""
URL="https://xpress.megasys.com.tw:8080"
API_VLIST="/html/api/get_videolist.php?id=root&pwd=1qazxdr5&"
API_MLIST="/html/api/mgmt_enduser.php?id=root&pwd=1qazxdr5&command=get_camera_list&"
#WURL=$(echo $URL  | head -c $(expr ${#URL} - 5) )
API_TOKEN="/html/api/mgmt_enduser.php?id=root&pwd=1qazxdr5&command=get_token&"

if [ -z "$1" ]; then
  echo "usage: sh $0 <MAC 12> <date 8>/default yesterday"
  exit
fi

getMACList(){
MYNAME="$1"
MYPWD="$2"
#curl -sS "${URL}${API_MLIST}name=$ACCOUNT&password=$PASSWD" >> mlist
wget -O mlist "${URL}${API_MLIST}name=$ACCOUNT&password=$PASSWD" 
}

MYTOKEN="wget -qO- `${URL}${API_TOKEN}name=$ACCOUNT&password=$PASSWD`"

downloadList(){
MAC="$1"
TDATE="$2"
#curl -sS "${URL}${API}mac=$MAC&date=$TDATE" >> vlist
wget -O vlist "${URL}${API_VLIST}mac=$MAC&date=$TDATE"
if [ -f "vlist" ]; then
  #check if backup before
  if [ -d "$TARGET_FOLDER/$MAC/$TDATE" ]; then
  if [ "$(ls $TARGET_FOLDER/$MAC/$TDATE | wc -l)" = "$(grep . vlist | wc -l)" ]; then
    echo "$MAC video files was bakcup COMPLETED @ $TARGET_FOLDER/$MAC/$TDATE"
    #rm vlist
    exit
  else  #compare missing file
    echo "$MAC $TDATE bakcup $(ls $TARGET_FOLDER/$MAC/$TDATE | wc -l) video files. resume missing one:"
    resumeList $TARGET_FOLDER/$MAC/$TDATE
    echo "Resume $MAC $TDATE bakcup COMPLETED."
    exit
  fi
  else
  resumeList
  fi
fi
}

resumeList(){
  LIST=$(cat vlist | grep http)
  for oneline in $LIST
  do
    if [ ! -z "$1" ]; then
      wget -nc -P $1 $oneline
    else
      wget -nc $oneline
    fi
    onelinelen=${#oneline}
    completeline="OK-$(echo $oneline | tail -c $(expr $onelinelen - 2))"
    sed -i -e 's|'"$oneline"'|'"$completeline"'|' vlist
    echo "$oneline was downloaded."
  done
}

if [ -z "$2" ]; then
  DL_DATE=$(date -d "yesterday" +%Y%m%d)
else
  DL_DATE="$2"
fi
#previous file exist
if [ -f "vlist" ]; then
#all file downloaded?
if [ "$(cat vlist | grep OK- | wc -l)" = "$(grep . vlist | wc -l)" ]; then
  rm vlist
  downloadList $1 $DL_DATE
else
  if [ -d "$TARGET_FOLDER/$1/$DL_DATE" ]; then
    #file was download completed before
    downloadList $1 $DL_DATE
  else
  echo "resume downloading from previous vlist"
  resumeList
  fi
fi
else  #never downloaded
downloadList $1 $DL_DATE
fi

if [ ! -d "$TARGET_FOLDER" ]; then
  mkdir $TARGET_FOLDER
fi
if [ ! -d "$TARGET_FOLDER/$1" ]; then
  mkdir $TARGET_FOLDER/$1
fi
if [ ! -d "$TARGET_FOLDER/$1/$DL_DATE" ]; then
  mkdir $TARGET_FOLDER/$1/$DL_DATE
fi
mv *.mp4 $TARGET_FOLDER/$1/$DL_DATE
echo "$1 video files bakcup @ $TARGET_FOLDER/$1/$DL_DATE"
