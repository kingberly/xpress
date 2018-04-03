#Validated on Dec-18,2017,
## query UID from database of current tunnel server 
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
  ISAT_DB_NAME="isat"
  MYSQL_HOST="192.168.1.140"
  ISAT_DB_USER="isatRoot"
  ISAT_DB_PWD="isatPassword"

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
if (len(sys.argv)==2):
  PORT=str(sys.argv[1])
  if ( (len(PORT) != 5) or (not PORT.isdigit()) ):
    sys.exit("error port format")
  mysqlstr="SELECT device_uid, bind_port,url_prefix from isat.tunnel_server_assignment where tunnel_server_uid ='"+UUID+"' and bind_port = "+PORT
else:
  mysqlstr="SELECT device_uid, bind_port from isat.tunnel_server_assignment where tunnel_server_uid ='"+UUID+"' and url_prefix='http://'"
db = MySQLdb.connect(host=MYSQL_HOST, user=ISAT_DB_USER, passwd=ISAT_DB_PWD, db=ISAT_DB_NAME)
#db.autocommit(True)
cursor = db.cursor()
cursor.execute(mysqlstr)
#db.commit()  #need this to work on insert
if (len(sys.argv)==2):
  result=cursor.fetchone()
  if result is not None:
    if (result[2]=="http://"):
      print result[0]
  else:
    print "No such http port"
else:
  result = cursor.fetchall()
  for record in result:
    print str(record[1])+"=>" +record[0]

cursor.close()
#db.close()