import subprocess
import os
import sys
#auto get param from
confPATH="../isat_stream/stream_server_control.conf"
if (os.path.isfile(confPATH)):
  MYSQL_HOST = subprocess.check_output("grep ^mysql_host "+confPATH, shell=True).split(" ")[1].strip('\n') 
  ISAT_DB_NAME = subprocess.check_output("grep ^mysql_db "+confPATH, shell=True).split(" ")[1].strip('\n') 
  ISAT_DB_USER = subprocess.check_output("grep ^mysql_user "+confPATH, shell=True).split(" ")[1].strip('\n') 
  ISAT_DB_PWD = subprocess.check_output("grep ^mysql_passwd "+confPATH, shell=True).split(" ")[1].strip('\n') 
else:
  ISAT_DB_NAME="isat"
  MYSQL_HOST="192.168.1.140"
  ISAT_DB_USER="isatRoot"
  ISAT_DB_PWD="isatPassword"

response = os.system("ping -c 1 " + MYSQL_HOST +" > /dev/null 2>&1")
if (response != 0) : 
  sys.exit( "mysql connection error! %s"  % MYSQL_HOST)
import MySQLdb
if (len(sys.argv)==2):
    db = MySQLdb.connect(host=MYSQL_HOST, user=ISAT_DB_USER, passwd=ISAT_DB_PWD, db=ISAT_DB_NAME)
    #db.autocommit(True)
    cursor = db.cursor()
    cursor.execute(str(sys.argv[1]))
    db.commit()  #need this to work on insert
    result = cursor.fetchall()
         
    for record in result:
        print record[0]
else:
  sys.exit( "no sql parameter!")