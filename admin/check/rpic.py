import sys
import paramiko
from igsssh import execCommand
from igsssh import execSudoCommand
from streamsrv import *
from vminfo import * 
from sys import stdin
import datetime
import os
import init

#
# Main function
#
helpList = [
["","======================"],
["mountvideo","sudo sh web/mountcheck.sh T04"],
["mountvideox01","sudo sh web/mountcheck.sh X01"],
["mountvideox02","sudo sh web/mountcheck.sh X02"],
["mountvideot05","sudo sh web/mountcheck.sh T05"],
["mountvideok01","sudo sh web/mountcheck.sh K01"],
["mountvideov04","sudo sh web/mountcheck.sh V04"],
["mountvideop04","sudo sh web/mountcheck.sh P04"],
["mountvideov03","sudo sh web/mountcheck.sh V03"],
#sudo fuser -km /media/videos/  #force
["umountforce","sudo fuser -km /media/videos/"],
["umountlsof","sudo lsof /media/videos"],
["umountfuser","sudo fuser -m /media/videos"],
["unmountvideo","sudo umount /media/videos"],
["mountstatus","df -h /media/videos | grep xpress"],
["setxx","set to watch sepecific server, AA for all, 99 for nonLB web, lb for LB"],
["setlist","list server info"],
["help","print help list"],
["","======================"]
]
spList = ["help","setlist","setxx"]
cmdList = []
for sublist in helpList:
    if (sublist[0] not in spList):
      cmdList.append(sublist[0])
allHosts = dict(webHosts.items())
specifyVM = "AA"

msg = 'type "storage", "storagelist", "log", "checkmailer" to check mailer interval.\n'
msg += ' "mailer" to set interval, "mlog" to check mailer log,\n'
msg += ' "setAA" to check all servers status, "set10" to check web-1..etc\n'
msg += ' any command for all server or [Enter] to leave"'
msg = bcolors.HIGHBLACK + msg + bcolors.RESET

if (len(sys.argv)>1): #command
    print str(oem) +" @"+datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    path=str(sys.argv[1]) + "\n"
    if (len(sys.argv)>2): #specific server
      specifyVM = str(sys.argv[2])
      if  (webHosts.has_key(int(specifyVM)) ):
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
    elif path.lower() == "storage\n":
      for hostname, host in allHosts.items():
        try:
          getIterationOutput(host,"du -h /media/videos/")
        except Exception as e:
            continue
        break;
    elif path.lower() == "log\n":
      for hostname, host in allHosts.items():
        try:
          runScriptOutput(host,"tail -n 20 /var/log/lighttpd/error.log")
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