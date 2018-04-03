#!/usr/bin/python
#-*-coding:utf-8 -*-
import sys
import paramiko
from igsssh import execCommand
from igsssh import execSudoCommand
from sys import stdin
import datetime
import os
import subprocess
import platform
#for get_ip_address
import socket
if os.path.isfile("vminfo.py"):
  from vminfo import *

if (platform.system() == "Linux") :
  import fcntl  #linux only LAN IP
import struct

# Main function
# detect subip range x.x.x
def get_nic():
  nicArr=["eth0","eth1","em1"]
  response=""
  for i in range(len(nicArr)):
    try:
      response = subprocess.check_output("ifconfig | grep "+nicArr[i], shell=True, stderr=subprocess.STDOUT)
      #print " res=%s " %response
    except Exception as e:
        #print "grep " +nicArr[i] +" error:" + str(e)
        continue 
    if (response != "") :
      return nicArr[i]
      break
  return ""

def get_ip_address(ifname):
    s = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
    return socket.inet_ntoa(fcntl.ioctl(
        s.fileno(),
        0x8915,  # SIOCGIFADDR
        struct.pack('256s', ifname[:15])
    )[20:24])
if (platform.system() == "Linux") :
  nic = get_nic()
  if (nic != ""):
    IP=str(get_ip_address(nic))
    MYIP=IP.split(".")[0]+"."+IP.split(".")[1]+"."+IP.split(".")[2]+"."
  else:
      MYIP = "192.168.1."
else :
  IP=socket.gethostbyname(socket.gethostname())
  if ( IP[0:IP.find(".")] != "127" ):
      MYIP = IP[0:IP.rfind(".")] + "." 
  else:
      MYIP = "192.168.1."

