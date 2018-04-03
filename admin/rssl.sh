 SSLFOLDER="/etc/apache2/ssl"
 CONFIGFILE="/etc/apache2/sites-available/000-default.conf"
 REPL_OK=0
if [ -z "$1" -o -z "$2" -o -z "$3" ]; then 
  echo "replace SSL by: sh $0 <server.pem> <ca.pem> <server.key>"
  else
  SCRIPT_ROOT=`dirname "$0"`
  if [ -d "$SSLFOLDER" ]; then
  if [ -f "$SCRIPT_ROOT/$1" ]; then
  chmod 600 $SCRIPT_ROOT/$1
  chmod 600 $SCRIPT_ROOT/$2
  chmod 600 $SCRIPT_ROOT/$3 
  mv $SCRIPT_ROOT/$1 $SSLFOLDER
  mv $SCRIPT_ROOT/$2 $SSLFOLDER
  mv $SCRIPT_ROOT/$3 $SSLFOLDER
  fi
  if [ -z "$(grep SSLCertificateKeyFile $CONFIGFILE)" ]; then
    echo "No SSL Key @$CONFIGFILE"
  else
    if [ -f "$SSLFOLDER/$3" ]; then
    sed -i -e 's|SSLCertificateKeyFile.*|SSLCertificateKeyFile    '"$SSLFOLDER/$3"'|' $CONFIGFILE
    REPL_OK=1
    else
      echo "$SSLFOLDER/$3 Not Exist to Proceed"
    fi
  fi
  if [ -z "$(grep SSLCertificateFile $CONFIGFILE)" ]; then
    echo "No SSL CRT @$CONFIGFILE"
  else
    if [ -f "$SSLFOLDER/$1" ]; then
    sed -i -e 's|SSLCertificateFile.*|SSLCertificateFile    '"$SSLFOLDER/$1"'|' $CONFIGFILE
    REPL_OK=1
    else
      echo "$SSLFOLDER/$1 Not Exist to Proceed"
    fi    
  fi
  if [ -z "$(grep SSLCertificateChainFile $CONFIGFILE)" ]; then
    echo "No SSL CA @$CONFIGFILE"
  else
    if [ -f "$SSLFOLDER/$2" ]; then
    sed -i -e 's|SSLCertificateChainFile.*|SSLCertificateChainFile    '"$SSLFOLDER/$2"'|' $CONFIGFILE
    REPL_OK=1
    else
      echo "$SSLFOLDER/$2 Not Exist to Proceed"
    fi
  fi
  if [ $REPL_OK -eq 1 ]; then
    echo "restart web service"
    sudo service apache2 restart
  fi
  else
    echo "$SSLFOLDER Not Exist to proceed"
  fi
fi
 
#old code 
<<'COMMENT'
findMe="/etc/apache2"
findLine=$(sed -n '/SSLCertificateFile/p' /etc/apache2/sites-available/000-default.conf)
findLineLen=${#findLine}
findLinePos=$(awk -v a="$findLine" -v b="$findMe" 'BEGIN{print index(a,b)}')
findLinePos=$(($findLineLen - $findLinePos -17+2))
oripem=$(sed -n '/SSLCertificateFile/p' /etc/apache2/sites-available/000-default.conf | tail -c $findLinePos)
if [ ! "${mypem}" = "${oripem}" ]; then
    sed -i -e 's|'"$oripem"'|'"$mypem"'|' /etc/apache2/sites-available/000-default.conf
    echo "replace pem file in 000-default.conf"
fi
findLine=$(sed -n '/SSLCertificateKeyFile/p' /etc/apache2/sites-available/000-default.conf)
findLineLen=${#findLine}
findLinePos=$(awk -v a="$findLine" -v b="$findMe" 'BEGIN{print index(a,b)}')
findLinePos=$(($findLineLen - $findLinePos -17+2))
oripem=$(sed -n '/SSLCertificateKeyFile/p' /etc/apache2/sites-available/000-default.conf | tail -c $findLinePos)
if [ ! "${mykey}" = "${oripem}" ]; then
    sed -i -e 's|'"$oripem"'|'"$mykey"'|' /etc/apache2/sites-available/000-default.conf
    echo "replace key file in 000-default.conf"
fi
findLine=$(sed -n '/SSLCertificateChainFile/p' /etc/apache2/sites-available/000-default.conf)
findLineLen=${#findLine}
findLinePos=$(awk -v a="$findLine" -v b="$findMe" 'BEGIN{print index(a,b)}')
findLinePos=$(($findLineLen - $findLinePos -17+2))
oripem=$(sed -n '/SSLCertificateChainFile/p' /etc/apache2/sites-available/000-default.conf | tail -c $findLinePos)
if [ ! "${mycapem}" = "${oripem}" ]; then
    sed -i -e 's|'"$oripem"'|'"$mycapem"'|' /etc/apache2/sites-available/000-default.conf
    echo "replace ca file in 000-default.conf"
fi
COMMENT