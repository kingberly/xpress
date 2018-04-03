from streamsrv import *
from vminfo import * 
import datetime
import os
from sys import stdin

#
# Main function
#
helpList = [
["","======================"],
["memswap","show used swap memory by 'free | grep Swap | awk '{print $3}'"],
["storagelist","ls /var/evostreamms/media"],
["<MAC>/yyyymmdd","ls -lrtR /var/evostreamms/media/<MAC>/yyyymmdd"],
["setxx","set to watch sepecific server"],
["setlist","list server info"],
["log","tail /var/log/evostreamms/stream_server_control.log"],
["glog","tail /var/log/glusterfs/var-evostreamms-media.log"],
["ffmpeg","ps ax | grep [/u]sr/local/bin/ffmpeg"],
["ffmpegcount","ps ax | grep [/u]sr/local/bin/ffmpeg | wc -l"],
["vlc","ps ax | grep [v]lc"],
["live","find /var/evostreamms/temp -type f -size +1k"],
["livecount","ls /var/evostreamms/temp | wc -l"],
["liveall","ls /var/evostreamms/temp"],
["nginxerr","tail /var/log/nginx/error.log"],
["nginx","tail /var/log/nginx/access.log"],
["interval","sed -n '/interval_in_minutes =/p' /usr/local/lib/stream_server_control/daemon.py"],
["putfile","sftp.put $local/file $remote/home/$USER/file"],
["putfilem","upload multiple files from argument file1/file2/file3"],
["getfile","sftp.get $remote/file $local/file"],
["test","grep 'monitor Resurrecting recording ' /var/log/evostreamms/stream_server_control.log"],
["testlog","put 'test' command in logfile and 'getfile'"],
["latest10","find /var/evostreamms/media/ -type f -mmin -10 -ls"],
["restart","sh stream/restartstr.sh"],
["strrestart","/etc/init.d/stream_server_control restart"],
["checkuid","grep evostreamd:x: /etc/passwd"],
["myip","nslookup myip.opendns.com 208.67.222.222"],
["myip2","dig +short myip.opendns.com @resolver1.opendns.com"],
#["<UID-MAC> clouduser","grep -m 1 'addStream Adding stream: Z01CC-001BFE054D9D' /var/log/evostreamms/stream_server_control.log  | awk '{print $7,$9}'"],
["<MAC> clouduser","grep -m 1 '001BFE054D9D - rtsp://clouduser:' /var/log/evostreamms/stream_server_control.log | awk '{print $7,$9}'"],
["clouduser","grep clouduser /var/log/evostreamms/stream_server_control.log | awk '{print $7,$9}'"],
["diskusage","df -h / | grep / | awk '{print $5}'"],
["ffmpegver","/usr/local/bin/ffmpeg -version"],
["checksrv","tail /var/tmp/streamservice.log"],
["checksrvcount","sed -n 1p /var/tmp/streamservice.log;grep 'restart stream' /var/tmp/streamservice.log | wc -l"],
["checkdb","grep -rnw /var/log/evostreamms -e 'gone'"],
["reporton","set report mode on, will write log to /var/tmp/x41_<date>.log"],
["reportoff","set report mode off"],
["umountforce","sudo fuser -km /var/evostreamms/media/"],
["umountlsof","sudo lsof /var/evostreamms/media/"],
["unmountvideo","sudo umount /var/evostreamms/media/"],
["mountstatus","df -h /var/evostreamms/media/ | grep xpress"], 
["","sudo sh stream/mountcheck.sh X02"],
["help","print help list"],
["","======================"]
]

spList = ["setlist","putfile","putfilem","getfile","test","help","testlog","testlog2"]
spList2 = ["vlc","ffmpeg","interval","storagelist","log","evolog","live","liveall"]
cmdList = []
for sublist in helpList:
    if (sublist[0] not in spList2) and (sublist[0] not in spList):
      cmdList.append(sublist[0])  

allHosts = dict(strHosts.items())
specifyVM = "AA"
 
