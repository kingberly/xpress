pVer="3861" #3861= v3.2.3
echo "patch web from 3795 to $pVer"
sudo cp -avr SAT-CLOUDNVR/* /var/www/SAT-CLOUDNVR/
configVer=$(grep 'SVN_REVISION' /var/www/SAT-CLOUDNVR/include/global.php | tail -c 8 | head -c 4)
# define("SVN_REVISION", ".3861"); 
sed -i -e 's|define(\x22SVN_REVISION\x22, \x22.'"$configVer"'\x22);|define(\x22SVN_REVISION\x22, \x22.'"$pVer"'\x22);|' /var/www/SAT-CLOUDNVR/include/global.php

config=$(grep "'FILE_SERVER_ENABLED', false" /var/www/SAT-CLOUDNVR/include/global.php)
if [ -z "$config" ]; then
  echo "disable FILE_SERVER_ENABLED"
  configVal=$(grep "'FILE_SERVER_ENABLED', " /var/www/SAT-CLOUDNVR/include/global.php | tail -c 8 | head -c 5)#true?
  sed -i -e 's|define(\x27FILE_SERVER_ENABLED\x27, '"$configVal"');|define(\x27FILE_SERVER_ENABLED\x27, false);|' /var/www/SAT-CLOUDNVR/include/global.php 
fi
