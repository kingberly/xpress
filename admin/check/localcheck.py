import sys
from sys import stdin
import subprocess
import datetime
import os

def runCmdOutput(cmd):
    try:
        #print subprocess.check_output(cmd, shell=True)
        print subprocess.check_output(cmd, shell=True, stderr=subprocess.STDOUT)
    except Exception as e:
        #print "Fail:" + str(e)
        print "Not Found. "+str(e.output)          #if return nothing
        

def runSudoCmdOutput(cmd):
#echo <password> | sudo -S <command>
    pwd = "1qazxdr56yhN"
    try:
        print subprocess.check_output("echo "+pwd+" | sudo -S "+cmd, shell=True)
    except Exception as e:
        print "Sudo Cmd Fail:" + str(e)

def checkLastFile(path):
    try:
        cmd = "ls -Art "+ path +" | tail -n 1"
        return subprocess.check_output(cmd, shell=True)
    except Exception as e:
        print "checkLastFile Fail:" + str(e)


class bcolors:
    RESET   = '\033[0m'
    WHITE   = '\033[0;37m'
    YELLOW  = '\033[0;33m' 
    GREEN   = '\033[0;32m'
    BLUE    = '\033[0;34m'
    RED     = '\033[0;31m'
    MAGENTA = '\033[0;35m' 
    HIGHRED  = '\033[1;41m'
    HIGHBLUE = '\033[1;44m'
    HIGHCYAN ='\033[1;46m'
    HIGHMAGENTA ='\033[1;45m'
    HIGHBLACK   ='\033[1;30;47m'
#
# Main function
#
helpList = [
["memswap","show used swap memory by 'free | grep Swap | awk '{print $3}'"],
["checkmailer","sed -n '/$interval =/p' /var/www/SAT-CLOUDNVR/Mailer/configuration.php"],
["storage","du -h /media/videos"],
["sslconfig","cat /etc/lighttpd/conf-enabled/10-ssl.conf"],
["checkmailer","sed -n '/$interval =/p' /var/www/SAT-CLOUDNVR/Mailer/configuration.php"],
["overload","grep overload /var/log/lighttpd/error.log"],
["httpreq","netstat -an | grep ESTABLISHED | grep ':80' | wc -l"],
["haproxy.cfg","grep /etc/haproxy/haproxy.cfg"],
["tcpnetstat","netstat -n | awk '/^tcp/ {++S[$NF]} END {for(a in S) print a, S[a]}'"],
["storagelist","ls /var/evostreamms/media"],
["<MAC>/yyyymmdd","ls -lrtR /var/evostreamms/media/<MAC>/yyyymmdd"],
["ffmpeg","ps ax | grep [/u]sr/local/bin/ffmpeg"],
["ffmpegcount","ps ax | grep [/u]sr/local/bin/ffmpeg | wc -l"],
["vlc","ps ax | grep [v]lc"],
["live","find /var/evostreamms/temp -type f -size +1k"],
["livecount","ls /var/evostreamms/temp | wc -l"],
["liveall","ls /var/evostreamms/temp"],
["interval","sed -n '/interval_in_minutes =/p' /usr/local/lib/stream_server_control/daemon.py"],
["latest10","find /var/evostreamms/media/ -type f -mmin -10 -ls"],
["checkuid","grep evostreamd:x: /etc/passwd"],
["myip","nslookup myip.opendns.com 208.67.222.222"],
["<MAC> clouduser","grep -m 1 '001BFE054D9D - rtsp://clouduser:' /var/log/evostreamms/stream_server_control.log | awk '{print $7,$9}'"],
["clouduser","grep clouduser /var/log/evostreamms/stream_server_control.log | awk '{print $7,$9}'"],
["ffmpegver","/usr/local/bin/ffmpeg -version"],
["tlog","tail /var/log/tunnel_server/tunnel_server.log.xxxxxxxxxx"],
["rlog","tail /var/log/rtmpd_control.log"],
["tlogmysql","grep 'mysql_real_connect:' /var/log/tunnel_server/tunnel_server.log.xxxxxxxxxx"],
["logg","tail /var/log/tunnel_server/tunnel_server_guard.logxxx"],
["<MAC>","grep '<MAC>' /var/log/tunnel_server/tunnel_server.log.xxxxxxxxxx"],
["<MAC> status","grep -r '<MAC>' /var/log/tunnel_server/"],
["connlog","grep 'TunnelLink' /var/log/tunnel_server/tunnel_server.log.xxxxxxxxxx"],
["mlog","tail /var/log/alarm/mailer_YYYY-MM-DD.log"],
["llog","tail /var/log/lighttpd/error.log"],
["wlog","tail /var/log/lighttpd/web_server_control.log"],
["wlog1","tail /var/log/lighttpd/web_server_control.log.1"],
["glogmv","tail /var/log/glusterfs/media-videos.log"],
["slog","tail /var/log/evostreamms/stream_server_control.log"],
["glogmedia","tail /var/log/glusterfs/var-evostreamms-media.log"],
["nlog","tail /var/log/nginx/error.log"],
["dblog","tail /var/log/mysql/error.log"],
["trestart","/etc/init.d/tunnel_server restart"],
["rtmprestart","/etc/init.d/rtmpd_control restart"],
["wrestart","/etc/init.d/web_server_control restart"],
["webrestart","/etc/init.d/lighttpd restart"],
["phprestart","/etc/init.d/php5-fpm restart"],
["streamrestart","sh stream/restartstr.sh"],
["strrestart","/etc/init.d/stream_server_control restart"],
["memrestart","service memcached restart"],
["iosrestart","killall php5-fpm;/etc/init.d/php5-fpm start;/etc/init.d/lighttpd restart;/etc/init.d/web_server_control restart"],
["haproxyrestart","service haproxy restart"],
["phpkill","killall php5-fpm"],
["alog","tail /var/log/apache2/error.log"],  
["alog80","tail /var/log/apache2/error80.log"],
["aalog","tail /var/log/apache2/access.log"],
["aalog80","tail /var/log/apache2/access80.log"],
["nginxrestart","/etc/init.d/nginx restart"],
["checksrvt","tail /var/tmp/tunservice.log"],
["checksrvs","tail /var/tmp/streamservice.log"],
["checksrvr","tail /var/tmp/rtmpdservice.log"],
["core","cat /proc/cpuinfo | grep processor | wc -l"],
["mem","grep MemTotal /proc/meminfo"],
["disk","lsblk"],
["","mysql -u isatRoot -pisatPassword"],
["","mysql -u root -pivedaMysqlRoot"],
["help","print help list"]
]

