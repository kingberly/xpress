# python admin/check/getCPUMEM.py 192.168.1.132 CPU
# python admin/check/getCPUMEM.py 192.168.1.132 MEM
import sys
import paramiko
from igsssh import execCommand
from igsssh import execSudoCommand
from vminfo import * 
import os

def pinghost ( hostip ):
  if (isValidIP(hostip)):
    response = os.system("ping -c 1 " + hostip +" > /dev/null 2>&1")
    if (response == 0) :
      return True
  return False

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
        return msg
    except Exception as e:
        #print str(e)  
        raise ConnectionError('Connection error')

myHosts = dict()
if ( isValidIP(sys.argv[1]) and (len(sys.argv)==3) ) :
  for hostname, host in genHosts.items():
    if (str(sys.argv[1])==host[0] ):
        myHosts = {host[0]:host}
  if (len(myHosts) ==0):
    vmTemp = [sys.argv[1], '22', host[2], host[3], 'vmTemp'] 
    myHosts = {vmTemp[0]:vmTemp}
  #declare used variable
  cpu = 0.0
  mem_used = 0.0
  mem_free = 0.0
  idlecpu=""
  for hostname, host in myHosts.items():
    try:
      if pinghost(host[0]) :
          if (str(sys.argv[2]) == "MEM") :
            mem_used=float(runScriptOutput(host,"free -m | grep 'buffers/cache' | awk '{print $3}'"))
            mem_free=float(runScriptOutput(host,"free -m | grep 'buffers/cache' | awk '{print $4}'"))
            print "%0.2f%%" % (mem_used/(mem_used+mem_free)*100)
          else: #elif (str(sys.argv[2]) == "CPU") :
            idlecpu=runScriptOutput(host,"top -n 1 | grep 'Cpu(s)' |awk '{print $5}'")
            #93.1%id,
            if ( "%" in idlecpu) :
              cpu = 100.0 - float(idlecpu[0:4])
            else :
              #grep 'cpu ' /proc/stat | awk '{usage=($2+$4)*100/($2+$4+$5)} END {print usage "%"}'
              #1.54675%
              #idlecpu=runScriptOutput(host,"top -n 1 | grep 'Cpu(s)' |awk '{print $8}'")
              idlecpuarray = []
              idlecpuarray=runScriptOutput(host,"top -n 2 | grep 'Cpu(s)' |awk '{print $8}'").split(" ")
              idlecpu=0.0
              for index, value in enumerate(idlecpuarray):
                idlecpu += float(value)
              if (index == 0) : 
                cpu = 100.0 - float(idlecpu)
              else :
                cpu = 100.0 - float(idlecpu)/index 
            print "%0.1f%%" % cpu
      else:
          print "NA"           
    except Exception as e:
      print "NA" #print str(e)     