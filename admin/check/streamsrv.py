import sys
import paramiko
from igsssh import execCommand
from igsssh import execSudoCommand
import datetime
import init
import os
#import requests #for ConnectionError, need $$ pip install requests

def writeLog(filename, content):
  fpname = "/var/tmp/" + filename
  fp = open(fpname, 'a')
  fp.write(content)
  fp.close()

def pinghost ( hostip ):
  if (isValidIP(hostip)):
    response = os.system("ping -c 1 " + hostip +" > /dev/null 2>&1")
    if (response == 0) :
      return True
  return False

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

def checkValue(host,req, path):
    try:
        checkItem = 'Check Value'
        cmd = "sed -n '/"+req+"/p' "+path 
        stdin, stdout, stderr = execSudoCommand(host, cmd)
        lines = []
        lines = stdout.read().split()
        msg = "RUN> " + cmd +"\n"
        skipPwd = 0
        for index, value in enumerate(lines):
          if (host[2]=="root"):
              skipPwd = -1
          elif "password" in value :
              skipPwd = index + 2
          #print "%d %s :%d" % (index, value,skipPwd)
          if (skipPwd!=0) and (index > skipPwd):
              msg += " " + value
        logStatus(checkItem, host[4], msg)
    except Exception as e:
        logStatus(checkItem, host[4], "Error"+ str(e))
        raise ConnectionError('Connection error throw')

def checkLastFile(host,path):
    try:
        #add your command here
        checkItem = 'get last file'
        cmd = "ls -Art "+ path +" | tail -n 1"
        stdin, stdout, stderr = execCommand(host,  cmd)
        lines = []
        lines = stdout.read().split()
        filepath = ""
        for index, value in enumerate(lines):
        #  print index
          filepath = value
        return filepath
    except Exception as e:
        logStatus(checkItem, host[4], "Error"+ str(e))
        raise ConnectionError('Connection error throw')


def checkSecondLastFile(host,path):
    try:
        #add your command here
        checkItem = 'get 2nd last file'
        cmd = "ls -Art "+ path +" | tail -n 2"
        stdin, stdout, stderr = execCommand(host,  cmd)
        lines = []
        lines = stdout.read().split()
        filepath = ""
        for index, value in enumerate(lines):
        #  print index
          filepath = value
          break;
        return filepath
    except Exception as e:
        logStatus(checkItem, host[4], "Error"+ str(e))
        raise ConnectionError('Connection error throw')
        
    
def logStatus(checkItem, hostname, msg):
    if (init.bColorTag):
        hostname = bcolors.HIGHBLUE + hostname
        checkItem = checkItem + bcolors.RESET
        print '%-10s:%-15s:%s' % (hostname, checkItem, msg)
    else:
        print '%-10s:%s' % (hostname, msg)
    if (init.bWriteLog) :
      filename = "x41_"+datetime.datetime.now().strftime("%Y%m%d")+".log"
      print "writeFile %s" % filename
      writeLog (filename,"%-10s:%-15s:%s\n" % (hostname, checkItem, msg))
#filter year 201 and : or digit
def runScriptOutput(host, cmd):
    try:
        checkItem = 'Run script Output' #prefix bgcolor
        stdin, stdout, stderr = execSudoCommand(host, cmd)
        lines = []
        lines = stdout.read().split()
        if (init.bColorTag):
          msg = "RUN> "+cmd +"\n"   #prefix msg
        else:
          msg = ""
        skipPwd = 0
        for index, value in enumerate(lines):
          if (host[2]=="root"):
              skipPwd = -1
          elif "password" in value :
              skipPwd = index + 2
          #print "%d %s :%d" % (index, value,skipPwd)
          if (skipPwd!=0) and (index > skipPwd):
              if ("201" in value) or (":" in value) or (value.isdigit()):
                msg = msg + "\n" + value
              else:
                msg += " " + value
        logStatus(checkItem, host[4], msg)
    except Exception as e:
        logStatus(checkItem, host[4], "Error"+ str(e))
        raise ConnectionError('Connection error')

#db_check use
def runScriptPrint(host, cmd, param):
    try:
        checkItem = 'Run script Print'
        stdin, stdout, stderr = execSudoCommand(host, cmd)
        lines = []
        lines = stdout.read().split()
        msg = "RUN> "+cmd +"\n"
        skipPwd = 0
        for index, value in enumerate(lines):
          if (host[2]=="root"):
              skipPwd = -1
          elif "password" in value :
              skipPwd = index + 2
          #print "%d %s :%d" % (index, value,skipPwd)
          if (skipPwd!=0) and (index > skipPwd):
            if (param in value):
                msg = msg + "\n" + value
            else:
                msg += " " + value
        logStatus(checkItem, host[4], msg)
    except Exception as e:
        logStatus(checkItem, host[4], "Error"+ str(e))
        raise ConnectionError('Connection error throw')
