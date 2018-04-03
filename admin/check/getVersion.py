# python admin/check/getVersion.py 192.168.1.132 stream
# python admin/check/getVersion.py 192.168.1.141 rtmpd
import sys
import paramiko
from igsssh import execCommand
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
              msg += value  
        print msg
    except Exception as e:
        #print str(e)  
        raise ConnectionError('Connection error')

myHosts = dict()
 
if (len(sys.argv)==3) :
  for hostname, host in genHosts.items():
    if (str(sys.argv[1])==host[0] ):
        myHosts = {host[0]:host}
  if (len(myHosts) ==0):
    vmTemp = [sys.argv[1], '22', host[2], host[3], 'vmTemp'] 
    myHosts = {vmTemp[0]:vmTemp}
  for hostname, host in myHosts.items():
    try:          
      runScriptOutput(host,"cat "+sys.argv[2]+"/version")
    except Exception as e:
        print str(e)