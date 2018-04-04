#X02 only service
if [ -f "/etc/lighttpd/conf-available/15-svw-api.conf" ]; then
if [ -z "$(grep '/map/' /etc/lighttpd/conf-available/15-svw-api.conf)" ]; then
sudo cat 15-svw-api.conf >> /etc/lighttpd/conf-available/15-svw-api.conf
#sed -i -e '$a\\$HTTP["url"] =~ "^/map/" {\n    proxy.server = (\n        "" => ( (\n            "host" => "192.168.1.113",\n            "port" => 80\n        ) )\n    )\n}\n' /etc/lighttpd/conf-available/15-svw-api.conf
sudo /etc/init.d/lighttpd restart
fi 
fi