vmBBList=[
  [MYIP+"142","22","ivedasuper","1qazxdr56yhN","DB-142"],
  [MYIP+'141', '22', 'ivedasuper', '1qazxdr56yhN', 'DB-140'],
  [MYIP+'115', '22', 'ivedasuper', '1qazxdr56yhN', 'Web-115'],
  [MYIP+'116', '22', 'ivedasuper', '1qazxdr56yhN', 'Web-116'],
  [MYIP+'117', '22', 'ivedasuper', '1qazxdr56yhN', 'Web-117'],
  [MYIP+'118', '22', 'ivedasuper', '1qazxdr56yhN', 'Web-118'],
  [MYIP+'170', '22', 'ivedasuper', '1qazxdr56yhN', 'Tunnel-170'],
  [MYIP+'171', '22', 'ivedasuper', '1qazxdr56yhN', 'Tunnel-171'],
  [MYIP+'172', '22', 'ivedasuper', '1qazxdr56yhN', 'Tunnel-172'],
  [MYIP+'173', '22', 'ivedasuper', '1qazxdr56yhN', 'Tunnel-173'],
  [MYIP+'174', '22', 'ivedasuper', '1qazxdr56yhN', 'Tunnel-174'],
  [MYIP+'175', '22', 'ivedasuper', '1qazxdr56yhN', 'Tunnel-175'],
  [MYIP+'176', '22', 'ivedasuper', '1qazxdr56yhN', 'Tunnel-176'],
  [MYIP+'177', '22', 'ivedasuper', '1qazxdr56yhN', 'Tunnel-177'],
  [MYIP+'178', '22', 'ivedasuper', '1qazxdr56yhN', 'Tunnel-178'],
  [MYIP+'179', '22', 'ivedasuper', '1qazxdr56yhN', 'Tunnel-179'],
  [MYIP+'180', '22', 'ivedasuper', '1qazxdr56yhN', 'Stream-180'],
  [MYIP+'181', '22', 'ivedasuper', '1qazxdr56yhN', 'Stream-181'],
  [MYIP+'182', '22', 'ivedasuper', '1qazxdr56yhN', 'Stream-182'],
  [MYIP+'183', '22', 'ivedasuper', '1qazxdr56yhN', 'Stream-183'],
  [MYIP+'184', '22', 'ivedasuper', '1qazxdr56yhN', 'Stream-184'],
  [MYIP+'185', '22', 'ivedasuper', '1qazxdr56yhN', 'Stream-185'],
  [MYIP+'186', '22', 'ivedasuper', '1qazxdr56yhN', 'Stream-186'],
  [MYIP+'187', '22', 'ivedasuper', '1qazxdr56yhN', 'Stream-187'],
  [MYIP+'188', '22', 'ivedasuper', '1qazxdr56yhN', 'Stream-188'],
  [MYIP+"152","22","ivedasuper","1qazxdr56yhN","Rtmp-152"],
  [MYIP+'189', '22', 'ivedasuper', '1qazxdr56yhN', 'Stream-189']
#above VM is not ready
]
vmAAList=[
[MYIP+"111","22","ivedasuper","1qazxdr56yhN","Web-111"],
[MYIP+"112","22","ivedasuper","1qazxdr56yhN","Web-112"],
[MYIP+"113","22","ivedasuper","1qazxdr56yhN","Web-113"],
[MYIP+"114","22","ivedasuper","1qazxdr56yhN","Web-114"],
[MYIP+"121","22","ivedasuper","1qazxdr56yhN","Tunnel-121"],
[MYIP+"122","22","ivedasuper","1qazxdr56yhN","Tunnel-122"],
[MYIP+"123","22","ivedasuper","1qazxdr56yhN","Tunnel-123"],
[MYIP+"124","22","ivedasuper","1qazxdr56yhN","Tunnel-124"],
[MYIP+"125","22","ivedasuper","1qazxdr56yhN","Tunnel-125"],
[MYIP+"126","22","ivedasuper","1qazxdr56yhN","Tunnel-126"],
[MYIP+"127","22","ivedasuper","1qazxdr56yhN","Tunnel-127"],
[MYIP+"128","22","ivedasuper","1qazxdr56yhN","Tunnel-128"],
[MYIP+"151","22","ivedasuper","1qazxdr56yhN","Rtmp-151"],
[MYIP+"131","22","ivedasuper","1qazxdr56yhN","Stream-131"],
[MYIP+"132","22","ivedasuper","1qazxdr56yhN","Stream-132"],
[MYIP+"133","22","ivedasuper","1qazxdr56yhN","Stream-133"],
[MYIP+"134","22","ivedasuper","1qazxdr56yhN","Stream-134"],
[MYIP+"135","22","ivedasuper","1qazxdr56yhN","Stream-135"],
[MYIP+"136","22","ivedasuper","1qazxdr56yhN","Stream-136"],
[MYIP+"137","22","ivedasuper","1qazxdr56yhN","Stream-137"],
[MYIP+"138","22","ivedasuper","1qazxdr56yhN","Stream-138"],
[MYIP+"140","22","ivedasuper","1qazxdr56yhN","DB-140"],
[MYIP+"101","22","ivedasuper","1qazxdr56yhN","LB-101"],
[MYIP+"102","22","ivedasuper","1qazxdr56yhN","LB-102"],
[MYIP+"201","22","ivedasuper","1qazxdr56yhN","Admin-201"],
[MYIP+"202","22","ivedasuper","1qazxdr56yhN","Admin-202"]
]