msg = 'type "Z01CC-<MAC>/<DATE>" to list file under dir., "storagelist" to list dir.\n'
msg += ' from 1st stream server, "latest10" to list last 10 minutes recordings\n'
msg += ' "live" or "liveall" or "livecount" to list liveview recording files under temp,\n'
msg += ' "vlc", "ffmpeg" ,"ffmpegver" ,"ffmpegcount" to get online streaming count,\n'
msg += ' "log", "nginx"(or evolog), "interval", "help",\n'
msg += ' "setAA" to check all stream servers status, "set30" to check stream1..etc\n'
msg += ' any command for all stream server or [Enter] to leave'
msg = bcolors.HIGHBLACK + msg + bcolors.RESET
if (len(sys.argv)>1): #command
    print str(oem) +" @"+datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    path=str(sys.argv[1]) + "\n"
    if (len(sys.argv)>2): #specific server
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
            break;
    elif len(path)== 9 and path[:6]=="latest" and path[8] == "\n" :
      for hostname, host in allHosts.items():
        try:    
          runScriptOutput(host,"find /var/evostreamms/media/ -type f -mmin -" +path[6:8] +" -ls")
          #runScriptOutput(host,"find /var/evostreamms/media/ -type f -mmin -10 -ls")
        except Exception as e:
            continue
        break;
    elif len(path)== 6 and path[:3]=="set" and path[5] == "\n" :
      specifyVM = path[3:5]
      if specifyVM == "AA":
        allHosts = dict(strHosts.items())
        print "query all servers"
      else:
         if not (strHosts.has_key(int(specifyVM)) ): 
            print "No such server, set to query all"
            allHosts = dict(strHosts.items())
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
    elif path.lower() == "vlc\n":
      for hostname, host in allHosts.items():
        try:    
          checkProcess(host,"ps ax | grep [v]lc")
        except Exception as e:
            continue
    elif path.lower() == "ffmpeg\n":
      for hostname, host in allHosts.items():
        try:
          checkProcess(host,"ps ax | grep [/u]sr/local/bin/ffmpeg")
        except Exception as e:
            continue            
    elif path.lower() == "log\n":
      for hostname, host in allHosts.items():
        try:
          runScriptOutput(host,"tail -n 20 /var/log/evostreamms/stream_server_control.log")
        except Exception as e:
            continue 
    elif path.lower() == "evolog\n":
      for hostname, host in allHosts.items():
        try:        
          filepath = checkLastFile(host,"/var/log/evostreamms/")
          if (filepath != ""):
            runScriptOutput(host,"tail -n 20 /var/log/evostreamms/"+filepath)
        except Exception as e:
            continue
    elif path.lower() == "interval\n":
      for hostname, host in allHosts.items():
        try:
          checkValue(host,"interval_in_minutes =","/usr/local/lib/stream_server_control/daemon.py")
        except Exception as e:
            continue 
    elif path.lower() == "storagelist\n":
      for hostname, host in allHosts.items():
        try:
          getIterationOutput(host,"ls /var/evostreamms/media")
        except Exception as e:
            continue
        break;
    elif path.lower() == "live\n":
      for hostname, host in allHosts.items():
        try:
          getIterationOutput(host,"find /var/evostreamms/temp -type f -size +1k")
        except Exception as e:
            continue
    elif path.lower() == "liveall\n":
      for hostname, host in allHosts.items():
        try:
          getIterationOutput(host,"ls /var/evostreamms/temp")
        except Exception as e:
            continue
    elif len(path)> 12 and path[12] == "/" and path.find(" ")==-1:
      #path = "001BFE054EDF/20141026"
      for hostname, host in allHosts.items():
        try:
          checkCloudRecordingbyPath(host, "ls -lrtR /var/evostreamms/media/Z01CC-"+path)
        except Exception as e:
            continue
        break;
    elif len(path)> 18 and path[18] == "/" and path.find(" ")==-1:
      #path = "Z01CC-001BFE054EDF/20141026"
      for hostname, host in allHosts.items():
        try:
          checkCloudRecordingbyPath(host, "ls -lrtR /var/evostreamms/media/"+path)
        except Exception as e:
            continue
        break;
    elif path.lower() == "test\n":
      for hostname, host in allHosts.items():
        try:
          runScriptOutput(host,"grep 'monitor Resurrecting recording ' /var/log/evostreamms/stream_server_control.log")
        except Exception as e:
            continue
    elif path.lower() == "testlog\n":
      for hostname, host in allHosts.items():
        try:
          runScriptOutput(host,"rm -rf stream_"+host[0]+"_testlog_*")
          fileExt = "stream_"+host[0]+"_testlog_" + datetime.datetime.now().strftime("%Y%m%d%H%M%S")
          runScriptOutput(host,"uptime > "+fileExt)
          runScriptOutput(host,"grep 'monitor Resurrecting recording ' /var/log/evostreamms/stream_server_control.log >> "+fileExt)
          fileSize = getOneOutput(host,"du "+fileExt+" | awk '{print $1}'")
          if (int(fileSize) < 50):
              runScriptOutput(host,"echo '==============' >>"+fileExt)
              runScriptOutput(host,"grep 'monitor Resurrecting recording ' /var/log/evostreamms/"+checkSecondLastFile(host,"/var/log/evostreamms/")+" >> "+fileExt)
          ssh = createSSHClient(host[0], int(host[1]), host[2], host[3])
          sftp=ssh.open_sftp()
          print "remote file:%s => local file:%s" %(fileExt, os.getcwd()+"/"+fileExt)
          sftp.get(fileExt,os.getcwd()+"/"+fileExt)
          sftp.close()
          ssh.close()
        except Exception as e:
            print str(e)
            continue
    elif path.lower() == "testlog2\n":
      for hostname, host in allHosts.items():
        try:
          runScriptOutput(host,"rm -rf stream_"+host[0]+"_testlog_*")
          fileExt = "stream_"+host[0]+"_testlog_" + datetime.datetime.now().strftime("%Y%m%d%H%M%S")
          runScriptOutput(host,"uptime > "+fileExt)
          runScriptOutput(host,"grep 'monitor Resurrecting recording ' /var/log/evostreamms/"+checkSecondLastFile(host,"/var/log/evostreamms/")+" >> "+fileExt)
          ssh = createSSHClient(host[0], int(host[1]), host[2], host[3])
          sftp=ssh.open_sftp()
          print "remote file:%s => local file:%s" %(fileExt, os.getcwd()+"/"+fileExt)
          sftp.get(fileExt,os.getcwd()+"/"+fileExt)
          sftp.close()
          ssh.close()
        except Exception as e:
            print str(e)
            continue
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
    elif len(path)== 29 and path[:17].find(" ")==-1 and path[19:] == "clouduser\n":
    #grep -m 1 'addStream Adding stream: Z01CC-001BFE054D9D' /var/log/evostreamms/stream_server_control.log
      for hostname, host in allHosts.items():
          try:
              runScriptOutput (host, "grep -m 1 'addStream Adding stream: " + path[:18] + "' /var/log/evostreamms/stream_server_control.log | awk '{print $7,$9}'") 
          except Exception as e:
              continue
    elif len(path)== 23 and path[:11].find(" ")==-1 and path[13:] == "clouduser\n":
    #grep -m 1 '001BFE054D9D - rtsp://clouduser:' /var/log/evostreamms/stream_server_control.log
      for hostname, host in allHosts.items():
          try:
              runScriptOutput (host, "grep -m 1 '" + path[:12] + " - rtsp://clouduser:' /var/log/evostreamms/stream_server_control.log | awk '{print $7,$9}'") 
          except Exception as e:
              continue
    elif path.lower() == "help\n":
        for i in range(len(helpList)):
          print '%-15s: %s' % (helpList[i][0], helpList[i][1])
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
    elif path.lower().strip("\n") in cmdList:
      for sublist in helpList:
          if sublist[0] == path.lower().strip("\n") :
              inputcmd = sublist[1]
      for hostname, host in allHosts.items():
          try:
              runScriptOutput (host, inputcmd)
          except Exception as e:
              continue
    else:
      for hostname, host in allHosts.items():
        try:
          runScriptOutput (host, path)
        except Exception as e:
            continue
    if (len(sys.argv)>1): #one time command only
      break
    print bcolors.HIGHRED + str(oem) +" @"+datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S") + bcolors.RESET
    print msg
    path = stdin.readline()