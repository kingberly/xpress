#python getStreamingStatusFile.py 192.168.1.1xx cam107.txt
import sys
import paramiko
from igsssh import execCommand
from igsssh import execSudoCommand
from vminfo import *

#print 'Number of arguments:', len(sys.argv), 'arguments.'
#print 'Argument List:', str(sys.argv)

def runScriptOutput(host, cmd):
    try:
        stdin, stdout, stderr = execSudoCommand(host, cmd)
        lines = []
        lines = stdout.read().split()
        msg =""
        skipPwd = 0
        for index, value in enumerate(lines):
          if (host[2]=="root"):
              skipPwd = -1
          elif "password" in value :
              skipPwd = index + 2
          #print "%d %s :%d" % (index, value,skipPwd)
          if (skipPwd!=0) and (index > skipPwd):
                msg += value+" "
        if (len(msg) > 20) :
          return "Streaming"
        else :
          return "Not Streaming"
        #print msg
    except Exception as e:
        #print str(e)  
        raise ConnectionError('Connection error')
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

myHosts = dict()
if (len(sys.argv)==3) :
  for hostname, host in strHosts.items():
    if (str(sys.argv[1])==host[0] ):
        myHosts = {host[0]:host}
  if (len(myHosts) ==0):
    vmTemp = [sys.argv[1], '22', host[2], host[3], 'vmTemp'] 
    myHosts = {vmTemp[0]:vmTemp}
  lines = [line.strip() for line in open(sys.argv[2])]
  
  for hostname, host in myHosts.items():
    try:
      i = 0
      j = 0
      for index, value in enumerate(lines):
        if "#" in value: continue
        status = runScriptOutput(host,"ps ax | grep [C]C-"+ value)             
        if (status == "Streaming"):
          #print "%s : %s" % (value, status)  #can be remarked
          i= i+1
        else :
          print "%s : %s" % (value, bcolors.HIGHRED + status + bcolors.RESET)
          j=j+1
      print "Total %d Streaming.  %d Not Streaming" % (i,j) 
    except Exception as e:
        #print str(e)
        print " "
else :
  print "usage: python getStreamingStatusFile.py 192.168.1.1xx cam107.txt, # the line of mac to ignore"