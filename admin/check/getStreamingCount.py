# python admin/check/getStreamingStatus.py 192.168.1.132
import sys
import paramiko
from igsssh import execCommand
from igsssh import execSudoCommand
from vminfo import * 

#print 'Number of arguments:', len(sys.argv), 'arguments.'
#print 'Argument List:', str(sys.argv)

def runScriptOutput(host, cmd):
    try:
        stdin, stdout, stderr = execCommand(host, cmd)
        lines = []
        lines = stdout.read().split()
        msg =""
        skipPwd = 0 #usually index > 4, value @ 5
        for index, value in enumerate(lines):
          #only check if use execSudoCommand
          #if (host[2]=="root"):
          #    skipPwd = -1
          #elif "password" in value :
          #    skipPwd = index + 2
          #print "%d %s :%d" % (index, value,skipPwd)
          #if (skipPwd!=0) and (index > skipPwd):
              msg = value  
        print msg
    except Exception as e:
        #print str(e)  
        raise ConnectionError('Connection error')

myHosts = dict()
if (len(sys.argv)>=2) :
  for hostname, host in strHosts.items():
    if (str(sys.argv[1])==host[0] ):
        myHosts = {host[0]:host}
  if (len(myHosts) ==0):
    vmTemp = [sys.argv[1], '22', host[2], host[3], 'vmTemp'] 
    myHosts = {vmTemp[0]:vmTemp}
  for hostname, host in myHosts.items():
    try:
      if (len(sys.argv)==2) :
      #runScriptOutput(host,"ps ax | grep [C]C-0000 |wc -l")            
        runScriptOutput(host,"ps ax | grep [/u]sr/local/bin/ffmpeg | wc -l")
      elif (str(sys.argv[2])=="viewer") :
      #viewercount
        runScriptOutput(host,"netstat -n | grep 5544 | wc -l")
    except Exception as e:
        print str(e)      