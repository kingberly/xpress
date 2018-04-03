#python install (sudo)
echo "install python/ python-paramiko"
apt-get -y --force-yes install python-paramiko
echo "Edit oem cid in vminfo.py to make sure you get correct set of VM info\n"
echo "cloudrec_check.py to check stream server status >> python cloudrec_check.py\n"
echo "web_check.py to check web server log >> python web_check.py\n"
echo "tunnel_check.py to check tunnel server log >> python tunnel_check.py\n"
echo "db_check.py to check sql database >> python db_check.py\n"
echo "pingme.py to ping all vms >> python pingme.py\n"
#sendviascp.sh will upload all sh files to all VMs, edit VM info in the sh file
echo "make sendviascp files by python makescp.py\n"
echo "sendviascp_x0x.sh usage >> sh sendviascp.sh\n"
#changehost.sh will change the desired host name of current VM
echo "changehost.sh usage >> sudo sh changehost.sh <hostname>\n"
#changeuuid.sh will change the uuid of current VM
echo "changeuuid.sh usage >> sudo sh changeuuid.sh\n"
#uuid_check_x01.py will check listed VM hostname and UUID, edit VM info in the py file
echo "uuid_check.py for all server uuid and hostname check >> python uuid_check.py\n"
#cloudrec_check_x01.py will check listed stream server temp folder and storage usage, edit VM info in the py file
echo "runCmd.py to execute command on all VMs >> python runCmd.py\n" 
#sh getCamStatusFile.sh Cam90.txt
#python getStreamingStatusTest.py 192.168.1.135 178
echo "'python getStreamingStatusFile.py <str LAN IP> text file' for streaming status from list of MAC address"
echo "'python getStreamingStatusTest.py <str LAN IP> xxx' for simulation camera status"