vmPLDT=[
[MYIP+"103","22","root","P@ssword12","Web-103"],
[MYIP+"104","22","root","P@ssword12","Web-104"],
[MYIP+"105","22","root","P@ssword12","Web-105"],
[MYIP+"106","22","root","P@ssword12","Web-106"],
[MYIP+"107","22","root","P@ssword12","Web-107"],
[MYIP+"108","22","root","P@ssword12","Web-108"],
[MYIP+"109","22","root","P@ssword12","Web-109"],
[MYIP+"110","22","root","P@ssword12","Web-110"],
[MYIP+"112","22","root","P@ssword12","Web-112"],
[MYIP+"113","22","root","P@ssword12","Web-113"],
[MYIP+"114","22","root","P@ssword12","Web-114"],
[MYIP+"115","22","root","P@ssword12","Web-115"],
[MYIP+"116","22","root","P@ssword12","Web-116"],
[MYIP+"117","22","root","P@ssword12","Web-117"],
[MYIP+"118","22","root","P@ssword12","Web-118"],
[MYIP+"119","22","root","P@ssword12","Web-119"],
[MYIP+"203","22","root","P@ssword12","Web-203"],
[MYIP+"204","22","root","P@ssword12","Web-204"],
[MYIP+"205","22","root","P@ssword12","Web-205"],
[MYIP+"206","22","root","P@ssword12","Web-206"],
[MYIP+"207","22","root","P@ssword12","Web-207"],
[MYIP+"208","22","root","P@ssword12","Web-208"],
[MYIP+"209","22","root","P@ssword12","Web-209"],
[MYIP+"120","22","root","P@ssword12","Tunnel-120"],
[MYIP+"121","22","root","P@ssword12","Tunnel-121"],
[MYIP+"122","22","root","P@ssword12","Tunnel-122"],
[MYIP+"123","22","root","P@ssword12","Tunnel-123"],
[MYIP+"124","22","root","P@ssword12","Tunnel-124"],
[MYIP+"125","22","root","P@ssword12","Tunnel-125"],
[MYIP+"126","22","root","P@ssword12","Tunnel-126"],
[MYIP+"127","22","root","P@ssword12","Tunnel-127"],
[MYIP+"128","22","root","P@ssword12","Tunnel-128"],
[MYIP+"129","22","root","P@ssword12","Tunnel-129"],
[MYIP+"210","22","root","P@ssword12","Tunnel-210"],
[MYIP+"211","22","root","P@ssword12","Tunnel-211"],
[MYIP+"212","22","root","P@ssword12","Tunnel-212"],
[MYIP+"213","22","root","P@ssword12","Tunnel-213"],
[MYIP+"214","22","root","P@ssword12","Tunnel-214"],
[MYIP+"215","22","root","P@ssword12","Tunnel-215"],
[MYIP+"216","22","root","P@ssword12","Tunnel-216"],
[MYIP+"217","22","root","P@ssword12","Tunnel-217"],
[MYIP+"218","22","root","P@ssword12","Tunnel-218"],
[MYIP+"219","22","root","P@ssword12","Tunnel-219"],
[MYIP+"220","22","root","P@ssword12","Tunnel-220"],
[MYIP+"221","22","root","P@ssword12","Tunnel-221"],
[MYIP+"130","22","root","P@ssword12","Stream-130"],
[MYIP+"131","22","root","P@ssword12","Stream-131"],
[MYIP+"135","22","root","P@ssword12","Stream-135"],
[MYIP+"136","22","root","P@ssword12","Stream-136"],
[MYIP+"137","22","root","P@ssword12","Stream-137"],
[MYIP+"138","22","root","P@ssword12","Stream-138"],
[MYIP+"139","22","root","P@ssword12","Stream-139"],
[MYIP+"150","22","root","P@ssword12","Stream-150"],
[MYIP+"151","22","root","P@ssword12","Stream-151"],
[MYIP+"152","22","root","P@ssword12","Stream-152"],
[MYIP+"153","22","root","P@ssword12","Stream-153"],
[MYIP+"154","22","root","P@ssword12","Stream-154"],
[MYIP+"155","22","root","P@ssword12","Stream-155"],
[MYIP+"156","22","root","P@ssword12","Stream-156"],
[MYIP+"157","22","root","P@ssword12","Stream-157"],
[MYIP+"158","22","root","P@ssword12","Stream-158"],
[MYIP+"159","22","root","P@ssword12","Stream-159"],
[MYIP+"160","22","root","P@ssword12","Stream-160"],
[MYIP+"161","22","root","P@ssword12","Stream-161"],
[MYIP+"162","22","root","P@ssword12","Stream-162"],
[MYIP+"163","22","root","P@ssword12","Stream-163"],
[MYIP+"164","22","root","P@ssword12","Stream-164"],
[MYIP+"165","22","root","P@ssword12","Stream-165"],
[MYIP+"166","22","root","P@ssword12","Stream-166"],
[MYIP+"167","22","root","P@ssword12","Stream-167"],
[MYIP+"168","22","root","P@ssword12","Stream-168"],
[MYIP+"169","22","root","P@ssword12","Stream-169"],
[MYIP+"170","22","root","P@ssword12","Stream-170"],
[MYIP+"171","22","root","P@ssword12","Stream-171"],
[MYIP+"172","22","root","P@ssword12","Stream-172"],
[MYIP+"173","22","root","P@ssword12","Stream-173"],
[MYIP+"174","22","root","P@ssword12","Stream-174"],
[MYIP+"175","22","root","P@ssword12","Stream-175"],
[MYIP+"141","22","root","P@ssword12","DB-141"],
[MYIP+"142","22","root","P@ssword12","DB-142"],
[MYIP+"101","22","root","P@ssword12","LB-101"],
[MYIP+"102","22","root","P@ssword12","LB-102"],
[MYIP+"201","22","root","P@ssword12","Admin-201"],
[MYIP+"202","22","root","P@ssword12","Admin-202"]
]

