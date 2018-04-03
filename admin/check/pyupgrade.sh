#paramiko 1.10.1 create error "FutureWarning: CTR mode needs counter parameter, not IV"
#sudo apt-get -y --force-yes install python-paramiko
config=$(dpkg -l | grep paramiko | awk {print'$3'} | head -c 6 )
if [ $config = "1.10.1" ]; then
	sudo apt-get -y --force-yes install python-pip
	sudo python -m pip install --upgrade paramiko==1.18.3
fi