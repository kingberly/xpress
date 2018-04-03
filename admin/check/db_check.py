import sys
import paramiko
from igsssh import execCommand
from igsssh import execSudoCommand
from streamsrv import bcolors
from streamsrv import runScriptPrint
from streamsrv import runScriptOutput
from vminfo import * 
from sys import stdin
import datetime

def sqlCmd(host, cmd):
  print  bcolors.HIGHBLUE + host[4] + ": " +cmd + bcolors.RESET 
  try:
    msg1 = ""
    skipPwd = 0 #usually index > 4, value @ 5
    stdin, stdout, stderr = execSudoCommand(host, cmd)
    lines = []
    lines = stdout.read().split()
    for index, value in enumerate(lines):
      if (host[2]=="root"):
          skipPwd = -1
      elif "password" in value :
          skipPwd = index + 2
      #print "%d %s :%d" % (index, value,skipPwd)
      if (skipPwd!=0) and (index > skipPwd):
          value = value.replace("-","")
          if (value.find("|")!=-1):
            msg1 += "\n"  +value
          else:
            msg1 += " " + value
    print msg1
  except Exception as e:
    print str(e)

#
# Main function
#
helpList = [
["","======================"],
["setxx","set to watch sepecific server"],
["setlist","list server info"],
["showlist","show all server info"],
["checkslave","sh db/dbmm.sh"],
["dblog","tail -n 20 /var/log/mysql/error.log"],
["qlync status","ls -ltr /var/lib/mysql/qlync -"],
["isat status","ls -ltr /var/lib/mysql/isat -"],
["db","show databases;"],
["table","show tables;"],
["connection","SHOW GLOBAL STATUS;"],
["currentconn","SHOW STATUS;"],
["conn","show status like '%onn%';"],
["","show processlist;"],
["","mysql -u isatRoot -pisatPassword"],
["","mysql -u root -pivedaMysqlRoot"],
["","SHOW VARIABLES LIKE '%version%';"],
["reporton","set report mode on, will write log to /var/tmp/x41_<date>.log"],
["reportoff","set report mode off"],
["help","print help list"],
["","======================"]
]
cmdList = ["dblog","qlync status","isat status"]
allHosts = dict(dbHosts.items())
specifyVM = "AA"

msg = 'type "dblog", "qlync status" for qlync data file status, "isat status" for isat data file status,\n"show processlist;" or "currentconn","connection"\n"db", "table", "use <dbname>", any sql command (end in ;) to db server or [Enter] to leave"'
msg = bcolors.HIGHBLACK + msg + bcolors.RESET
dbname = "isat"
if (len(sys.argv)>1): #usage: mypy cmd num
    print str(oem) +" @"+datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    path=str(sys.argv[1]) + "\n"
    if (len(sys.argv)>2): #specific server
      specifyVM = str(sys.argv[2])
      if  (allHosts.has_key(int(specifyVM)) ):
        allHosts = {int(specifyVM):vm[int(specifyVM)] }
else:
  print bcolors.HIGHRED + str(oem) +" @"+datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S") + bcolors.RESET
  print msg
  path = stdin.readline()
while len(path) > 0:
    if (path == "\n"):
            break;
    elif path.lower() == "showlist\n":
      for hostname, host in genHosts.items():
        try:
          print '%-3s>%-15s: %-15s (%s) / %-15s' % (hostname,host[4], host[0],host[1], host[2])
        except Exception as e:
            continue
    elif len(path)== 6 and path[:3]=="set" and path[5] == "\n" :
      specifyVM = path[3:5]
      if specifyVM == "AA":
        allHosts = dict(tunHosts.items())
        print "query all servers"
      else:
         if not (dbHosts.has_key(int(specifyVM)) ): 
            print "No such server, set to query all"
            allHosts = dict(dbHosts.items())
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
    elif path.lower() == "db\n":
      cmd="mysql -u isatRoot -pisatPassword -e 'show databases;'"
      #cmd="mysql -u isatPldtDB -pisatPasswordDB -e 'show tables;' " + dbname
      for hostname, host in allHosts.items():
          try:
              sqlCmd(host,cmd)
          except Exception as e:
              continue
    elif path.lower() == "connection\n":
      cmd="mysql -u isatRoot -pisatPassword -e 'SHOW GLOBAL STATUS LIKE \"%connection%\";'"
      for hostname, host in allHosts.items():
          try:
              sqlCmd(host,cmd)
          except Exception as e:
              continue
    elif path.lower() == "currentconn\n":
      cmd="mysql -u isatRoot -pisatPassword -e 'SHOW STATUS WHERE `variable_name` = \"Threads_connected\";'"
      for hostname, host in allHosts.items():
          try:
              sqlCmd(host,cmd)
          except Exception as e:
              continue
    elif path.lower() == "table\n":
      cmd="mysql -u isatRoot -pisatPassword -e 'show tables;' " + dbname
      #cmd="mysql -u isatPldtDB -pisatPasswordDB -e 'show tables;' " + dbname
      for hostname, host in allHosts.items():
          try:
              sqlCmd(host,cmd)
          except Exception as e:
              continue
    elif (path.lower().find("use ") != -1):
      path = path.replace("\n","")
      path = path.replace(";","")
      path = path.replace("use ","")
      path = path.replace(" ","")
      dbname = path
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
      path = path.replace("\n","")
      if (path.find(";")==-1):
        path = path + ";" 
      cmd="mysql -u isatRoot -pisatPassword -e \""+ path + "\" " + dbname
      #cmd="mysql -u isatPldtDB -pisatPasswordDB -e '"+ path + "' " + dbname
      for hostname, host in allHosts.items():
          try:
              sqlCmd(host,cmd)
          except Exception as e:
              continue
    if (len(sys.argv)>1): #one time command only
      break
    print bcolors.HIGHRED + str(oem) +" @"+datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S") + bcolors.RESET
    print msg
    path = stdin.readline()    