backupCMD =""
#init for vminfo
if os.path.isfile("vminfo.py"):
  vmAAList = vm
  print "=======using oem=%s" %oem 

helpList = [
["","======================"],
["putfile","sftp.put $local/file $remote/home/$USER/file"],
["putfile1","sftp.put $local/changehost.sh $remote/home/$USER/"],
["putfile3","put 3 files to /home"],
["hostname","hostname"],
["blkid","blkid"],
["uuid","cat /etc/fstab | grep '/               ext4' | awk '{print $1}'"],
["chost","sh /home/changehost.sh name"],
["","sed -i -e '/192.168.2.99/d'  /etc/hosts"],
["cuuid","sh /home/changeuuid.sh"],
["date","date"],
["ict","sh /home/timezone.sh Asia/Ho_Chi_Minh"],
["ntpstatus","/etc/init.d/ntp status"],
["uptime","uptime"],
["offeth0","sudo ifdown eth0"],
["addi","sh /home/addiveda.sh"],
["setXX","set xx to put command to specific server, AA for AA list, BB for BB list"],
["setXXX","set xxx to put command to sublist server, web/str/tun/adm/rtm for sublist"],
["setPLDT","set PLDT list"],
["setlist","list server info"],
["setscan","arp -e"], #sudo nmap -O 192.168.1.14
["pingme","ping %s -c 1"],
["pinglist","ping all server list"],
["pingw","ping -c 1 www.google.com"],
["core","cat /proc/cpuinfo | grep processor | wc -l"],
["mem","grep MemTotal /proc/meminfo"],
["disk","lsblk | grep disk"],
["diskusage","df -h / | grep / | awk '{print $5}'"],
["nass","df -h | grep /var/evostreamms"],
["nasw","df -h | grep /media/videos"],
["syslog","tail /var/log/syslog"],
["kmsg","grep kmsg /var/log/syslog"],    #check restart time for 12.04
["myip","nslookup myip.opendns.com 208.67.222.222"],
["myip2","dig +short myip.opendns.com @resolver1.opendns.com"],
["readonly","grep ' ro ' /proc/mounts"],
["","fsck -Af -M"], #sudo check disk access permission
["","tune2fs -c 1 /dev/sda1"], #sudo force an fsck on the filesystem at next reboot
["","tune2fs -i 0 /dev/sda1"], #set check interval to 0 to stop checking
["allpoweroff","powerdown in request sequence"],    #setup a powerdown sequence
["","sudo poweroff"],
["r","run (non-special) last command"],
["uname","uname -ar"],
["","uname -a | grep x86_64"],
["","uname -r"],
["","sudo ifconfig eth0 down"],
#sudo sed -i -e '/^auto eth0/, /dns-search/ s|^|#|' /etc/network/interfaces
#sudo sed -i -e '$a\\tgateway 192.168.2.254' /etc/network/interfaces;sudo sed -i -e '$a\\tdns-nameservers 8.8.8.8' /etc/network/interfaces
["","df -h | grep videos"],
["","df -h | grep evostreamms"],
["","mysql -u isatRoot -pisatPassword"],
["","mysql -u root -pivedaMysqlRoot"],
["","apt-get -y --force-yes dist-upgrade"],     #ldd security
["","apt-get -y --force-yes upgrade bash"], #check bash security
["","env x='() { :;}; echo vulnerable'  bash -c \"echo this is a test\""], #check usn-2485-1 seuciryt
["","grep -rnw /var/log/tunnel_server/ -e <MAC> | tail -20"],
["","dpkg -l openssl"],  #openssl freak security
["","dpkg -l  | grep 'grub2'"],
["","rm -rf /var/lib/apt/lists/*"],
["","cat /etc/lsb-release | grep DISTRIB_DESCRIPTION"],
["","unattended-upgrades"],
["","dd if=/dev/zero of=/var/evostreamms/temp/dd-test bs=1M count=2000"], #dd test2G
["","ifconfig | grep 'eth0:0'"],
["","note:poweroff,shutdown -h now"],
["help","print help list"],
["","======================"]
]

