#Validated on Jan-22,2018,
## query external IP from database of current tunnel server
#watch -n 10 <cmd>
#echo "`date '+%Y/%m/%d %H:%M:%S'`$(sudo python checkIP.py)" >> log 
#Writer: JinHo, Chang
import subprocess
import os
import sys
#auto get param from
#currentFile = __file__
#realPath = os.path.realpath(currentFile)  # /home/user/test/my_script.py
#dirPath = os.path.dirname(realPath)  # /home/user/test
dirPath = os.path.dirname(os.path.realpath(__file__))
confPATH=dirPath+"/../tunnel_server/tunnel_server.conf"
if (os.path.isfile(confPATH)):
  MYSQL_HOST = subprocess.check_output("grep ^mysql_host "+confPATH, shell=True).split(" ")[1].strip('\n') 
  ISAT_DB_NAME = subprocess.check_output("grep ^mysql_db "+confPATH, shell=True).split(" ")[1].strip('\n') 
  ISAT_DB_USER = subprocess.check_output("grep ^mysql_user "+confPATH, shell=True).split(" ")[1].strip('\n') 
  ISAT_DB_PWD = subprocess.check_output("grep ^mysql_password "+confPATH, shell=True).split(" ")[1].strip('\n') 
else:
  sys.exit("No MYSQL DB setting");
response = os.system("ping -c 1 " + MYSQL_HOST +" > /dev/null 2>&1")
if (response != 0) : 
  sys.exit( "mysql connection error! %s"  % MYSQL_HOST)
  #print "Mysql config: %s/%s @%s/%s" % (ISAT_DB_USER,ISAT_DB_PWD,MYSQL_HOST,ISAT_DB_NAME)

#uuid
UUID = subprocess.check_output("sudo blkid | grep ext4 | head -n 1  | tail -c 51 |head -c 36 | awk -F\"-\" '{ print $1$2$3$4$5 }'", shell=True).strip('\n')
#16cc11dc634844d89c91e3ea6e4d35ce
if (len(UUID) != 32):
  sys.exit( "uuid error! %s"  % UUID)
  #print "uuid is %s" % UUID  
import MySQLdb
mysqlstr="SELECT internal_address,external_address from isat.tunnel_server where ( external_address IS NULL OR external_address='') and uid ='"+UUID+"'"
db = MySQLdb.connect(host=MYSQL_HOST, user=ISAT_DB_USER, passwd=ISAT_DB_PWD, db=ISAT_DB_NAME)
#db.autocommit(True)
cursor = db.cursor()
cursor.execute(mysqlstr)
#db.commit()  #need this to work on insert
if (cursor.rowcount == 1):
  if (cursor.fetchone()[0] != "") and (cursor.fetchone()[1] == ""):
    print "EXTERNAL_ADDRESS ERROR"
  else:
    print "(%s)" % cursor.fetchone()
else:
  print "rowcount <> 1"
cursor.close()
#db.close()