import subprocess
from sys import stdin
import datetime
import os

logfile = "checkRecording.log"
nasfolder="/media/videos/"
#cameraUID = "Z01CC-001BFE054ED1"
#date = "20150701"
interval = 10
statDiff = 1.5 * interval / 10.0  #10min /12M   60min/175kbps/70M
camType = "Z01CC-001B" #"Z01CC-001B" , "Z01CC-0000", "Z01CC-" , "CC-" , "M04CC-1"

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

def fileSizeAvg(flist):
    size = 0.0
    for index, value in enumerate(flist):
      if "K" in value:
          size += float(int(value.strip("K\n"))/1024.0)
      else:
          size +=  float(value.strip("M\n"))
    avgSize = size / float(index)
    return avgSize
########
# Main
########
print "check interval : %d\n" % interval
flog = open(logfile, 'a')
flog.write("======="+datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")+"=======\n")
cmd = "ls -al %s | grep %s | awk '{print$9}'" % (nasfolder,camType)
cameraUIDlist = subprocess.check_output(cmd, shell=True).split()
print "input date like 20150701 >> "
dateInput = stdin.readline()
while len(dateInput) > 0:
    if (dateInput == "\n"):
        break;
    elif len(dateInput)==19 and dateInput[18] == "\n": #MAC only
        break;
    elif len(dateInput)==9 and dateInput[8] == "\n":
        camCount = 0
        camPerfect = 0
        date = dateInput[:8]
        flog.write("datefolder %s\n" % date)
        pfilecount = 24 * (60/interval)
        try:
          for index, value in enumerate(cameraUIDlist):
              cameraUID = value
              #count
              if os.path.isdir(nasfolder+cameraUID+"/"+date) :
                  cmd = "ls -al %s%s/%s | grep ^- | wc -l" % (nasfolder,cameraUID,date)
                  filecount = subprocess.check_output(cmd, shell=True).strip("\n")
                  if  (filecount.isdigit()):
                      camCount += 1
                      if (int(filecount) != pfilecount) :
                          print "%s folder file# : %d (perfect# %d)" % (cameraUID, int(filecount), pfilecount)
                          flog.write("%s folder file# : %d (perfect# %d)\n" % (cameraUID, int(filecount), pfilecount) )
                          #time interval
                          cmd = "ls -alh %s%s/%s | grep ^- | awk '{print$8}'" % (nasfolder,cameraUID,date)
                          datelist = subprocess.check_output(cmd, shell=True).split()
                          #print when missing file by interval
                          for index, value in enumerate(datelist):
                              if index > 0 :
                                   timediff1 = datelist[index].split(":")
                                   timediff2 = datelist[index-1].split(":")
                                   timediffH = int(timediff1[0]) - int(timediff2[0])
                                   if timediffH ==0 : 
                                      timediffM =int(timediff1[1]) - int(timediff2[1])
                                   elif timediffH ==1 :
                                      timediffM =int(timediff1[1])+ 60 - int(timediff2[1])
                                   if  abs(timediffM - interval) > 2 :
                                        if abs(timediffM - interval)==0 :
                                          print "%s th value identical @ %s" % (index, datelist[index])
                                          #flog.write( "%s th value identical @ %s" % (index, datelist[index]))
                                        else:
                                          print "%s th value is deviated @ %s to %s" % (index, datelist[index],datelist[index-1])
                                          #flog.write("%s th value is deviated @ %s to %s\n" % (index, datelist[index]),datelist[index-1])
                                        #filelist = []
                                        #cmd = "ls -alh %s%s/%s | grep ^- | awk '{print$5}'" % (nasfolder,cameraUID,date)
                                        #filelist = subprocess.check_output(cmd, shell=True).split()
                                        #avg = fileSizeAvg (filelist) 
                                        #print "size avg : %.2f M" %  avg
                                        #for index, value in enumerate(filelist):
                                        #   if "K" in value:
                                        #       if  abs((float(int(value.strip("K\n"))/1024.0)- avg)) > statDiff :
                                        #          print "%s th value %s is deviated from avg %.2f @ %s" % (index, value, statDiff, datelist[index])
                                        #          flog.write("%s th value %s is deviated from avg %.2f @ %s\n" % (index, value, statDiff, datelist[index])) 
                                        #   else:
                                        #       if  abs((float(value.strip("M\n"))- avg)) > statDiff :
                                        #          print "%s th value %s is deviated from avg %.2f @ %s" % (index, value, statDiff, datelist[index])
                                        #          flog.write("%s th value %s is deviated from avg %.2f @ %s\n" % (index, value, statDiff, datelist[index]))
                      else:
                          camPerfect += 1
                          print "%s folder file# Not Interruptted" % (cameraUID)
                          flog.write("%s folder file# Not Interruptted" % (cameraUID) )
          print "%s : Total %d folder, %d Not Interrupted." % (date, camCount, camPerfect)
          flog.write("%s : Total %d folder, %d Not Interrupted." % (date, camCount, camPerfect) )
        except Exception as e: 
          print str(e)
          continue
    print "input date like 20150701 >> "
    dateInput = stdin.readline()
flog.close()