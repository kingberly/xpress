#!/bin/sh

Start() {
	echo "\\033[92m$1\\033[0m"
	PROCEDURE="$1"
}

Start "update apt package cache"
sudo apt-get update || Exit
#php install
sudo apt-get -y install python-software-properties || Exit
sudo add-apt-repository -y ppa:ondrej/php5 || Exit
#sudo apt-get -y upgrade || Exit

Start "install mail support"
cd mail
sudo sh install.sh
cd ..

Start "install ftp support"
#sudo userdel iveda
sudo adduser --gecos "" --disabled-password iveda;echo 'iveda:2wsxCFT6'| chpasswd;sudo usermod -a -G sudo iveda  
sudo apt-get install vsftpd
echo "vftp installed with user iveda under /home/iveda/"
#/etc/vsftpd.conf
sudo ufw allow ftp
sudo iptables -I INPUT -p tcp --destination-port 10090:10095 -j ACCEPT
config=$(grep "^anonymous_enable=NO" /etc/vsftpd.conf )
if [ -z "$config" ]; then
sed -i -e '$a\anonymous_enable=NO' /etc/vsftpd.conf
Start "disable anonymous_enable config"
fi
config=$(grep "^local_enable=YES" /etc/vsftpd.conf )
if [ -z "$config" ]; then
sed -i -e '$a\local_enable=YES' /etc/vsftpd.conf
Start "add local_enable config"
fi
config=$(grep "^pasv_enable" /etc/vsftpd.conf )
if [ -z "$config" ]; then
sed -i -e '$a\pasv_enable=YES' /etc/vsftpd.conf
sed -i -e '$a\pasv_max_port=10095' /etc/vsftpd.conf
sed -i -e '$a\pasv_min_port=10090' /etc/vsftpd.conf
myip=$(dig +short myip.opendns.com @resolver1.opendns.com)
#sed -i -e '$a\pasv_address=125.227.139.173' /etc/vsftpd.conf
sed -i -e '$a\pasv_address='"$myip"'' /etc/vsftpd.conf
sed -i -e '$a\port_enable=YES' /etc/vsftpd.conf
echo "add pasv_enable configs"
fi
Start "configuraiton done. Please enable NAT port 21, 10090-10095"
sudo service vsftpd restart
netstat -tul | grep ftp
echo "download by wget ftp://iveda:2wsxCFT6@$myip/xxx"

Start "install php packages"
sudo apt-get -y install php5 php5-cgi php5-curl php5-mysql|| Exit
Start "install python packages"
sudo apt-get -y install python-mysqldb python-apt python-pip libav-tools openssl|| Exit
Start "install ffmpeg/vlc/libavcodec packages"
sudo apt-get -y install make vlc libavcodec-extra* libavformat-extra* libxslt1.1
sudo apt-get -y --force-yes install libfdk-aac0 libgme0 || Exit
#sudo dpkg -i "vlc-nox_2.1.6-0ubuntu14.04.1_amd64.deb" || Exit
#sudo apt-mark hold vlc-nox
sudo tar -C / -zxvf "ffmpeg.tar.gz" || Exit

sudo adduser --system evostreamd 2>/dev/null
sudo usermod -u 107 evostreamd 
sudo sh mountcheck.sh X02

tar zxvf ffmpeg-conv.tar.gz
chown ivedasuper. ../ffmpeg-3.0.1
sudo -u ivedasuper mv ffmpeg-3.0.1 ..
sudo -u ivedasuper cp ffmpeg-conv.sh ..
sudo mkdir /var/www
sudo mkdir /var/www/SAT-CLOUDNVR/
sudo cp -avr SAT-CLOUDNVR/* /var/www/SAT-CLOUDNVR/