def pinghost ( hostip ):
  if (isValidIP(hostip)):
    if (platform.system() == "Linux") :
        response = os.system("ping -c 1 " + hostip +" > /dev/null 2>&1")
        #cmd = "ping %s -c %d" % (vmList[i][0],1)
        if (response == 0) :
          return True
    else :
        response = subprocess.call("ping -n 1 %s" % hostip, shell=True, stdout=open('ping.temp','w'), stderr=subprocess.STDOUT)
        if response == 0:
            print "======================= %s" %hostip
            with open('ping.temp','r') as content_file:
              print content_file.read()
              #not working, cannot detect ping OK or fail
              if '主機無法連線' in content_file.decode('utf-8'):
                return False
              elif '要求等候逾時' in content_file.decode('utf-8'):
                return False
              else:
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

spList = ["putfile","putfile1","putfile3","chost","pingme","setxx","setlist","help","addi","cuuid","pinglist","allpoweroff","r"]
#rest of not mentioned will be automatically in the cmdList
cmdList = []
for sublist in helpList:
    if (sublist[0] not in spList):
      cmdList.append(sublist[0]) 

def runScriptOutput(host, cmd):
    try:
        stdin, stdout, stderr = execSudoCommand(host, cmd)
        lines = []
        lines = stdout.read().split()
        msg =">"
        skipPwd = 0 #usually index > 4, value @ 5
        for index, value in enumerate(lines):
              msg += value+" "  
        print "%-15s) %s" %(host[4],msg)
    except Exception as e:
        print host[4]+ str(e)  
        #raise ConnectionError('Connection error')
def createSSHClient(server, port, user, password):
    client = paramiko.SSHClient()
    client.load_system_host_keys()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    #client.set_missing_host_key_policy(paramiko.WarningPolicy())
    client.connect(server, port, user, password)
    return client

def putFile(host,fileExt):
      #ip, port, acc, pwd, hostname, if acc is root, placed under /home/
      if (not os.path.isfile(fileExt)):
        print "file %s not exist!!" % fileExt
        return
      ssh = createSSHClient(host[0], int(host[1]), host[2], host[3])
      sftp=ssh.open_sftp()
      if (host[2] == "root") : #if its root, automatically place under /home
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

