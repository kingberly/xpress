#Validated on Dec-21,2017,
## list http port of connected camrea 
## sudo sh tunnel/cameraweb.sh m04 query
## sudo sh tunnel/cameraweb.sh m04 NNNNN y
#Writer: JinHo, Chang
SCRIPT_ROOT=`dirname "$0"`
PORTLIST=$(netstat -lat | grep '*:' | awk '{print $4}' | sort -n| awk -F":" '{print $2}')
oem=`echo $1 | tr [:lower:] [:upper:]` 
if [ ! -z "$2" ]; then
  if [ "$oem" = "M04" ]; then
  param="system.datetime.ntpinterval&system.datetime.method&system.datetime.ntpserver&system.datetime.timezone&system.datetime.date"
  uparam="system.datetime.timezone=79&system.datetime.ntpinterval=4&system.datetime.method=2&system.datetime.ntpserver=tw.pool.ntp.org"
  cmdlist="/operator/get_param.cgi?"
  cmdupdate="/operator/set_param.cgi?"
  #wget not working due to digest auth
  ccmd=" --anyauth -u admin:1234"
  #curl --anyauth -u admin:1234 "http://127.0.0.1:45008/operator/get_param.cgi?system.datetime.ntpinterval"
  elif [ "$oem" = "B03" ]; then
  param="get_video.cgi?"
  uparam="set_video.cgi?H264BitRateP1=256"
  #uparam="set_linkage_alarm.cgi?PIRAlarmEnable=false"
  cmdlist="/cgi-bin/"
  cmdupdate="/cgi-bin/"
  ccmd=" --anyauth -u admin:admin@Iveda1688"
  elif [ "$oem" = "Z01" ]; then
  param="group=General.Time.NTP.Server&group=General.Time.SyncSource&group=General.Time.TimeZone&group=General.Time.ServerDate&group=General.Time.ServerTime"
  uparam="General.Time.SyncSource=NTP&General.Time.NTP.ManualServer=tw.pool.ntp.org&General.Time.NTP.Server=tw.pool.ntp.org"
  #param="group=StreamProfile.I1.Video.FPS"
  #uparam="StreamProfile.I1.Video.FPS=30"
  #param="group=StreamProfile.I1.Video.Resolution"
  #uparam="StreamProfile.I1.Video.Resolution=640x360"
  #param="group=StreamProfile.I0.Video.Quality.BitRate&group=StreamProfile.I1.Video.Quality.BitRate"
  #uparam="StreamProfile.I1.Video..Video.Quality.BitRate=256k&StreamProfile.I1.Video.Quality.BitRate=256k"
  cmdlist="/cgi-bin/admin/param?action=list&"
  cmdupdate="/cgi-bin/admin/param?action=update&"
  #reboot
  #param="group=General.Network.eth0"
  #cmdupdate="/cgi-bin/admin/"
  #uparam="reboot"
  #ptz
  #cmdlist="/cgi-bin/operator/ptzconfig?"
  #cmdupdate="/cgi-bin/operator/ptzset?move=left"
  wcmd="  --tries=1 --http-user=admin --http-password=admin@Iveda1688 -qO-"
  ccmd=" --anyauth -u admin:admin@Iveda1688"
  else
    echo "no matching camera vendor"
    exit
  fi
  TPORT=$2
  if [ "$TPORT" = "query" ]; then
      i=1
      while true; do
        PORT=$(echo $PORTLIST | cut -f$i -d" ")
        #echo "$i:$PORT"
        if [ -z "$PORT" ]; then
          break
        fi
        #detect PORT is number, is 5 digit
        if [ "$PORT" -eq "$PORT" ] 2>/dev/null; then
          if [ $(echo -n $PORT | wc -m) -eq 5 ]; then
          MAC=$(python $SCRIPT_ROOT/getMAC.py $PORT)
          MACOEM=$(echo $MAC | head -c 3)
          if [ "$MACOEM" = "$oem" ]; then
            echo "($PORT)$MAC=>" 
            if [ ! -z "$param" ]; then
              #echo "http://127.0.0.1:$PORT$cmdlist$param" 
              curl $ccmd "http://127.0.0.1:$PORT$cmdlist$param"
            fi
          fi
          fi
        fi
        i=$((i+1))
      done
  elif [ "$TPORT" = "set" ]; then
  # $3==> port $4==> cmd
    if [ -z "$3" ]; then
      echo "no port set!!"
    else
      if [ -z "$4" ]; then
        echo "no cmd provide!! ex: \"/operator/set_param.cgi?system.datetime.method=0&system.datetime.date=2018/01/22&system.datetime.time=10:21:26\" "
      else
        curl $ccmd "http://127.0.0.1:$3$4"
      fi
    fi
  elif [ "$TPORT" = "setdate" ]; then    #$0 oem TPORT NNNNN 
    if [ -z "$3" ]; then
      echo "no port set!!"
    else
      if [ "$oem" = "M04" ]; then
      thiscmd="/operator/set_param.cgi?system.datetime.method=0&system.datetime.date=`date '+%Y/%m/%d'`"
      else
        exit
      fi
      curl $ccmd "http://127.0.0.1:$3$thiscmd"
    fi
  elif [ "$TPORT" = "listdate" ]; then    #$0 oem TPORT NNNNN
    if [ -z "$3" ]; then
      echo "no port set!!"
    else
      if [ "$oem" = "M04" ]; then
      thiscmd="/operator/get_param.cgi?system.datetime.method&system.datetime.timezone&system.datetime.date&system.datetime.time"
      else
        exit
      fi
      curl $ccmd "http://127.0.0.1:$3$thiscmd"
    fi
  elif [ "$TPORT" = "list" ]; then
  # $3==> port $4==> cmd
    if [ -z "$3" ]; then
      echo "no port set!!"
    else
      if [ -z "$4" ]; then
        echo "no cmd provide!! ex: \"/operator/get_param.cgi?system.datetime.method&system.datetime.date&system.datetime.time\""
      else
        curl $ccmd "http://127.0.0.1:$3$4"
      fi
    fi
  elif [ "$TPORT" = "auto" ]; then
      i=1
      while true; do
        PORT=$(echo $PORTLIST | cut -f$i -d" ")
        #echo "$i:$PORT"
        if [ -z "$PORT" ]; then
          break
        fi
        #detect PORT is number, is 5 digit
        if [ "$PORT" -eq "$PORT" ] 2>/dev/null; then
          if [ $(echo -n $PORT | wc -m) -eq 5 ]; then
          MAC=$(python $SCRIPT_ROOT/getMAC.py $PORT)
          MACOEM=$(echo $MAC | head -c 3)
          if [ "$MACOEM" = "$oem" ]; then
            echo "$MAC=>" 
            if [ ! -z "$param" ]; then
              #echo "http://127.0.0.1:$PORT$cmdlist$param" 
              curl $ccmd "http://127.0.0.1:$PORT$cmdlist$param"
            fi
            if [ ! -z "$uparam" ]; then
              #echo "http://127.0.0.1:$PORT$cmdupdate$uparam" 
              curl $ccmd "http://127.0.0.1:$PORT$cmdupdate$uparam"
            fi
          fi
          fi
        fi
        i=$((i+1))
      done
  elif [ $(echo -n $TPORT | wc -m) -eq 5 ]; then
    if [ ! -z "$param" ]; then
    echo "http://127.0.0.1:$TPORT$cmdlist$param" 
    #wget $wcmd "http://127.0.0.1:$2$cmdlist$param"
    curl $ccmd "http://127.0.0.1:$2$cmdlist$param"
    fi
    if [ -z "$3" ]; then   #any $3 to auto update
    echo -n "Enter Y to proceed update? "
    read ANS
    else
      ANS="Y"
    fi
    if [ "$ANS" = "y" -o "$ANS" = "Y" ]; then
      echo "http://127.0.0.1:$TPORT$cmdupdate$uparam"
      if [ ! -z "$uparam" ]; then
      #wget $wcmd "http://127.0.0.1:$2$cmdupdate$uparam"
      curl $ccmd "http://127.0.0.1:$TPORT$cmdupdate$uparam"
      fi
    fi  #ANS is Y/y
  else
    echo "parameter2 input error"
  fi  #port=5digit

