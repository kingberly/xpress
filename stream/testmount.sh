config_info=$(grep '/var/evostreamms/media' /proc/mounts)
if [ ! -z "$config_info" ]; then
  echo "umount shared nas"
  umount /var/evostreamms/media
fi
        config_info2=$(grep '/var/evostreamms/media' /etc/rc.local)
        eval $config_info2 