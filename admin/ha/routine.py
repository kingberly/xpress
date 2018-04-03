import sys
import datetime
import os
#import os.path
#update for v3.2.x

def execCommand(command):
    stdout = os.popen(command)
    res = stdout.read()
    print res  
       
#support eth0 HA only
def isMaster():
    stdout = os.popen("ifconfig | grep eth0:0 | wc -l")
    isMaster = stdout.read().replace("\n", "")
    return int(isMaster)

# 
# Main function 
# /usr/igs/scripts/routine.py 

if isMaster():
    print "I am master, I am going to do my job~"
    # every minute
    execCommand("/usr/bin/php5 /var/www/qlync_admin/html/server/tunnel_log.php")
    execCommand("/usr/bin/php5 /var/www/qlync_admin/html/server/evo_log.php")
    execCommand("/usr/bin/php5 /var/www/qlync_admin/html/server/web_log.php")
    execCommand("/usr/bin/php5 /var/www/qlync_admin/html/server/partner_log.php")
    
    now = datetime.datetime.now()
    #special task based on file existence by Jinho
    #tunnel
    if (os.path.isfile("/var/www/qlync_admin/plugin/taipei/tunnel_connlog_tpe.php")):
        if now.minute == 0 or now.minute == 30:
          execCommand("/usr/bin/php5 /var/www/qlync_admin/plugin/taipei/tunnel_connlog_tpe.php")
    elif (os.path.isfile("/var/www/qlync_admin/plugin/debug/tunnel_connlog_Const.php")):
        if now.minute == 0 or now.minute == 30 or now.minute == 15 or now.minute == 45:
          execCommand("/usr/bin/php5 /var/www/qlync_admin/plugin/debug/tunnel_connlog_Const.php")
    #rtmp    
    if (os.path.isfile("/var/www/qlync_admin/plugin/taipei/rtmpd_connlog_tpe.php")):
        if now.minute == 0 or now.minute == 30:
          execCommand("/usr/bin/php5 /var/www/qlync_admin/plugin/taipei/rtmpd_connlog_tpe.php")
    elif (os.path.isfile("/var/www/qlync_admin/plugin/debug/rtmpd_connlog_Const.php")):
        if now.minute == 0 or now.minute == 30 or now.minute == 15 or now.minute == 45:
          execCommand("/usr/bin/php5 /var/www/qlync_admin/plugin/debug/rtmpd_connlog_Const.php")

    #licenservice special plugin sunday: isoweekday:mon=1
    if os.path.isfile("/var/www/qlync_admin/plugin/licservice/addLicenseNotify.php") and now.isoweekday() == 7 and now.hour == 5 and now.minute == 5:
        execCommand("/usr/bin/php5 /var/www/qlync_admin/plugin/licservice/addLicenseNotify.php")

    # every day
    if now.hour == 1 and now.minute == 1:
        execCommand("/usr/bin/php5 /var/www/qlync_admin/html/common/daily_update_device.php >> /var/www/qlync_admin/html/common/update_device.log")
    

    if now.hour == 2 and now.minute == 2:
        execCommand("/usr/bin/php5 /var/www/qlync_admin/html/common/auto_model_list_feature.php >> /var/www/qlync_admin/html/common/update_model.log")

    #2016-11-10 new qlync added
    if (os.path.isfile("/var/www/qlync_admin/html/common/reservation_check.php")) and now.minute == 10:
          execCommand("/usr/bin/php5 /var/www/qlync_admin/html/common/reservation_check.php  >> /var/tmp/reservation.log")
    if (os.path.isfile("/var/www/qlync_admin/html/common/monthly_service_log.php")) and now.hour == 1 and now.minute == 0 and now.day == 1:
          execCommand("/usr/bin/php5 /var/www/qlync_admin/html/common/monthly_service_log.php >> /var/tmp/monthly_bill_update.log")

else:
    print "I am not master, skip this round~"
