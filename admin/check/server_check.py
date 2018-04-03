from streamsrv import *
from vminfo import * 
import sys
import paramiko
from sys import stdin
import datetime
import os
import subprocess
import platform
#
# Main function
#
def gotoWorkingDir():
    if not "admin/check" in os.getcwd():    
        os.chdir("/home/ivedasuper/admin/check/")
    elif not "check" in os.getcwd():
        os.chdir("/home/ivedasuper/admin/check/")

def runCmd(host, cmd):
    try:
        stdin, stdout, stderr = execSudoCommand(host, cmd)
        lines = []
        lines = stdout.read().split()
        msg =">"
        skipPwd = 0 #usually index > 4, value @ 5
        for index, value in enumerate(lines):
              if ("sudo" in value) or ("password" in value) or (host[2] in value) or ( host[3] in value)  :
                msg =">"
                continue
              msg += value+" \n"  
        print "%-15s)\n%s" %(host[4],msg)
    except Exception as e:
        print host[4]+ str(e)  
        #raise ConnectionError('Connection error')

helpList = [
["","======================"],
["tunnel","python tunnel_check.py"],
["rtmp","python rtmp_check.py"],
["web","python web_check.py"],
["stream","python cloudrec_check.py"],
["db","python db_check.py"],
["deploy","python deploy.py"],
["localcheck","python localcheck.py"],
["setlist","show servers list"],
["pinglist","ping"],
["setlistext","show ext admin servers list"],
["connect xnn","connect to external admin xnn"],
["disconnect","disconnect from external admin"],
["help","print help list"],
["","======================"]
]

spList = ["help","setlist","setlistext","pinglist","disconnect","connect xnn","putfile","r"]
spList2 = ["tunnel","rtmp","web","stream","db","deploy","localcheck"]
cmdList = []
for sublist in helpList:
    if (sublist[0] not in spList) or (sublist[0] not in spList2):
      cmdList.append(sublist[0])

lastCMD=""
msg = 'type "help" to see command list\n'
msg += ' [Enter] to leave'
specifyVM = "" #extAdmin feature only
allHosts = dict(genHosts.items()) 

if (len(sys.argv)>1): #command
    print str(oem) +" @"+datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    path=str(sys.argv[1]) + "\n"
    if (len(sys.argv)>2): #specific server
      specifyVM = str(sys.argv[2])
      if  (allHosts.has_key(int(specifyVM)) ):
        allHosts = {int(specifyVM):vm[int(specifyVM)] }
    #init.bColorTag = False
else:
  if (platform.system() == "Linux") :
    print bcolors.HIGHRED + str(oem) +" @"+datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S") + bcolors.RESET
    msg = bcolors.HIGHBLACK + msg + bcolors.RESET
  else:
    print str(oem) +" @"+datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    print msg
  path = stdin.readline()

while len(path) > 0:
    if path.lower() == "r\n":
      path = lastCMD
    if (path == "\n"):
        break;
    elif path.lower() == "help\n":
      for i in range(len(helpList)):
          print '%-15s: %s' % (helpList[i][0], helpList[i][1])
    elif path.lower() == "setlist\n":
      for hostname, host in allHosts.items():
        try:
          print '%-3s>%-15s: %-15s (%s) / %-15s' % (hostname,host[4], host[0],host[1], host[2])
        except Exception as e:
            continue
      if specifyVM !="" :
        print "=========== %s ===========" % specifyVM
      else:
        print "=========== %s ===========" % str(oem)
    elif path.lower() == "setlistext\n":
      for hostname, host in extAdmin.items():
        try:
          print '%-3s>%-15s: %-15s (%s) / %-15s' % (hostname,host[4], host[0],host[1], host[2])
        except Exception as e:
            continue
      print "====== External Admin ======"
    elif path.lower() == "disconnect\n":
      specifyVM = ""
      allHosts = dict(genHosts.items()) 
    elif len(path)== 12 and path[:7]=="connect" and path[11] == "\n" :
      specifyVM = path[8:11].lower()
      if  (extAdmin.has_key(specifyVM) ):
          #allHosts = dict(extAdmin.items())
          allHosts = {specifyVM : extAdmin[specifyVM] } 
          print 'set to %-15s: %-15s (%s) / %-15s' % (extAdmin[specifyVM][4], extAdmin[specifyVM][0],extAdmin[specifyVM][1], extAdmin[specifyVM][2]) 
      else:
          print "No External Admin Found."
    elif path.lower() == "pinglist\n":
      if specifyVM !="" :
        print "pinglist not support for extAdmin"
      else:
        print "=========== %s ===========" % str(oem)
        for hostname, host in allHosts.items():
          try:
            if pinghost (host[0]):
                status = "Avail"
            else:
                status = "NA!!"
            print '(%-5s)%-3s>%-15s: %-15s (%s) / %-15s' % (status,hostname,host[4], host[0],host[1], host[2])
          except Exception as e:
              continue
          print "=========== %s ===========" % str(oem)
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
    elif path.lower().strip("\n") in spList2:
      for sublist in helpList:
          if sublist[0] == path.lower().strip("\n") :
              inputcmd = sublist[1]
      if (platform.system() == "Linux") :
        gotoWorkingDir()
        #print os.getcwd() #print os.path.dirname(os.path.abspath(__file__))
      os.system(inputcmd)  
    elif path.lower().strip("\n") in cmdList:
      for sublist in helpList:
          if sublist[0] == path.lower().strip("\n") :
              inputcmd = sublist[1]
      for hostname, host in allHosts.items():
        try:
            runCmd (host, inputcmd)
        except Exception as e:
            print host[4] + str(e)
            continue
    else:
        for hostname, host in allHosts.items():
          try:
              runCmd(host,path)    
          except Exception as e:
              continue
        if specifyVM !="" :
          print "========%s cmd: %s ===========" % ( specifyVM, path.strip("\n"))
        else:
          print "========%s cmd: %s ===========" % (oem, path.strip("\n") )
    lastCMD = path
    if (len(sys.argv)>1): #one time command only
      break
    print bcolors.HIGHRED + str(oem) +" @"+datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S") + bcolors.RESET
    print msg
    path = stdin.readline()    