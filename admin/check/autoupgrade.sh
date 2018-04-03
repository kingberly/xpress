#sudo apt-get upgrade -s | grep -i security
#sudo apt-get -s upgrade
#sudo apt-get autoremove
sudo apt-get -V -u -y --force-yes upgrade
#sudo unattended-upgrades -d
config=$(dpkg -l | grep unattended-upgrades)
if [ -z "$config" ]; then
sudo dpkg-reconfigure unattended-upgrades
#manuall upgrade
#sudo apt-get update
#sudo apt-get dist-upgrade
fi