 SSLFOLDER="/etc/lighttpd/ssl"
 CONFIGFILE="/etc/lighttpd/conf-enabled/10-ssl.conf"
 REPL_OK=0
if [ -z "$1" -o -z "$2" ]; then 
  echo "replace SSL by: sh $0 <server.pem> <ca.pem>"
  else
  SCRIPT_ROOT=`dirname "$0"`
  if [ -d "$SSLFOLDER" ]; then
  if [ -f "$SCRIPT_ROOT/$1" ]; then
  chmod 600 $SCRIPT_ROOT/$1
  chmod 600 $SCRIPT_ROOT/$2
  mv $SCRIPT_ROOT/$1 $SSLFOLDER
  mv $SCRIPT_ROOT/$2 $SSLFOLDER
  fi
  if [ -z "$(grep ssl.pemfile $CONFIGFILE)" ]; then
    echo "No SSL CRT @$CONFIGFILE"
  else
    if [ -f "$SSLFOLDER/$1" ]; then
    sed -i -e 's|ssl.pemfile.*|ssl.pemfile = "'"$SSLFOLDER/$1"'"|' $CONFIGFILE
    REPL_OK=1
    else
      echo "$SSLFOLDER/$1 Not Exist to Proceed"
    fi    
  fi
  if [ -z "$(grep ssl.ca-file $CONFIGFILE)" ]; then
    echo "No SSL CA @$CONFIGFILE"
  else
    if [ -f "$SSLFOLDER/$2" ]; then
    sed -i -e 's|ssl.ca-file.*|ssl.ca-file = "'"$SSLFOLDER/$2"'"|' $CONFIGFILE
    REPL_OK=1
    else
      echo "$SSLFOLDER/$2 Not Exist to Proceed"
    fi
  fi
  if [ $REPL_OK -eq 1 ]; then
    echo "restart web service"
    /etc/init.d/lighttpd restart
  fi
  else
    echo "$SSLFOLDER Not Exist to proceed"
  fi
fi
 
#old code 
<<'COMMENT'
findMe="/etc/lighttpd"
findLine=$(sed -n '/ssl.pemfile/p' /etc/lighttpd/conf-enabled/10-ssl.conf)
findLineLen=${#findLine}
findLinePos=$(awk -v a="$findLine" -v b="$findMe" 'BEGIN{print index(a,b)}')
findLinePos=$(($findLineLen - $findLinePos -19+2 -1))      # end with "
findLineLen=$(($findLineLen - 1)) # end with "
oripem=$(sed -n '/ssl.pemfile/p' /etc/lighttpd/conf-enabled/10-ssl.conf | head -c $findLineLen |tail -c $findLinePos)
if [ ! "${mypem}" = "${oripem}" ]; then
    sed -i -e 's|'"$oripem"'|'"$mypem"'|' /etc/lighttpd/conf-enabled/10-ssl.conf
    echo "replace pem file in conf-enabled/10-ssl.conf"
fi
findLine=$(sed -n '/ssl.ca-file/p' /etc/lighttpd/conf-enabled/10-ssl.conf)
findLineLen=${#findLine}
findLinePos=$(awk -v a="$findLine" -v b="$findMe" 'BEGIN{print index(a,b)}')
findLinePos=$(($findLineLen - $findLinePos -19+2 -1))
findLineLen=$(($findLineLen - 1))
oripem=$(sed -n '/ssl.ca-file/p' /etc/lighttpd/conf-enabled/10-ssl.conf | head -c $findLineLen |tail -c $findLinePos)
if [ ! "${mycapem}" = "${oripem}" ]; then
    sed -i -e 's|'"$oripem"'|'"$mycapem"'|' /etc/lighttpd/conf-enabled/10-ssl.conf
    echo "replace ca file in conf-enabled/10-ssl.conf"
fi
COMMENT