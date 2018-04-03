MYSQL_HOST="192.168.1.140"
ISAT_DB_NAME="godwatch"
ISAT_DB_USER="isatRoot"
ISAT_DB_PWD="isatPassword"
import sys
import MySQLdb
import os  #import listdir

db = MySQLdb.connect(host=MYSQL_HOST, user=ISAT_DB_USER, passwd=ISAT_DB_PWD, db=ISAT_DB_NAME)
#db.autocommit(True)

def queryDB (db, defaultStr) :
    cursor = db.cursor()
    cursor.execute(defaultStr)
    db.commit()  #need this to work on insert
    result = cursor.fetchall()
    for index, value in enumerate(result):
        print value  


if (len(sys.argv)==2):
    defaultStr = "select id,start,end from isat.recording_list where path like '/vod/%"+str(sys.argv[1])+"%' order by id desc limit 10"
    #"select id,start,end from isat.recording_list where path like '/vod/Z01CC-<mac>%'"
    print "(%s) latest 10\n%-15s %-15s %-15s" % ("recording_list","id","start","end")
    queryDB (db, defaultStr)
    defaultStr = "select recording_id, date from isat.cloud_event where device_uid like '%"+str(sys.argv[1])+"' order by id desc limit 10"
    print "(%s) latest 10\n%-15s %-15s" % ("cloud_event","id","date")
    queryDB (db, defaultStr)
    print "usage: python checkmac.py <12 digit MAC>"
elif (len(sys.argv)==3):
    if (str(sys.argv[1]) =="delete" ):
        defaultStr = "delete from isat.recording_list where id= "+str(sys.argv[2])
        print "delete (%s) id: %-15s" % ("recording_list",str(sys.argv[2]) )
        queryDB (db, defaultStr)
        defaultStr = "delete from isat.cloud_event where recording_id= "+str(sys.argv[2])
        print "delete (%s) id: %-15s" % ("cloud_event",str(sys.argv[2]) )
        queryDB (db, defaultStr)
    elif (str(sys.argv[1]) =="select" ):
        defaultStr ="select id,path from isat.recording_list where id="+str(sys.argv[2])
        print "(%s)%-5s %-50s" % ("recording_list","id","path")
        queryDB (db, defaultStr)
    elif (str(sys.argv[1]) =="getpath" ):
        defaultStr ="select path from isat.recording_list where id="+str(sys.argv[2])
        queryDB (db, defaultStr)
        
    #print "usage: python checkmac.py delete <id#>\npython checkmac.py select <id#>"
else:
    print "no parameter! (%d)" % len(sys.argv)
    defaultStr ="select group_id,uid,name from godwatch.gw_device where device_type='COMMON'"
    print "(%s)\n%-15s %-15s %-15s" % ("gw_device COMMON","group_id","uid","name")
    queryDB (db, defaultStr)
 