#command required sudo permission
cmdSudoList = ["nginxrestart","llog","wlog","glogmv","glogmedia","checkuid","streamrestart","rtmprestart","strrestart","trestart","haproxyrestart","iosrestart","wrestart","webrestart","sslconfig","phpkill","memrestart"]
#special command, required to manually write code to command list
spList = ["connlog","logg","tlog","mlog","help","tlogmysql"]
#rest of not mentioned will be automatically in the cmdList
cmdList = []
for sublist in helpList:
    if (sublist[0] not in cmdSudoList) and (sublist[0] not in spList):
      cmdList.append(sublist[0]) 


isRoot = 0
print bcolors.HIGHRED +datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S") + bcolors.RESET 
msg = 'type "help" or [Enter] to leave"'
msg = bcolors.HIGHBLACK + msg + bcolors.RESET
print msg
path = stdin.readline()
while len(path) > 0:
    if (path == "\n"):
            break;
    elif path.lower() == "mlog\n" :
        filepath = "/var/log/alarm/mailer_*"
        if (filepath != ""):
            cmdpath = checkLastFile(filepath)
        print bcolors.MAGENTA + ">> "+path.lower().strip("\n") +" (tail "+cmdpath.strip("\n") +")"+ bcolors.RESET
        runCmdOutput("tail "+cmdpath.strip("\n"))
    elif path.lower() == "connlog\n" :
        filepath = "/var/log/tunnel_server/tunnel_server.log*"
        if (filepath != ""):
            cmdpath = checkLastFile(filepath)
        print bcolors.MAGENTA + ">> "+path.lower().strip("\n") +" (grep 'TunnelLink'  "+cmdpath.strip("\n") +")"+ bcolors.RESET
        runCmdOutput("grep 'TunnelLink' "+cmdpath.strip("\n"))
    elif path.lower() == "logg\n" :
        filepath = "/var/log/tunnel_server/tunnel_server_guard.log*"
        if (filepath != ""):
            cmdpath = checkLastFile(filepath)
        print bcolors.MAGENTA + ">> "+path.lower().strip("\n") +" (tail "+cmdpath.strip("\n") +")"+ bcolors.RESET
        runCmdOutput("tail "+cmdpath.strip("\n")) 
    elif path.lower() == "tlogmysql\n" :
        filepath = "/var/log/tunnel_server/tunnel_server.log*"
        if (filepath != ""):
            cmdpath = checkLastFile(filepath)
        print bcolors.MAGENTA + ">> "+path.lower().strip("\n") +" (grep 'mysql_real_connect:' "+cmdpath.strip("\n") +")"+ bcolors.RESET
       
        runCmdOutput("grep 'mysql_real_connect:' "+cmdpath.strip("\n"))
    elif path.lower() == "tlog\n" :
        filepath = "/var/log/tunnel_server/tunnel_server.log*"
        if (filepath != ""):
            cmdpath = checkLastFile(filepath)
        print bcolors.MAGENTA + ">> "+path.lower().strip("\n") +" (tail "+cmdpath.strip("\n") +")"+ bcolors.RESET
        runCmdOutput("tail "+cmdpath.strip("\n"))

    elif path.lower().strip("\n") in cmdList:
      for sublist in helpList:
          if sublist[0] == path.lower().strip("\n") :
              inputcmd = sublist[1]
      print bcolors.MAGENTA + ">> "+path.lower().strip("\n") + " ("+inputcmd +")"+ bcolors.RESET +" in cmdList"
      runCmdOutput (inputcmd)
    elif path.lower().strip("\n") in cmdSudoList:
      for sublist in helpList:
          if sublist[0] == path.lower().strip("\n") :
              inputcmd = sublist[1]
      print bcolors.HIGHMAGENTA + ">> "+ path.lower().strip("\n") + " ("+inputcmd +")"+ bcolors.RESET +" in cmdSudoList"
      runSudoCmdOutput (inputcmd)

    elif path.lower() == "sudo\n":
      isRoot = True
      print "set next command permission to sudo"
    elif path.lower() == "unsudo\n":
      isRoot = 0
      print "set next command permission to normal"
    elif path.lower() == "help\n":
      for i in range(len(helpList)):
          print '%-15s: %s' % (helpList[i][0], helpList[i][1])
    else:
      if bool(isRoot):
        print bcolors.HIGHMAGENTA + ">> "+ path.lower().strip("\n") + bcolors.RESET
        runSudoCmdOutput(path)
      else:
        print bcolors.MAGENTA + ">> "+path.lower().strip("\n") + bcolors.RESET
        runCmdOutput(path)
    print bcolors.HIGHRED + datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S") + bcolors.RESET 
    print msg
    path = stdin.readline()