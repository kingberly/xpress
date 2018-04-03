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
["setxx","set to watch sepecific server"],
["setlist","list server info"],
["log","tail /var/log/rtmpd_control.log"],
["putfile","sftp.put $local/file $remote/home/$USER/file"],
["getfile","sftp.get $remote/file $local/file"],
["restart","/etc/init.d/rtmp_server restart"],
["nginxrestart","/etc/init.d/nginx restart"],
["myip","nslookup myip.opendns.com 208.67.222.222"],
["checksrv","tail /var/tmp/rtmpdservice.log"],
["","12 digits MAC: grep '<MAC>' /var/log/rtmpd_control.log"],
["<MAC> status","grep '<MAC>' /var/log/rtmpd_control.log*"],
["reporton","set report mode on, will write log to /var/tmp/x41_<date>.log"],
["reportoff","set report mode off"],
["help","print help list"],
["","======================"]
]
spList = ["help","setlist","setxx","putfile","getfile"]
cmdList = []
for sublist in helpList:
    if (sublist[0] not in spList):
      cmdList.append(sublist[0])
allHosts = dict(rtmpHosts.items())
specifyVM = "AA"
msg = 'type "<MAC>" for camera latest status,"<MAC> status" for full camera status,\n'
msg += ' "restart" for rtmp restart, "log" or "rtmplog" for rtmp log,\n'
msg += ' "setAA" to check all servers status, "set40" to check rtmp-1..etc\n'
msg += ' any command for all tunnel server or [Enter] to leave'
msg = bcolors.HIGHBLACK + msg + bcolors.RESET 

if (len(sys.argv)>1): #command
    print str(oem) +" @"+datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    path=str(sys.argv[1]) + "\n"
    if (len(sys.argv)>2): #specific server
      specifyVM = str(sys.argv[2])
      if  (rtmpHosts.has_key(int(specifyVM)) ):
        allHosts = {int(specifyVM):vm[int(specifyVM)] }
    init.bColorTag = False
else:
  print bcolors.HIGHRED + str(oem) +" @"+datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S") + bcolors.RESET
  print msg
  path = stdin.readline()

while len(path) > 0:
    if (path == "\n"):
            break;
    elif len(path)== 6 and path[:3]=="set" and path[5] == "\n" :
    #setAA  set20
      specifyVM = path[3:5]
      if specifyVM == "AA":
        allHosts = dict(rtmpHosts.items())
        print "query all servers"
      else:
         if not (rtmpHosts.has_key(int(specifyVM)) ): 
            print "No such server, set to query all"
            allHosts = dict(rtmpHosts.items())
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
    elif path.lower() == "log\n":
      for hostname, host in allHosts.items():
          try:
              runScriptOutput(host,"tail -n 20 /var/log/rtmpd_control.log")
          except Exception as e:
              continue
    elif path.lower() == "restart\n":
      for hostname, host in allHosts.items():
          try:
              runScriptOutput (host, "/etc/init.d/rtmpd_control restart")
          except Exception as e:
              continue
    elif path.lower() == "rtmplog\n":
      for hostname, host in allHosts.items():
          try:
              runScriptOutput(host,"tail -n 20 /var/log/rtmpd_control.log")
          except Exception as e:
              continue
    elif len(path)== 13 and path.find(" ")==-1:
      for hostname, host in allHosts.items():    
          try:
              runScriptOutput (host, "grep '" + path[:12] + "' /var/log/rtmpd_control.log")
          except Exception as e:
              continue
    elif len(path)== 20 and path[:11].find(" ")==-1 and path[13:] == "status\n":
      for hostname, host in allHosts.items():
          try:
            runScriptOutput (host, "grep '" + path[:12] + "' /var/log/rtmpd_control.log*")
          except Exception as e:
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
    elif path.lower() == "restart\n":
      for hostname, host in allHosts.items():
          try:
              runScriptOutput (host, "/etc/init.d/rtmp_server restart")
          except Exception as e:
              continue
    elif path.lower() == "help\n":
      for i in range(len(helpList)):
        print '%-15s: %s' % (helpList[i][0], helpList[i][1])
    elif path.lower() == "myip\n":
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