import sys
import paramiko
from igsssh import execCommand
from igsssh import execSudoCommand
from streamsrv import *
from vminfo import * 
from sys import stdin
import datetime
import os

#
# Main function
#
helpList = [
["","======================"],
#sudo fuser -km /media/videos/  #force
["unmountvideo","sudo umount /media/videos"],
["mountstatus","df -h /media/videos | grep xpress"],
["memswap","show used swap memory by 'free | grep Swap | awk '{print $3}'"],
["setxx","set to watch sepecific server, AA for all, 99 for nonLB web, lb for LB"],
["setlist","list server info"],
["mlog","tail /var/log/alarm/mailer_YYYY-MM-DD.log"],
["mailer","wget -O - 'http://<WEB IP>/Mailer/configuration.php?interval=<interval>'"],
["storage","du -h /media/videos"],
["storagelist","ls /media/videos"],
["log","tail /var/log/lighttpd/error.log"],
["elog","grep overload /var/log/lighttpd/error.log"],
["wlog","tail /var/log/lighttpd/web_server_control.log"],
["wlog1","tail /var/log/lighttpd/web_server_control.log.1"],
["glog","tail /var/log/glusterfs/media-videos.log"],
["sslconfig","cat /etc/lighttpd/conf-enabled/10-ssl.conf"],
["checkmailer","sed -n '/$interval =/p' /var/www/SAT-CLOUDNVR/Mailer/configuration.php"],
["putfile","sftp.put $local/file $remote/home/$USER/file"],
["putfilem","upload multiple files from argument file1/file2/file3"],
["getfile","sftp.get $remote/file $local/file"],
["putfilehome","sftp.put $local/file $remote/home/file"],
["testlog","put 'listcount.sh, listsize.sh' command in logfile and 'getfile'"],
["restart","/etc/init.d/web_server_control restart"],
["webrestart","/etc/init.d/lighttpd restart"],
["phprestart","/etc/init.d/php5-fpm restart"],
["phpkill","killall php5-fpm"],
["overload","grep overload /var/log/lighttpd/error.log"],
["memrestart","service memcached restart"],
["httpreq","netstat -an | grep ESTABLISHED | grep ':80' | wc -l"],
["iosrestart","killall php5-fpm;/etc/init.d/php5-fpm start;/etc/init.d/lighttpd restart;/etc/init.d/web_server_control restart"],
["haproxyrestart","service haproxy restart"],
["haproxy.cfg","grep /etc/haproxy/haproxy.cfg"],
["tcpnetstat","netstat -n | awk '/^tcp/ {++S[$NF]} END {for(a in S) print a, S[a]}'"],
["reporton","set report mode on, will write log to /var/tmp/x41_<date>.log"],
["reportoff","set report mode off"],
["mountstatus","df -h /media/videos | grep xpress"], 
["","sudo sh web/mountcheck.sh X02"],
["help","print help list"],
["","======================"]
]
spList = ["memswap","help","setlist","setxx","mlog","mailer","putfile","putfilem","getfile","putfilehome","testlog","reporton","reportoff"]
cmdList = []
for sublist in helpList:
    if (sublist[0] not in spList):
      cmdList.append(sublist[0])
allHosts = dict(webHosts.items())
specifyVM = "AA"

msg = 'type "storage", "storagelist", "log", "checkmailer" to check mailer interval.\n'
msg += ' "mailer" to set interval, "mlog" to check mailer log,\n'
msg += ' "setAA" to check all servers status, "set10" to check web-1..etc\n'
msg += ' any command for all web server or [Enter] to leave"'
msg = bcolors.HIGHBLACK + msg + bcolors.RESET

if (len(sys.argv)>1): #command
    print str(oem) +" @"+datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    path=str(sys.argv[1]) + "\n"
    if (len(sys.argv)>2) :
      if (str(sys.argv[1]) != "putfilem" ): #web_check.py putfilem file1/file2/file3
          specifyVM = str(sys.argv[2]) #web_check.py cmd server#
          if  (allHosts.has_key(int(specifyVM)) ):
            allHosts = {int(specifyVM):vm[int(specifyVM)] }
        #init.bColorTag = False
