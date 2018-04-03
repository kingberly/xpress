#remove authenticate
sudo bash -c "echo > /etc/nginx/stream_server_auth.conf"
sudo service nginx stop;sudo service nginx start 