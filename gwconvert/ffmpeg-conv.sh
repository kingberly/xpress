workFolder="/home/ivedasuper"
ffmpegPath="./ffmpeg-3.0.1"
tl30x="0.04"
tl60x="0.02"
tlx=$tl60x
cd $workFolder

uploadVideo() {
if [ -z "$1" -o -z "$2" -o -z "$3" ]; then
echo "upload Video: sudo -u evostreamd php gwconvert/custom-event/custom-event.php -v tl.mp4 -t <timestamp14> -m <mac12>\n"
fi 
sudo -u evostreamd php gwconvert/custom-event/custom-event.php -v $1 -t $2 -m $3	
}

if [ ! -f "$ffmpegPath" ]; then
  echo "time-lapse ffmpeg file is not existed!.\n"
  echo "wget ftp://iveda:2wsxCFT6@ftp.tdi-megasys.com/TW/Xpress41/ffmpeg-conv.tar.gz $ffmpegPath."
  exit
else
#  sudo chmod 777 $ffmpegPath
  echo "ffmpeg is ready."
fi

videoFolder='/var/evostreamms/media'
#if [ ! -d "$videoFolder/godwatch/" ]; then
#  sudo -u evostreamd mkdir "$videoFolder/godwatch/"
#  echo "create godwatch folder for time-lapse files"
#fi
if [ ! -z "$1" -a ! -z "$2" ]; then
  if [ "$1" = "upload" ]; then
    if [ ! -z "$4" ]; then
      uploadVideo "$workFolder/$2" "${3}00" $4
      python gwconvert/checkmac.py $4
    else
      echo "upload Video: sudo -u evostreamd php gwconvert/custom-event/custom-event.php -v $workFolder/$1_$2_tl.mp4 -t ${2}hhmmss -m <target mac 12 digits>\n"
    fi 
  elif [ "$1" = "delete" ]; then
      if [ -z "$2" ]; then
        echo "no mac or recording id to proceed!!"
        exit
      fi
      if [ -z "$3" ]; then
        echo "no recording id to proceed!!"
        python gwconvert/checkmac.py $2
        exit
      fi
      python gwconvert/checkmac.py $2
      python gwconvert/checkmac.py "select" $3
      FILEPATH=$(python gwconvert/checkmac.py "getpath" $3)
      if [ ! -z "$FILEPATH" ]; then
        len=${#FILEPATH}
        COUNT=$(expr $len - 6)
        FILEPATH=$(echo $FILEPATH | tail -c $COUNT) #trim head
        COUNT=$(($COUNT -4))
        FILEPATH=$(echo $FILEPATH | head -c $COUNT ) #trim tail
        while true; do
            read -p "Delete recording $3 ($FILEPATH) [y/n]?" yn
            case $yn in
                [Yy]* ) sudo rm $videoFolder/$FILEPATH;break;; #echo $FILEPATH
                [Nn]* ) exit;;
                * ) echo "Please answer y or n.";;
            esac
        done
        python gwconvert/checkmac.py "delete" $3
        len=${#2}
        if [ "$len" = "12" ]; then
          ls -al $videoFolder/???CC-${2}
        else
          ls -al $videoFolder/$2
        fi
      else
        echo "File Not Exist!!"
      fi
  else

  if [ ! -d "$videoFolder/$1/$2" ]; then
      echo "no such camera folder, check video files by ll -h $videoFolder/"
      exit
  fi
  #cmd3 only accept empty, timeframe, or upload timestamp
  cmdlen=${#3}
  cmdlenN=$(expr $cmdlen)
  startDate=$(date)
  concatList=""
  LIST=$(ls -l $videoFolder/$1/$2 | grep mp4 | awk '{print$9}')
  #LIST=$(find $videoFolder/$1/$2 -type f -iname "*.mp4" -printf "%f\n"')
  #filename list
  if [ "$3" = "timeframe" ]; then
  for FILE in $LIST
    do
      if [ ! -z "$FILE" ]; then
        fstartN=$(expr $4 - 8)
        if [ $fstartN -lt 0 ]; then
          echo "Only allow between 8AM to 24"
          exit
        fi
        fstart=$(printf "%02d" $fstartN)
        fstartDateN=$(expr $2$fstart)
        fendN=$(expr $5 - 8)
        fend=$(printf "%02d" $fendN)
        fendDateN=$(expr $2$fend)
        fprefix=$(echo $FILE | head -c 10)
        fprefixN=$(expr $fprefix)
        if [ $fprefixN -ge $fstartDateN -a $fprefixN -le $fendDateN ]; then 
 
        $ffmpegPath -i $videoFolder/$1/$2/$FILE -c copy -bsf:v h264_mp4toannexb -f mpegts $workFolder/$FILE.ts
        concatList="$concatList|$FILE.ts"
        fi
      fi
    done
  else
  #whole day convert 
  
  for FILE in $LIST
    do
      if [ ! -z "$FILE" ]; then 
        $ffmpegPath -i $videoFolder/$1/$2/$FILE -c copy -bsf:v h264_mp4toannexb -f mpegts $workFolder/$FILE.ts
        concatList="$concatList|$FILE.ts"
      fi
    done
  fi

    len=${#concatList}
    #COUNT=$(expr $len - 1)
    concatList=$(echo $concatList | tail -c $len)    #trim head
    
    if [ "$3" = "timeframe" ]; then

      $ffmpegPath -i "concat:$concatList" -c copy -bsf:a aac_adtstoasc "$workFolder/$1_$2_$4_$5.mp4"
      echo "Video file output: $1_$2_$4_$5.mp4"
    else #concat and create timelapsed file
      $ffmpegPath -i "concat:$concatList" -c copy -bsf:a aac_adtstoasc "$workFolder/$1_$2.mp4"
      #0.017 = 60x speed, 0.02 = 50x speed
      $ffmpegPath -i $workFolder/$1_$2.mp4 -filter:v "setpts=${tlx}*PTS" -c:v libx264 -an "$workFolder/$1_$2_tl.mp4"
  
      if [ ! -z "$3" ]; then
        sudo -u evostreamd php gwconvert/custom-event/custom-event.php -v "$workFolder/${1}_${2}_tl.mp4" -t "${2}010000" -m $3
        python gwconvert/checkmac.py $3
      else
        echo "upload Video: sudo -u evostreamd php gwconvert/custom-event/custom-event.php -v $workFolder/${1}_${2}_tl.mp4 -t ${2}010000 -m <target mac 12 digits>\n"
      fi
      echo "Video file output: $1_$2.mp4 , ${1}_${2}_tl.mp4"
    fi
    endDate=$(date)

    #echo "empty $workFolder by rm -rf $workFolder/*.ts"
    rm -rf $workFolder/*.ts;
    #while true; do
    #    read -p "Do you want to empty ts file @$workFolder [y/n]?" yn
    #    case $yn in
    #        [Yy]* ) rm -rf $workFolder/*.ts; break;;
    #        [Nn]* ) break;;
    #        * ) echo "Please answer y or n.";;
    #    esac
    #done
    #rm -rf $workFolder/${1}_${2}.mp4
    echo "Video Convert takes: $startDate -- $endDate"
    #echo "please change file ownership by sudo chown evostreamd. $videoFolder/<UID>/$2/"
  fi
else
echo "sh ffmpeg-conv.sh <target camera uid Z01CC-001BFE04936D> <target date folder 20160316>"
echo "sh ffmpeg-conv.sh <target camera uid Z01CC-001BFE04936D> <target date folder 20160316> timeframe <start hh> <end hh>"
echo "sh ffmpeg-conv.sh upload <time-lapsed filename> <target upload date YYYYMMDDhhmm> <shared camera mac>"
echo "sh ffmpeg-conv.sh <target camera uid Z01CC-001BFE04936D> <target date folder 20160316> <shared camera mac>\n"
echo "check godwatch shared camera mac below:"
python gwconvert/checkmac.py
echo "delete sharevideo: sh ffmpeg-conv.sh delete <uid18 or mac12> <recording id>"
fi
