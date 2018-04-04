config_info=$(sudo /etc/init.d/lighttpd status | grep 'running')
if [ ! -z "$config_info" ]; then #-z test if its empty string
  echo "stop web service before patching jar file"
  sudo /etc/init.d/lighttpd stop
fi
VER="`date '+%Y%m%d%H%M%S'`"
mv /var/www/SAT-CLOUDNVR/jar/SAT_P2P_iveda.jar /var/www/SAT-CLOUDNVR/jar/SAT_P2P_iveda.$VER
mv /var/www/SAT-CLOUDNVR/jar/ref_library_iveda.jar /var/www/SAT-CLOUDNVR/jar/ref_library_iveda.$VER
if [ -d "jar/" ]; then
cp jar/*.jar /var/www/SAT-CLOUDNVR/jar/
else
sudo mv *.jar /var/www/SAT-CLOUDNVR/jar/
fi
if [ ! -f "/var/www/SAT-CLOUDNVR/jar/SAT_P2P_iveda.jar" ]; then
  mv  /var/www/SAT-CLOUDNVR/jar/SAT_P2P_iveda.$VER  /var/www/SAT-CLOUDNVR/jar/SAT_P2P_iveda.jar
  mv  /var/www/SAT-CLOUDNVR/jar/ref_library_iveda.$VER  /var/www/SAT-CLOUDNVR/jar/ref_library_iveda.jar
  echo "file not exist, Recovery"
fi 
echo "replaced new iveda jar files\n"
sudo /etc/init.d/lighttpd start