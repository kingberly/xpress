netstat -n | awk '/^tcp/ {++S[$NF]} END {for(a in S) print a, S[a]}'
config_info=$(grep '^net.ipv4.tcp_syncookies=' /etc/sysctl.conf) 
if [ ! -z "$config_info" ]; then
    sed -i -e '/^net.ipv4.tcp_syncookies=/d'  /etc/sysctl.conf
    sed -i -e '$a\net.ipv4.tcp_syncookies=1'  /etc/sysctl.conf
    #sed -i -e 's|^#net.ipv4.tcp_syncookies=1|net.ipv4.tcp_syncookies=1|' /etc/sysctl.conf
    echo "replace tcp_syncookies @ sysctl"
else
    sed -i -e '$a\net.ipv4.tcp_syncookies=1'  /etc/sysctl.conf
    echo "add tcp_syncookies @ sysctl" 
fi

config_info=$(grep '^net.ipv4.tcp_max_syn_backlog=' /etc/sysctl.conf) 
if [ ! -z "$config_info" ]; then
    sed -i -e '/^net.ipv4.tcp_max_syn_backlog=/d'  /etc/sysctl.conf
    sed -i -e '$a\net.ipv4.tcp_max_syn_backlog=65536'  /etc/sysctl.conf
    echo "replace tcp_max_syn_backlog @ sysctl"
else
    sed -i -e '$a\net.ipv4.tcp_max_syn_backlog=65536'  /etc/sysctl.conf
    echo "add tcp_max_syn_backlog @ sysctl" 
fi 

config_info=$(grep '^net.ipv4.tcp_fin_timeout=' /etc/sysctl.conf) 
if [ ! -z "$config_info" ]; then
    sed -i -e '/^net.ipv4.tcp_fin_timeout=/d'  /etc/sysctl.conf
    sed -i -e '$a\net.ipv4.tcp_fin_timeout=1'  /etc/sysctl.conf
    echo "replace tcp_fin_timeout @ sysctl"
else
    sed -i -e '$a\net.ipv4.tcp_fin_timeout=1'  /etc/sysctl.conf
    echo "add tcp_fin_timeout @ sysctl" 
fi
config_info=$(grep '^net.ipv4.tcp_tw_reuse=' /etc/sysctl.conf) 
if [ ! -z "$config_info" ]; then
    sed -i -e '/^net.ipv4.tcp_tw_reuse=/d'  /etc/sysctl.conf
    sed -i -e '$a\net.ipv4.tcp_tw_reuse=1'  /etc/sysctl.conf
    echo "replace tcp_tw_reuse @ sysctl"
else
    sed -i -e '$a\net.ipv4.tcp_tw_reuse=1'  /etc/sysctl.conf
    echo "add tcp_tw_reuse @ sysctl" 
fi
config_info=$(grep '^net.ipv4.tcp_tw_recycle=' /etc/sysctl.conf) 
if [ ! -z "$config_info" ]; then
    sed -i -e '/^net.ipv4.tcp_tw_recycle=/d'  /etc/sysctl.conf
    sed -i -e '$a\net.ipv4.tcp_tw_recycle=1'  /etc/sysctl.conf
    echo "replace tcp_tw_recycle @ sysctl"
else
    sed -i -e '$a\net.ipv4.tcp_tw_recycle=1'  /etc/sysctl.conf
    echo "add tcp_tw_recycle @ sysctl" 
fi

sudo sysctl -p