#for tunnel/stream cloud_check.py check fileSize
def getOneOutput(host, cmd):
    try:
        stdin, stdout, stderr = execSudoCommand(host, cmd)
        lines = []
        lines = stdout.read().split()
        msg = ""
        skipPwd = 0
        for index, value in enumerate(lines):
          if (host[2]=="root"):
              skipPwd = -1
          elif "password" in value :
              skipPwd = index + 2
          if (skipPwd!=0) and (index > skipPwd):
            msg = value
        return msg
    except Exception as e:
        print str(e)

def checkProcess(host,cmd):
    try:
        checkItem = 'Process'
        stdin, stdout, stderr = execCommand(host, cmd )
        lines = []
        lines = stdout.read().split()
        msg = ">> " + cmd
        for index, value in enumerate(lines):
            if value.isdigit(): #vlc will add one line in http port 808x
              msg = msg + "\n" + value
            else:
              msg = msg + " " + value
        logStatus(checkItem, host[4], msg)
    except Exception as e:
        logStatus(checkItem, host[4], "Error:" + str(e))
        raise ConnectionError('Connection error throw')
#getALLOutput in iteration
def getIterationOutput(host,cmd):
    try:
        checkItem = 'List All'
        stdin, stdout, stderr = execCommand(host, cmd)
        lines = []
        lines = stdout.read().split()
        msg = ">> " + cmd
        for i in lines:
          msg = msg  + "\n" + i
        logStatus(checkItem, host[4], msg)
    except Exception as e:
        logStatus(checkItem, host[4], "Error:" + str(e))
        raise ConnectionError('Connection error throw')

def checkCloudRecordingbyPath(host, folderpath):
    try:
        checkItem = 'Recording Check'
        stdin, stdout, stderr = execCommand(host, folderpath)
        lines = []
        lines = stdout.read().split()
        msg = ">> "+cmd+ "\n"
        for index, value in enumerate(lines):
          #print (index, value)
          #2, 11, 20 carriage return
          msg = msg + " " + value
          if ((index-2)%9 == 0): #??
            msg = msg  + "\n" 
        logStatus(checkItem, host[4], msg)
    except Exception as e:
        logStatus(checkItem, host[4], "Error:" + str(e))
        raise ConnectionError('Connection error throw')

def createSSHClient(server, port, user, password):
    client = paramiko.SSHClient()
    client.load_system_host_keys()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    #client.set_missing_host_key_policy(paramiko.WarningPolicy())
    client.connect(server, port, user, password)
    return client

def putFile(host,fileExt):
      #ip, port, acc, pwd, hostname
      if (not os.path.isfile(fileExt)):
        print "file %s not exist!!" % fileExt
        return
      ssh = createSSHClient(host[0], int(host[1]), host[2], host[3])
      sftp=ssh.open_sftp()
      if (host[2] == "root") :
          if "/" in fileExt:
            fileExtS = fileExt.rsplit("/",1)
            print "local file:%s => %s remote file:%s" %(fileExt, host[4] ,"/home/"+host[2]+"/"+fileExtS[1])
            sftp.put(fileExt,"/home/"+host[2]+"/"+fileExtS[1]) #local, remote
          else:
            print "local file:%s => %s remote file:%s" %(os.getcwd()+"/"+fileExt, host[4] ,"/home/"+fileExt)
            sftp.put(os.getcwd()+"/"+fileExt,"/home/"+fileExt) #local, remote
      else :
          if "/" in fileExt:
            fileExtS = fileExt.rsplit("/",1)
            print "local file:%s => %s remote file:%s" %(fileExt, host[4] ,"/home/"+host[2]+"/"+fileExtS[1])
            sftp.put(fileExt,"/home/"+host[2]+"/"+fileExtS[1]) #local, remote
          else:
            print "local file:%s => %s remote file:%s" %(os.getcwd()+"/"+fileExt, host[4] ,"/home/"+host[2]+"/"+fileExt)
            sftp.put(os.getcwd()+"/"+fileExt,"/home/"+host[2]+"/"+fileExt) #local, remote
      sftp.close()
      ssh.close()

def getFile(host,fileExt):
      ssh = createSSHClient(host[0], int(host[1]), host[2], host[3])
      sftp=ssh.open_sftp()
      print "%s remote file:%s => local file:%s" %(host[4] ,fileExt, os.getcwd()+"/"+fileExt)
      sftp.get(fileExt,os.getcwd()+"/"+fileExt)
      sftp.close()
      ssh.close()
