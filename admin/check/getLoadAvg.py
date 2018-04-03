# python admin/check/getLoadAvg.py 192.168.1.132  empty(2)/3/2/1
import sys
import paramiko
from igsssh import execCommand
from igsssh import execSudoCommand
from vminfo import * 

#print 'Number of arguments:', len(sys.argv), 'arguments.'
#print 'Argument List:', str(sys.argv)

def isValidIP(s):
    a = s.split('.')
    if len(a) != 4:
        return False
    for x in a:
        if not x.isdigit():
            return False
        i = int(x)
        if i < 0 or i > 255:
            return False
    return True

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
                msg = value
        print msg
    except Exception as e:
        #print str(e)  
        raise ConnectionError('Connection error')

myHosts = dict()
if ( isValidIP(sys.argv[1]) and (len(sys.argv)>2) ) :
  for hostname, host in genHosts.items():
    if (str(sys.argv[1])==host[0] ):
        myHosts = {host[0]:host}
  if (len(myHosts) ==0):
    vmTemp = [sys.argv[1], '22', host[2], host[3], 'vmTemp'] 
    myHosts = {vmTemp[0]:vmTemp}

  for hostname, host in myHosts.items():
    try:
      if (len(sys.argv)==2) : #5min            
        runScriptOutput(host,"cat /proc/loadavg | awk '{print $2}'")
      elif (len(sys.argv)==3) and (sys.argv[2]=="3"): #15min
        runScriptOutput(host,"cat /proc/loadavg | awk '{print $3}'")
      elif (len(sys.argv)==3) and (sys.argv[2]=="2"): #5min
        runScriptOutput(host,"cat /proc/loadavg | awk '{print $2}'")
      elif (len(sys.argv)==3) and (sys.argv[2]=="1"): #1min
        runScriptOutput(host,"cat /proc/loadavg | awk '{print $1}'")
    except Exception as e:
        print str(e)