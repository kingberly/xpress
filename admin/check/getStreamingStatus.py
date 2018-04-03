# python admin/check/getStreamingStatus.py 192.168.1.132 001BFE054C64
#2018/1/18
#does not detect if streaming data is available, use ls
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
        skipPwd = 0 #usually index > 4, value @ 5
        for index, value in enumerate(lines):
          if (host[2]=="root"):
              skipPwd = -1
          elif "password" in value :
              skipPwd = index + 2
          #print "%d %s :%d" % (index, value,skipPwd)
          if (skipPwd!=0) and (index > skipPwd):
                msg += value+" "
        #if (len(msg) > 20) :  #ps ax
        if ("No such file" not in msg) :  #ls
          return "Streaming"
        else :
          return "Not Streaming"
        #print msg
    except Exception as e:
        raise ConnectionError('Connection error '+str(e))

myHosts = dict()
if (len(sys.argv)==3 ):
  for hostname, host in strHosts.items():
    if (str(sys.argv[1])==host[0] ):
        myHosts = {host[0]:host}
  if (len(myHosts) ==0):
    vmTemp = [sys.argv[1], '22', host[2], host[3], 'vmTemp'] 
    myHosts = {vmTemp[0]:vmTemp}
  for hostname, host in myHosts.items():
    try:            
      #print runScriptOutput(host,"ps ax | grep [C]-"+ str(sys.argv[2]))
      print runScriptOutput(host,"ls /var/evostreamms/temp/????C-"+ str(sys.argv[2]) +"*")
    except Exception as e:
        #print str(e)
        print " "