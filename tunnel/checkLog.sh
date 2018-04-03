#delete small files older than 30mins
find  /var/log/tunnel_server/tunnel_server.log*  -type f -size -200c -mmin +30 -exec rm {} +
lastfile=$(ls -lc /var/log/tunnel_server/tunnel_server.log* |tail -n 1| awk '{print$9}')
#lastfile=$(ls -Art /var/log/tunnel_server/tunnel_server.log* | tail -n 1)
#config=$(du -h $lastfile | awk '{print$1}') 
#fileUnit=$(echo $config | tail -c 2)
#if [ "$fileUnit" = "M" ]; then
#    len=${#config}
#    fileSize=$(($len-1)) 
#    fileSize=$(echo $config | head -c $fileSize )
#    fileSizeN=$(expr $fileSize)
#if [ $fileSizeN -gt 100 ]; then
config=$(du -k $lastfile | awk '{print$1}') 
fileSizeN=$(expr $config) 
if [ $fileSizeN -gt 30720 ]; then   #30M as quantity K     
  /etc/init.d/tunnel_server restart
  echo "tunnel log file (size= $fileSizeN K) recycled!"
else
  echo "`date '+%Y/%m/%d %H:%M:%S'`: current log file size is $config K." 
fi 