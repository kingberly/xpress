# python admin/check/getStreamingStatus.py 192.168.1.132 001BFE054C64
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
        if (len(msg) > 20) :
          return "Streaming"
        else :
          return "Not Streaming"
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
if (len(sys.argv)<2) :
  print "usage: python getStreamingStatusTest.py 192.168.1.x 50\n"
elif (len(sys.argv)==2) :
  for hostname, host in strHosts.items():
    if (str(sys.argv[1])==host[0] ):
        myHosts = {host[0]:host}
  if (len(myHosts) ==0):
    vmTemp = [sys.argv[1], '22', host[2], host[3], 'vmTemp'] 
    myHosts = {vmTemp[0]:vmTemp}
  total = 0
  for hostname, host in myHosts.items():
    try:
      #no need to be 18 digit, but number  000000000000000001 18 digit
      if (str(sys.argv[2]).isdigit()) :
        maxnum = int(sys.argv[2])
        for i in range(1, maxnum+1, 1):
          if (i >= 100) :
            #status = runScriptOutput(host,"ps ax | grep [/]000000000000000"+ str(i))
            #print "000000000000000%d: %s" % (i,status)
            status = runScriptOutput(host,"ps ax | grep [C]C-000000000"+ str(i))
            if (status == "Streaming") :
                print "CC-000000000%d: %s" % (i,status)
            else :
                print "CC-000000000%d: %s" % (i,bcolors.HIGHRED + status + bcolors.RESET)          
          elif (i >= 10) : #M04CC-000000000010
            #status = runScriptOutput(host,"ps ax | grep [/]0000000000000000"+ str(i))
            #print "0000000000000000%d: %s" % (i,status)
            status = runScriptOutput(host,"ps ax | grep [C]C-0000000000"+ str(i))
            if (status == "Streaming") :
                print "CC-0000000000%d: %s" % (i,status)          
            else :
                print "CC-0000000000%d: %s" % (i,bcolors.HIGHRED + status + bcolors.RESET) 
            #for stream server simulation script
            #status = runScriptOutput(host,"ps ax | grep [/]00000000000000000"+ str(i))
            #print "00000000000000000%d: %s" % (i,status)
          else :
            #for simulation script
            status = runScriptOutput(host,"ps ax | grep [C]C-00000000000"+ str(i))
            if (status == "Streaming") :
                print "CC-00000000000%d: %s" % (i,status)
            else :
                print "CC-00000000000%d: %s" % (i,bcolors.HIGHRED + status + bcolors.RESET)
          if (status == "Streaming") :
            total = total +1
        print "total %d Streaming." % total                      
    except Exception as e:
        print str(e)     