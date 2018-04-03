#Validated on Jan-4,2018,
## change maximum shared account of camera
#Writer: JinHo, Chang
#sed -i -e '/var max_shared_visitors =/d'  /var/www/SAT-CLOUDNVR/js/share_device.js

echo "current max shared number is ==> $(grep 'max_shared_visitors =' /var/www/SAT-CLOUDNVR/js/share_device.js)"

if [ ! -z "$1" ]; then
  sed -i -e 's|var max_shared_visitors = .*|var max_shared_visitors = '"$1"';|'  /var/www/SAT-CLOUDNVR/js/share_device.js
  echo "changed max shared number to $(grep 'max_shared_visitors =' /var/www/SAT-CLOUDNVR/js/share_device.js)"
else
  echo "No parameter input!! please type 'sh sharecamera.sh <number of shared cameras>'\n" 
fi