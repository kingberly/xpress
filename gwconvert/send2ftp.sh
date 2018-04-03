#find  /var/evostreamms/media/???CC-2C625A10579C/20161108  -type f -newermt "2016-11-08 14:20" ! -newermt "2016-11-08 17:30"
# | xargs -d "\n" tar -czvf 2C625A10579C.tar.gz
#(web)tar czvf /var/www/SAT-CLOUDNVR/download/VI0200089431.tar.gz /media/videos/X02CC-VI0200089431/20161112/
#sed -i -e "\$a0 1     * * *   root  find  /var/www/SAT-CLOUDNVR/download/ -type f -name \x22*.tar.gz\x22 -mtime +3 -exec rm {} +" /etc/crontab 
ftpFolder="/home/iveda/"
ftpURL="ftp://iveda:2wsxCFT6@125.227.139.173/"
SCID001Arr=(B03CC-0050C2F53D82 B03CC-0050C2F53D85)

config_info=$(grep '/home/iveda/' /etc/crontab)
if [ -z "$config_info" ]; then #-z test if its empty string 
  sed -i -e "\$a0 1     * * *   root  find  /home/iveda/ -type f -name \x22*.mp4\x22 -mtime +3 -exec rm {} +" /etc/crontab
  sed -i -e "\$a0 1     * * *   root  find  /home/iveda/ -type f -name \x22*.tar.gz\x22 -mtime +3 -exec rm {} +" /etc/crontab
fi
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
 