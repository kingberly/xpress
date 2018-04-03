ffmpegPath="./ffmpeg-3.0.1"
workFolder="./"
#confileExt=".avi"
confileExt=".3gp"
tl="60x" #"30x" "60x" "1x"
tl30x="0.04"
tl60x="0.02"
tl1x="1"


streamFolder="/var/evostreamms/media"
uploadVideo() {
sudo -u evostreamd php gwconvert/custom-event/custom-event.php -v $1 -t $2 -m $3	
}

if [ ! -f "$ffmpegPath" ]; then
  echo "time-lapse ffmpeg file is not existed!.\n"
  echo "wget ftp://iveda:2wsxCFT6@ftp.tdi-megasys.com/TW/Xpress41/ffmpeg-convcamera.tar.gz $ffmpegPath."
  exit
else
#  sudo chmod 777 $ffmpegPath
  echo "ffmpeg is ready."
fi

echo "check video codec by: ffmpeg -v verbose -i FILENAME"


if [ ! -z "$1" -a ! -z "$2" ]; then
  if [ "$1" = "set" ]; then
    if [  -z "$3" ]; then
    echo "sh ffmpeg-convcamera.sh set <avi/3gp/mp4> <30/60/1>"
    exit
    fi
    if [ "$2" = "avi" ]; then
      sed -i -e 's|confileExt=".3gp"|confileExt=".avi"|' ffmpeg-convcamera.sh
    elif [ "$2" = "3gp" ]; then
      echo "no change"
    elif [ "$2" = "mp4" ]; then
      sed -i -e 's|confileExt=".3gp"|confileExt=".mp4"|' ffmpeg-convcamera.sh
    fi
  elif [ "$1" = "upload" ]; then
    if [ ! -z "$4" ]; then
      uploadVideo "$workFolder/$2" "${3}00" $4
      python gwconvert/checkmac.py $4
    else
      echo "upload Video: sudo php gwconvert/custom-event/custom-event.php -v $workFolder/$1_$2_$tl_tl.mp4 -t ${2}hhmmss -m <target mac 12 digits>\n"
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
                [Yy]* ) sudo rm $workFolder/$FILEPATH;break;; #echo $FILEPATH
                [Nn]* ) exit;;
                * ) echo "Please answer y or n.";;
            esac
        done
        python gwconvert/checkmac.py "delete" $3
        len=${#2}
        if [ "$len" = "12" ]; then
          ls -al $streamFolder/???CC-${2}
        else
          ls -al $streamFolder/$2
        fi
      else
        echo "File Not Exist!!"
      fi
        
  else #formal convert
  startDate=$(date)
  if [ ! -d "$workFolder/$1/$2" ]; then
      echo "no such camera folder, check video files by ll -h $workFolder/"
      exit
  fi
 
  LIST=$(ls -l $workFolder/$1/$2 | grep $confileExt | awk '{print$9}')
  concatList=""
  for FILE in $LIST
    do
      if [ ! -z "$FILE" ]; then
        if [ $confileExt = ".avi" ]; then
        #avi concat
        #cat avi1.avi avi2.avi avix.avi > avi_all.avi
        #ffmpeg -i avi_all.avi -acodec copy -vcodec copy avi_all_reindexed.avi
        #avi h264 concat
        $ffmpegPath -i $workFolder/$1/$2/$FILE -c copy -avoid_negative_ts make_zero -fflags +genpts $workFolder/$FILE.ts 
        elif [ $confileExt = ".mp4" -o $confileExt = ".3gp" ]; then
        $ffmpegPath -i $workFolder/$1/$2/$FILE -c copy -bsf:v h264_mp4toannexb -f mpegts $workFolder/$FILE.ts
        fi
        concatList="$concatList|$FILE.ts"
      fi
    done
    len=${#concatList}
    #COUNT=$(expr $len - 1)
    concatList=$(echo $concatList | tail -c $len)
    if [ $confileExt = ".avi" ]; then
#for avi h264 concat
    $ffmpegPath -i "concat:$concatList" -c copy -flags +global_header -fflags +genpts "$workFolder/$1_$2.mp4"
    elif [ $confileExt = ".mp4" -o $confileExt = ".3gp" ]; then
    $ffmpegPath -i "concat:$concatList" -c copy -bsf:a aac_adtstoasc "$workFolder/$1_$2.mp4"
    fi
    if [ $tl = "30x" ]; then
    #0.017 = 60x speed, 0.02 = 50x speed
    $ffmpegPath -i $workFolder/$1_$2.mp4 -filter:v "setpts=$tl30x*PTS" -c:v libx264 -an "$workFolder/${1}_${2}_${tl}_tl.mp4"
    elif [ $tl = "60x" ]; then
    $ffmpegPath -i $workFolder/$1_$2.mp4 -filter:v "setpts=$tl60x*PTS" -c:v libx264 -an "$workFolder/${1}_${2}_${tl}_tl.mp4"
    elif [ $tl = "1x" ]; then
    #filename =$workFolder/$1_$2.mp4 
    mv $workFolder/$1_$2.mp4 "$workFolder/${1}_${2}_${tl}_tl.mp4"
    fi

    if [ ! -z "$3" ]; then
      uploadVideo "$workFolder/${1}_${2}_${tl}_tl.mp4" "${2}090000" $3
      python gwconvert/checkmac.py $3
    else
      echo "upload Video: sudo -u evostreamd php gwconvert/custom-event/custom-event.php -v $workFolder/${1}_${2}_${tl}_tl.mp4 -t ${2}hhmmss -m <target mac 12 digits>\n"
    fi
    endDate=$(date)

    echo "empty $workFolder by rm -rf $workFolder/*.ts"
    rm -rf $workFolder/*.ts;
    echo "Video Convert takes: $startDate -- $endDate"

  fi
else
echo "------edit below parameter-------------------"
echo "<UID> <Date>:converting from ${workFolder}/*${confileExt} to time-lapsed ${tl} mp4"
echo "<Level folder> :converting from ${confileExt} to time-lapsed ${tl} mp4"
echo "delete :delete from ${workFolder}/<UID>/ and cleanup db"
echo "---------------------------------------------"
echo "sh ffmpeg-convcamera.sh <1st Level folder> <2nd Level folder>"
echo "sh ffmpeg-convcamera.sh upload <time-lapsed filename> <target upload date YYYYMMDDhhmm> <shared camera mac>"
echo "delete sharevideo: sh ffmpeg-conv.sh delete <uid 18digit> <recording id>"
echo "check godwatch shared camera mac below:"
python gwconvert/checkmac.py

fi