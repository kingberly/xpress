if [ -z "$1" -o -z "$2" ]; then
  echo "No parameter!, usage: sh patchjar.sh <stream5544_enable>"
  exit
fi
  #patch new jar file SAT_P2P_iveda.jar=304, ref_library_iveda.jar=1304
  #check old file size SAT_P2P_iveda.jar=304, ref_library_iveda.jar=1756  
#  config_info=$(du /var/www/SAT-CLOUDNVR/jar/ref_library_iveda.jar | awk '{print $1}')
config_info=$(sudo /etc/init.d/lighttpd status | grep 'running')
if [ ! -z "$config_info" ]; then #-z test if its empty string
  echo "stop web service before patching jar file"
  /etc/init.d/lighttpd stop
fi
if [ "$1" != "stream5544_enable" ]; then
     #default jar before v2.0.2
    if [ "$2" = "megasys_enable" ]; then
        cp solevision/SAT_P2P_iveda.jar /var/www/SAT-CLOUDNVR/jar/
        cp solevision/ref_library_iveda.jar /var/www/SAT-CLOUDNVR/jar/
        echo "replaced sole-vision jar files\n"
    else
        cp ivedasol/SAT_P2P_iveda.jar /var/www/SAT-CLOUDNVR/jar/
        cp ivedasol/ref_library_iveda.jar /var/www/SAT-CLOUDNVR/jar/
        echo "replaced iveda jar files\n"
    fi
else
  #stream5544_enable=1 using new jar after v2.1.1, expired after 2016, Feb
  if [ "$2" = "megasys_enable" ]; then
      cp solevision/SAT_P2P_iveda_stream5544.jar /var/www/SAT-CLOUDNVR/jar/SAT_P2P_iveda.jar
      cp solevision/ref_library_iveda_stream5544.jar /var/www/SAT-CLOUDNVR/jar/ref_library_iveda.jar
      echo "replaced stream5544 jar file for solevision release\n"
  else
      cp ivedasol/SAT_P2P_iveda_stream5544.jar /var/www/SAT-CLOUDNVR/jar/SAT_P2P_iveda.jar
      cp ivedasol/ref_library_iveda_stream5544.jar /var/www/SAT-CLOUDNVR/jar/ref_library_iveda.jar      
      echo "replaced stream5544 jar file for ivedasol release\n"
  fi 
fi
echo "Must start web server\n"
#/etc/init.d/lighttpd start 