else #port is empty

  echo "usage: sh $0 <m04/z01/b03> <auto /query / NNNNN y / listdate NNNNN / list NNNNN cmd /set NNNNN cmd>"
  echo "rtsp/http/cmd for each camera"
  if [ ! -z "$1" ]; then
  if [ ! $(echo -n $oem | wc -m) -eq 3 ]; then
    echo "oem id is 3 digit" 
    exit
  fi
  fi
  i=1
  while true; do
    PORT=$(echo $PORTLIST | cut -f$i -d" ")
    #echo "$i:$PORT"
    if [ -z "$PORT" ]; then
      break
    fi
    #detect PORT is number, is 5 digit
    if [ "$PORT" -eq "$PORT" ] 2>/dev/null; then
      if [ $(echo -n $PORT | wc -m) -eq 5 ]; then
      MAC=$(python $SCRIPT_ROOT/getMAC.py $PORT)
      if [ ! -z "$MAC" ]; then
        MACOEM=$(echo $MAC | head -c 3)
        if [ -z "$oem" ]; then
          echo "$PORT=>$MAC"
        elif [ "$MACOEM" = "$oem" ]; then
          echo "$PORT=>$MAC"
        fi
      fi
      fi
    fi
    i=$((i+1))
  done
  #echo $PORTLIST
  #for PORT in $PORTLIST;do python getMAC.py $PORT;done
  echo "get MAC from port by: python getMAC.py <PORT>"
fi 