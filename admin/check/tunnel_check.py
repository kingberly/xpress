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
["log","tail /var/log/tunnel_server/tunnel_server.log.xxxxxxxxxx"],
["logg","tail /var/log/tunnel_server/tunnel_server_guard.logxxx"],
["<MAC>","grep '<MAC>' /var/log/tunnel_server/tunnel_server.log.xxxxxxxxxx"],
["<MAC> status","grep -r '<MAC>' /var/log/tunnel_server/"],
["putfile","sftp.put $local/file $remote/home/$USER/file"],
["putfilem","upload multiple files from argument file1/file2/file3"],
["getfile","sftp.get $remote/file $local/file"],
["connlog","grep 'TunnelLink' /var/log/tunnel_server/tunnel_server.log.xxxxxxxxxx"],
["testlog","put 'connlog' command in logfile and 'getfile'"],
["restart","/etc/init.d/tunnel_server restart"],
["myip","nslookup myip.opendns.com 208.67.222.222"],
["myip2","dig +short myip.opendns.com @resolver1.opendns.com"],
["checksrv","tail /var/tmp/tunservice.log"],
["reporton","set report mode on, will write log to /var/tmp/x41_<date>.log"],
["reportoff","set report mode off"],
["help","print help list"],
["","======================"]
]

#spList = ["setlist","putfile","getfile","test","help","testlog","testlog2","log","logg","connlog"]
cmdList = ["myip","restart","checksrv"]
#cmdList = []
#for sublist in helpList:
#    if (sublist[0] not in spList2) and (sublist[0] not in spList):
#      cmdList.append(sublist[0])   

allHosts = dict(tunHosts.items())
specifyVM = "AA"

msg = 'type "<MAC>" for camera latest status,"<MAC> status" for full camera status,\n'
msg += ' "connlog" for camera connection log, "log" for currnet tunnel log,\n'
msg += ' "setAA" to check all servers status, "set20" to check tunnel-1..etc\n'
msg += ' any command for all tunnel server or [Enter] to leave'
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
    elif len(path)== 6 and path[:3]=="set" and path[5] == "\n" :
      specifyVM = path[3:5]
      if specifyVM == "AA":
        allHosts = dict(tunHosts.items())
        print "query all servers"
      else:
         if not (tunHosts.has_key(int(specifyVM)) ): 
            print "No such server, set to query all"
            allHosts = dict(tunHosts.items())
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
    elif path.lower() == "logg\n":
      for hostname, host in allHosts.items():
          try:
            filepath = "/var/log/tunnel_server/tunnel_server_guard.log"
            if (filepath != ""):
              runScriptOutput(host,"tail -n 20 "+filepath)
          except Exception as e:
              continue
    elif path.lower() == "log\n":
      for hostname, host in allHosts.items():
          try:
            filepath = checkLastFile(host,"/var/log/tunnel_server/tunnel_server.log*")
            if (filepath != ""):
              runScriptOutput(host,"tail -n 20 "+filepath)
          except Exception as e:
              continue
    elif len(path)== 13 and path.find(" ")==-1:
      for hostname, host in allHosts.items():    
          try:
            filepath = checkLastFile(host,"/var/log/tunnel_server/tunnel_server.log*")
            if (filepath != ""):
              runScriptOutput (host, "grep '" + path[:12] + "' "+filepath)
          except Exception as e:
              continue
    elif len(path)== 20 and path[:11].find(" ")==-1 and path[13:] == "status\n":
      for hostname, host in allHosts.items():
          try:
            runScriptOutput (host, "grep -r '" + path[:12] + "' /var/log/tunnel_server/")
          except Exception as e:
              continue
    elif path.lower() == "connlog\n":
      for hostname, host in allHosts.items():
          try:
            filepath = checkLastFile(host,"/var/log/tunnel_server/tunnel_server.log*")
            if (filepath != ""):
              runScriptOutput(host,"grep 'TunnelLink' "+filepath)
          except Exception as e:
              continue
    elif path.lower() == "testlog\n":
      for hostname, host in allHosts.items():
          try:
              runScriptOutput(host,"rm -rf tunnel_"+host[0]+"_testlog_*")
              fileExt = "tunnel_"+host[0]+"_testlog_" + datetime.datetime.now().strftime("%Y%m%d%H%M%S")
              runScriptOutput(host,"uptime > "+fileExt)
              runScriptOutput(host,"grep 'TunnelLink' /var/log/tunnel_server/"+checkLastFile(host,"/var/log/tunnel_server/tunnel_server.log*")+" >> "+fileExt)
              fileSize = getOneOutput(host,"du "+fileExt+" | awk '{print $1}'")
              if (int(fileSize) < 50):
                  runScriptOutput(host,"echo '==============' >>"+fileExt)
                  runScriptOutput(host,"grep 'TunnelLink' /var/log/tunnel_server/"+checkSecondLastFile(host,"/var/log/tunnel_server/tunnel_server.log*")+" >> "+fileExt)          
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
    elif path.lower() == "testlog2\n":
      for hostname, host in allHosts.items():
        try:
            runScriptOutput(host,"rm -rf tunnel_"+host[0]+"_testlog_*")
            fileExt = "tunnel_"+host[0]+"_testlog_" + datetime.datetime.now().strftime("%Y%m%d%H%M%S")
            runScriptOutput(host,"uptime > "+fileExt)
            runScriptOutput(host,"grep 'TunnelLink' /var/log/tunnel_server/"+checkSecondLastFile(host,"/var/log/tunnel_server/")+" >> "+fileExt)
            ssh = createSSHClient(host[0], int(host[1]), host[2], host[3])
            #scp = SCPClient(ssh.get_transport())
            sftp=ssh.open_sftp()
            print "remote file:%s => local file:%s" %(fileExt, os.getcwd()+"/"+fileExt)
            sftp.get(fileExt,os.getcwd()+"/"+fileExt)
            sftp.close()
            ssh.close()
        except Exception as e:
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