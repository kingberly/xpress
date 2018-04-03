
if [ ! -z "$1" ]; then
  if [ "$1" = "default" ]; then
    sed -i -e 's|interval_in_minutes =.*|interval_in_minutes = 60|' /usr/local/lib/stream_server_control/daemon.py
  else
    config_info=$(sed -n '/interval_in_minutes =/p' /usr/local/lib/stream_server_control/daemon.py)
    sed -i -e 's|interval_in_minutes =.*|interval_in_minutes = '"$1"'|' /usr/local/lib/stream_server_control/daemon.py
    echo "replace from $config_info to $1"
    echo -n "Need to restart stream_server to take effect, Enter Y to restart now? "
    read ANS
    if [ "$ANS" = "y" -o "$ANS" = "Y" ]; then
      SCRIPT_ROOT=`dirname "$0"`
      sudo sh $SCRIPT_ROOT/restartstr.sh  
    fi
  fi
else
  config_info=$(sed -n '/interval_in_minutes =/p' /usr/local/lib/stream_server_control/daemon.py)
  echo "current: $config_info minutes"
  echo "usage: sh $0 <default/60/10/...>"
fi
