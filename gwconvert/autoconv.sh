#sed -i -e "\$a0 1     * * *   root  find  /var/tmp/  -type f -mtime +14 -exec rm {} +" /etc/crontab
#sed -i -e "\$a0 1     * * *   root  find  /home/ivedasuper/  -type f -name '*.mp4' -mtime +60 -exec rm {} +" /etc/crontab
#sed -i -e "\$a0 10     * * *   root  bash /home/ivedasuper/autoconv.sh scid003" /etc/crontab
#sed -i -e "\$a0 11     * * *   root  bash /home/ivedasuper/autoconv.sh scid004" /etc/crontab
#!/bin/bash
SCID001Arr=(B03CC-0050C2F53D82 B03CC-0050C2F53D85)
SCID001Share="001BFE054DB1"
SCID003Arr=(B03CC-0050C2F53D3A Z01CC-001BFE05051F)
SCID003Share="001BFE054DB3"
SCID004Arr=(Z01CC-001BFE054C9C Z01CC-001BFE054CC0)
SCID004Share="001BFE054DB4"
SCID005Arr=(Z01CC-001BFE054C79)
SCID005Share="001BFE054DB5"
workFolder="/home/ivedasuper"
videoFolder="/var/evostreamms/media"
cd $workFolder

myConv(){
  if [ "${SCID}" = "SCID003" ]; then
    ref=("${SCID003Arr[@]}")
    sharemac=$SCID003Share
  elif [ "${SCID}" = "SCID004" ]; then
    ref=("${SCID004Arr[@]}")
    sharemac=$SCID004Share
  elif [ "${SCID}" = "SCID005" ]; then
    ref=("${SCID005Arr[@]}")
    sharemac=$SCID005Share
  elif [ "${SCID}" = "SCID001" ]; then
    ref=("${SCID001Arr[@]}")
    sharemac=$SCID001Share
  else
    return
  fi
#for i in "${!ref[@]}"; do 
#  printf "%s\t%s\n" "$i" "${ref[$i]}"
#done
  index=1
  #for uid in "${ref[@]}"
  for i in "${!ref[@]}"
    do
      uid=${ref[$i]}
      echo "$uid $tdate ($index) $sharemac"      
      if [ ! -f "$workFolder/${uid}_${tdate}_tl.mp4" -a -d "$videoFolder/$uid/$tdate" ]; then 
        sh ffmpeg-conv.sh $uid $tdate
      fi
      if [ -f "$workFolder/${uid}_${tdate}_tl.mp4" ]; then
        #check duplicate, using index
        sh ffmpeg-conv.sh upload "${uid}_${tdate}_tl.mp4" "${tdate}0${index}00" $sharemac
      fi
      if [ -f "$workFolder/${uid}_${tdate}.mp4" ]; then
        #sh ffmpeg-conv.sh upload "${uid}_${tdate}.mp4" "${tdate}0${index}05" $sharemac
        mv "${uid}_${tdate}.mp4" /var/tmp/
      fi
      (( index++ ))
    done 
}

if [ ! -z "$1" ]; then
  SCID=`echo $1 | tr [:lower:] [:upper:]`
  tdate=$(echo `date --date yesterday '+%Y%m%d'`)
  if [ ! -z "$2" ]; then
    tdate=$2
  fi
  #SCID, Array, tdate exist
  myConv
else
  echo "usage: bash autoconv.sh <SCID001/SCID003/SCID004/SCID005> <empty/8 digit date>" 
fi

#for i in "${!SCID001Arr[@]}"; do 
#  printf "%s\t%s\n" "$i" "${SCID001Arr[$i]}"
#done
