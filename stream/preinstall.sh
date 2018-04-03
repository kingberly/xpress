if [ -z "$(dpkg --get-selections | grep vlc)" ]; then
  wget "ftp://iveda:1qaz2wsX@118.163.90.31/IvedaIGS/TW/stream/stream_pkg.tar"
  if [ ! -d "pkg" ]; then
    mkdir pkg
  fi
  tar vxf stream_pkg.tar -C ./pkg
  cd pkg
  sudo sh install.sh
  rm -rf pkg/
  rm stream_pkg.tar
fi 