specifyVM = "AA" #VM list
vmList = vmAAList
msg = 'type help to get quick command list or [Enter] to leave'
print msg
path = stdin.readline()
while len(path) > 0:
    if (path == "\n"):
            break;
    elif path.lower() == "putfile\n":
          print "input filename to put:"
          fileExt = stdin.readline()
          if (fileExt == "\n") :
             print "skip, no filename"
          else :
              fileExt = fileExt.strip("\r\n")
              if (not os.path.isfile(fileExt)):
                fileExt = sys.path[0]+"/"+fileExt
              for i in range(len(vmList)):
                try:
                  putFile(vmList[i],fileExt)
                except Exception as e:
                    print vmList[i][4] + str(e)
                    continue
    elif path.lower() == "putfile1\n":
          fileExt = "changehost.sh\n"
          if (not os.path.isfile(fileExt)):
            fileExt = sys.path[0]+"/"+fileExt
          if (fileExt == "\n") :
             print "skip, no filename"
          else :
              fileExt = fileExt.strip("\r\n")
              for i in range(len(vmList)):
                try:
                  putFile(vmList[i],fileExt)
                except Exception as e:
                    print vmList[i][4] + str(e)
                    continue
    elif path.lower() == "setlist\n":
          print 'current List is %s' % specifyVM
          for i in range(len(vmList)):
              print '%-2d) %-15s: %-15s (%s) / %-15s' % (i,vmList[i][4], vmList[i][0],vmList[i][1], vmList[i][2])
    elif len(path)== 8 and path[:3]=="set" and path[7] == "\n" :
      #must be placed after setlist
        if (path[3:7].lower() =="pldt"):
          vmList = vmPLDT
          specifyVM = path[3:7]
          print "set to query (%s) vmList" % specifyVM
    elif len(path)== 7 and path[:3]=="set" and path[6] == "\n" :
        #web / tun /str/ adm /rtm  #print_matrix(subarray)
        tmpList = []
        if ( (path[3:6].lower() =="web") or (path[3:6].lower() =="tun") \
           or (path[3:6].lower() =="rtm") \
           or (path[3:6].lower() =="str") or (path[3:6].lower() =="adm") ):
          for i in range(len(vmList)):
            if ( path[3:6].lower() in vmList[i][4].lower()) : 
              tmpList.append(vmList[i])
          print "set to query sublist (%s) from (%s) vmList" % (path[3:6], specifyVM)
          vmList = tmpList
          specifyVM = path[3:6].lower()
        else :
          print "SubList not supported"
    elif len(path)== 6 and path[:3]=="set" and path[5] == "\n" :
        if (path[3:5] =="AA"):
          vmList = vmAAList
          specifyVM = path[3:5]
          print "set to query (%s) vmList" % specifyVM
        elif (path[3:5] =="BB"):
          vmList = vmBBList
          specifyVM = path[3:5]
          print "set to query (%s) vmList" % specifyVM
        else:
          if (specifyVM =="AA"):
              if ( int(path[3:5]) >= len(vmAAList)) :
                vmList = vmAAList
                print "No such server, set to vmList(%s)" % specifyVM
              else: 
                vmList = [vmAAList[int(path[3:5])] ]
                print "set to query (%s) %s %s" % (specifyVM, vmList[0][4], vmList[0][0])
          elif (specifyVM =="BB"):
              if ( int(path[3:5]) >= len(vmBBList)) :
                vmList = vmBBList
                print "No such server, set to vmList(%s)" % specifyVM
              else:
                vmList = [vmBBList[int(path[3:5])] ]
                print "set to query (%s) %s %s" % (specifyVM, vmList[0][4], vmList[0][0])
          elif (len(specifyVM) ==3):
              if ( int(path[3:5]) >= len(vmList)) :
                print "No such server, set to vmList(%s)" % specifyVM
              else:
                vmList = [vmList[int(path[3:5])] ]
                print "set to query (%s) %s %s" % (specifyVM, vmList[0][4], vmList[0][0])          
    elif path.lower() == "chost\n":
      for i in range(len(vmList)):
        try:
          if vmList[i][2] == "root" : 
            runScriptOutput (vmList[i], "sh /home/changehost.sh "+vmList[i][4])
          else:
            runScriptOutput (vmList[i], "sh changehost.sh "+vmList[i][4])
        except Exception as e:
            print vmList[i][4] + str(e)
            continue
    elif path.lower() == "cuuid\n":
      for i in range(len(vmList)):
        try:
          if vmList[i][2] == "root" : 
            runScriptOutput (vmList[i], "sh /home/changeuuid.sh "+vmList[i][4])
          else:
            runScriptOutput (vmList[i], "sh changeuuid.sh "+vmList[i][4])
        except Exception as e:
            print vmList[i][4] + str(e)
            continue
    elif path.lower() == "pingme\n":
      for i in range(len(vmList)):
        try:
          if (platform.system() == "Linux") :
              cmd = "ping %s -c %d" % (vmList[i][0],1)
          else :
              cmd = "ping %s -n %d" % (vmList[i][0],1) #windows
          print subprocess.check_output(cmd, shell=True)
        except Exception as e:
            continue
    elif path.lower() == "addi\n":
      for i in range(len(vmList)):
        try:
          if vmList[i][2] == "root" : 
            runScriptOutput (vmList[i], "sh /home/addiveda.sh "+vmList[i][4])
          else:
            runScriptOutput (vmList[i], "sh addiveda.sh "+vmList[i][4])

        except Exception as e:
            print vmList[i][4] + str(e)
            continue
    elif path.lower() == "ict\n":
      for i in range(len(vmList)):
        try:
          if vmList[i][2] == "root" : 
            runScriptOutput (vmList[i], "sh /home/timezone.sh Asia/Ho_Chi_Minh")
          else:
            runScriptOutput (vmList[i], "sh timezone.sh Asia/Ho_Chi_Minh")
        except Exception as e:
            print vmList[i][4] + str(e)
            continue
    elif path.lower() == "putfile3\n":
        fileExtArr = ["changehost.sh", "changeuuid.sh", "addiveda.sh"]
        for i in range(len(vmList)):
          try:
            for x in range (0,len(fileExtArr)):
              if (not os.path.isfile(fileExtArr[x])):
                fileExt = sys.path[0]+"/"+fileExtArr[x]
              putFile(vmList[i],fileExt)
          except Exception as e:
              print vmList[i][4] + str(e)
              continue

    elif path.lower() == "pinglist\n":
      print "=========================="
      for i in range(len(vmList)):
        try:
          if pinghost (vmList[i][0]):
              status = "Avail"
          else:
              status = "NA!!"
          print '(%-5s)%-15s: %-15s (%s) / %-15s' % (status,vmList[i][4], vmList[i][0],vmList[i][1], vmList[i][2])
        except Exception as e:
            continue
      print "=========================="
    elif path.lower() == "help\n":
      for i in range(len(helpList)):
        print '%-15s: %s' % (helpList[i][0], helpList[i][1])
    elif path.lower().strip("\n") in cmdList:
      for sublist in helpList:
          if sublist[0] == path.lower().strip("\n") :
              inputcmd = sublist[1]
      for i in range(len(vmList)):
        try:
            runScriptOutput (vmList[i], inputcmd)
        except Exception as e:
            print vmList[i][4] + str(e)
            continue
    elif path.lower() == "allpoweroff\n":
      print ('Are you sure to power off all VMs (Y/Yes)') 
      ch = stdin.readline()
      if "y" in ch.lower() :
        inputcmd = "sudo poweroff"
        #seq: admin/rtmp/tunnel/stream/web/lb/db
        for i in range(len(vmList)):
          try:
            if "admin" in vmList[i][4].lower() :
              print vmList[i][4] + inputcmd #runScriptOutput (vmList[i], inputcmd)
          except  Exception as e:
            print vmList[i][4] + str(e)
            continue
  
        for i in range(len(vmList)):
          try:
            if "rtmp" in vmList[i][4].lower() :
              print vmList[i][4] + inputcmd #runScriptOutput (vmList[i], inputcmd)
          except  Exception as e:
            print vmList[i][4] + str(e)
            continue
  
        for i in range(len(vmList)):
          try:
            if "tunnel" in vmList[i][4].lower() :
              print vmList[i][4] + inputcmd #runScriptOutput (vmList[i], inputcmd)
          except  Exception as e:
            print vmList[i][4] + str(e)
            continue
  
        for i in range(len(vmList)):
          try:
            if "stream" in vmList[i][4].lower() :
              print vmList[i][4] + inputcmd #runScriptOutput (vmList[i], inputcmd)
          except  Exception as e:
            print vmList[i][4] + str(e)
            continue
  
        for i in range(len(vmList)):
          try:
            if "web" in vmList[i][4].lower() :
              print vmList[i][4] + inputcmd #runScriptOutput (vmList[i], inputcmd)
          except  Exception as e:
            print vmList[i][4] + str(e)
            continue
  
        for i in range(len(vmList)):
          try:
            if ("lb" in vmList[i][4].lower()) or ("balance" in vmList[i][4].lower()) :
              print vmList[i][4] + inputcmd #runScriptOutput (vmList[i], inputcmd)
          except  Exception as e:
            print vmList[i][4] + str(e)
            continue
        for i in range(len(vmList)):
          try:
            if ("db" in vmList[i][4].lower()) or ("database" in vmList[i][4].lower()) :
              print vmList[i][4] + inputcmd #runScriptOutput (vmList[i], inputcmd)
          except  Exception as e:
            print vmList[i][4] + str(e)
            continue
    elif path.lower() == "r\n":
        for i in range(len(vmList)):
          try:
            runScriptOutput (vmList[i], backupCMD)
          except Exception as e:
              print vmList[i][4] + str(e)
              continue
    else:
        backupCMD = path
        for i in range(len(vmList)):
          try:
            runScriptOutput (vmList[i], path)
          except Exception as e:
              print vmList[i][4] + str(e)
              continue
    print datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    print msg
    path = stdin.readline()