else:
  print bcolors.HIGHRED + str(oem) +" @"+datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S") + bcolors.RESET 
  print msg
  path = stdin.readline()
while len(path) > 0:
    if (path == "\n"):
            break
    elif len(path)== 6 and path[:3]=="set" and path[5] == "\n" :
    #set00  set10
      specifyVM = path[3:5]
      if specifyVM == "AA":
        allHosts = dict(webHosts.items())
        print "query all web servers"
      elif specifyVM == "lb":
        allHosts = dict(lbHosts.items())
        print "query lb servers"
      else:
         if not (webHosts.has_key(int(specifyVM)) ): 
            print "No such server, set to query all"
            allHosts = dict(webHosts.items())
            specifyVM = "AA"
         else: 
            allHosts = {int(specifyVM):vm[int(specifyVM)] }
            print "set to query (%s) %s %s" % (specifyVM, vm[int(specifyVM)][4], vm[int(specifyVM)][0])
    elif path.lower() == "setlist\n":
      for hostname, host in allHosts.items():
        try:
          print '%-3s>%-15s: %-15s (%s) / %-15s' % (hostname,host[4], host[0],host[1], host[2])
        except Exception as e:
            continue
    elif path.lower() == "storagelist\n":
      for hostname, host in allHosts.items():
        try:
          getIterationOutput(host,"ls /media/videos/")
        except Exception as e:
            continue
        break                                                            
    elif path.lower() == "storage\n":
      for hostname, host in allHosts.items():
        try:
          getIterationOutput(host,"du -h /media/videos/")
        except Exception as e:
            continue
        break
    elif path.lower() == "log\n":
      for hostname, host in allHosts.items():
        try:
          runScriptOutput(host,"tail -n 20 /var/log/lighttpd/error.log")
        except Exception as e:
            continue
    elif path.lower() == "mlog\n":
      for hostname, host in allHosts.items():
        try:
          filepath = checkLastFile(host,"/var/log/alarm/")
          if (filepath != ""):
            runScriptOutput(host,"tail -n 20 /var/log/alarm/"+filepath)
        except Exception as e:
            continue
    elif path.lower() == "checkmailer\n":
      #sed -n '/$interval =/p' /var/www/SAT-CLOUDNVR/Mailer/configuration.php
      for hostname, host in allHosts.items():
        try:
          checkValue(host,"$interval =","/var/www/SAT-CLOUDNVR/Mailer/configuration.php")
          checkValue(host,"$host =","/var/www/SAT-CLOUDNVR/Mailer/configuration.php")
          checkValue(host,"$port =","/var/www/SAT-CLOUDNVR/Mailer/configuration.php")
          checkValue(host,"$username =","/var/www/SAT-CLOUDNVR/Mailer/configuration.php")
        except Exception as e:
            continue
    elif path.lower() == "mailer\n":
      print ('Input desired Mailer interval (mins):') 
      inputmin = stdin.readline()
      #if inputmin is not number, leave
      try:
          inputImin = int(inputmin)
          try:
            for hostname, host in allHosts.items():
              cmd = "wget -O - 'http://%s/Mailer/configuration.php?interval=%d'" % (host[0],inputImin)
              subprocess.check_output(cmd, shell=True)
          except Exception as e:
            print "wget Fail:" + str(e)
            continue
      except ValueError:
         print "Interval is not an integer!" 
    elif path.lower() == "testlog\n":
      for hostname, host in allHosts.items():
        try:
          fileExt = "web_"+host[0]+"_testlog_" + datetime.datetime.now().strftime("%Y%m%d%H%M%S")
          print "input date yyyymmdd:"
          date = stdin.readline()
          if len(date)== 9 :
            date = date[:8]
          else:
            date = datetime.datetime.now().strftime("%Y%m%d")
          runScriptOutput(host,"rm -rf web_"+host[0]+"_testlog_*") 
          runScriptOutput(host,"uptime > "+fileExt)
          runScriptOutput(host,"sh web/listcount.sh " +date+" >> "+fileExt)
          runScriptOutput(host,"sh web/listsize.sh " +date+" >> "+fileExt)
          runScriptOutput(host,"ls -l /media/videos/Z01CC-000000000011/" +date+" >> "+fileExt)
          ssh = createSSHClient(host[0], int(host[1]), host[2], host[3])
          #scp = SCPClient(ssh.get_transport()) #scp.get()
          sftp=ssh.open_sftp()
          print "remote file:%s => local file:%s" %(fileExt, os.getcwd()+"/"+fileExt)
          sftp.get(fileExt,os.getcwd()+"/"+fileExt)
          sftp.close()
          ssh.close()
        except Exception as e:
            print str(e)
            continue
        break
    elif path.lower() == "putfilem\n": #special upload command
          if (len(sys.argv)<=2) :
            break
          fileExt = str(sys.argv[2])
          #print fileExt
          for i, filename in enumerate(fileExt.split('/') ) :
              print "%d:%s" % (i, filename)
              if (filename == "") :
                  break
              for hostname, host in allHosts.items():
                try:
                  putFile(host,filename)
                except Exception as e:
                    print str(e)
                    continue
    elif path.lower() == "putfile\n":
          print "input filename to put:"
          fileExt = stdin.readline()
          if (fileExt == "\n") :
             print "skip, no filename"
          else :
              fileExt = fileExt.strip("\r\n")
              for hostname, host in allHosts.items():
                try:
                  putFile(host,fileExt)
                except Exception as e:
                    print str(e)
                    continue
    elif path.lower() == "putfilehome\n":
          print "input filename to put at /home/:"
          fileExt = stdin.readline()
          if (fileExt == "\n") :
             print "skip, no filename"
          else :
              fileExt = fileExt.strip("\r\n")
              for hostname, host in allHosts.items():
                try:
                  ssh = createSSHClient(host[0], int(host[1]), host[2], host[3])
                  sftp=ssh.open_sftp()
                  if (host[2] == "root") :
                        print "local file:%s => %s remote file:%s" %(os.getcwd()+"/"+fileExt, host[4] ,"/home/"+fileExt)
                        sftp.put(os.getcwd()+"/"+fileExt,"/home/"+fileExt) #local, remote
                  else :
                        print "local file:%s => %s remote file:%s" %(os.getcwd()+"/"+fileExt, host[4] ,"/home/"+host[2]+"/"+fileExt)
                        sftp.put(os.getcwd()+"/"+fileExt,"/home/"+fileExt) #local, remote
                  sftp.close()
                  ssh.close()
                except Exception as e:
                    print str(e)
                    continue
    elif path.lower() == "getfile\n":
      for hostname, host in allHosts.items():
        try:
          print "input filename to get:"
          fileExt = stdin.readline()
          if (fileExt == "\n") :
             raise ValueError('skip '+host[4]+', no filename')
          fileExt = fileExt.strip("\r\n")
          getFile(host,fileExt)          
        except Exception as e:
            print str(e)
            continue
    elif path.lower() == "memswap\n":
    #free | grep Swap | awk '{print $3}'
      for hostname, host in allHosts.items():
        try:
          runScriptOutput (host, "free | grep Swap | awk '{print $3}'")
        except Exception as e:
            continue 
    elif path.lower().strip("\n") in cmdList:
      for sublist in helpList:
          if sublist[0] == path.lower().strip("\n") :
              inputcmd = sublist[1]
      for hostname, host in allHosts.items():
          try:
              runScriptOutput (host, inputcmd)
          except Exception as e:
              continue  
    elif path.lower() == "reporton\n":
        init.bWriteLog = True
        if (init.bWriteLog):
          print "enable report mode"
        else:
          print "disable report mode"
    elif path.lower() == "reportoff\n":
        init.bWriteLog = False
        if (init.bWriteLog):
          print "enable report mode"
        else:
          print "disable report mode"
    elif path.lower() == "help\n":
      for i in range(len(helpList)):
          print '%-15s: %s' % (helpList[i][0], helpList[i][1])
    else:
      for hostname, host in allHosts.items():
        try:
            runScriptOutput(host,path)
        except Exception as e:
            continue
    if (len(sys.argv)>1): #one time command only
      break
    print bcolors.HIGHRED + str(oem) +" @"+datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S") + bcolors.RESET 
    print msg
    path = stdin.readline()