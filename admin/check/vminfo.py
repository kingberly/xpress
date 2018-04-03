oem=""
import subprocess
import platform
def get_ip_address(ifname):
    s = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
    return socket.inet_ntoa(fcntl.ioctl(
        s.fileno(),
        0x8915,  # SIOCGIFADDR
        struct.pack('256s', ifname[:15])
    )[20:24])
#localIP = str(get_ip_address('eth0'))
#socket.gethostbyname(socket.gethostname())
if oem=="" : #check if oem is not set
  if (platform.system() == "Linux") :
      if (os.path.isfile("/var/www/qlync_admin/doc/config.php")):  
        cmd="sed -n '/$oem=/p' /var/www/qlync_admin/doc/config.php | tail -c 7 | head -c 5"  #"X"
        oem =subprocess.check_output(cmd, shell=True).strip('"').lower()
  else:#windows assign x02
      oem = "x02"
oem=oem.lower()
if oem=="t03": #test.ivedaxpress.com
  vAdmin = ['10.6.77.202', '22', 'xp41admin', '37n3nls8!', 'HA admin'] 
elif oem=="z02": #zee.ivedaxpress.com
  vm =[
  ['192.168.20.201', '22', 'ivedasuper', 'iltwaiveda', 'db'],
  ['192.168.20.201', '22', 'ivedasuper', 'iltwaiveda', 'web'],
  ['192.168.20.201', '22', 'ivedasuper', 'iltwaiveda', 'tunnel'],
  ['192.168.20.201', '22', 'ivedasuper', 'iltwaiveda', 'stream'],
  ['192.168.20.201', '22', 'ivedasuper', 'iltwaiveda', 'admin'],
  ]
  
elif oem=="p04": #videomonitoring.pldtcloud.com
  vm = [
  ['192.168.0.141', '22', 'ivedasuper', '1qazxdr56yhN', 'db141'],
  ['192.168.0.201', '22', 'ivedasuper', '1qazxdr56yhN', 'admin201'],
  ['192.168.0.101', '22', 'ivedasuper', '1qazxdr56yhN', 'lb101'],
  ['192.168.0.102', '22', 'ivedasuper', '1qazxdr56yhN', 'lb102'],
  ['192.168.0.7', '22', 'ivedasuper', '1qazxdr56yhN', 'web1'],
  ['192.168.0.14', '22', 'ivedasuper', '1qazxdr56yhN', 'web2'],
  ['192.168.0.111', '22', 'ivedasuper', '1qazxdr56yhN', 'web111'],
  ["192.168.0.112","22","ivedasuper","1qazxdr56yhN","Web-112"],
  ["192.168.0.113","22","ivedasuper","1qazxdr56yhN","Web-113"],
  ["192.168.0.114","22","ivedasuper","1qazxdr56yhN","Web-114"],
  ["192.168.0.115","22","ivedasuper","1qazxdr56yhN","Web-115"],
  ["192.168.0.116","22","ivedasuper","1qazxdr56yhN","Web-116"],
  ["192.168.0.117","22","ivedasuper","1qazxdr56yhN","Web-117"],
  ["192.168.0.118","22","ivedasuper","1qazxdr56yhN","Web-118"],
  ["192.168.0.119","22","ivedasuper","1qazxdr56yhN","Web-119"],
  ["192.168.0.110","22","ivedasuper","1qazxdr56yhN","Web-110"],
  ['192.168.0.121', '22', 'ivedasuper', '1qazxdr56yhN', 'tun121'],
  ['192.168.0.122', '22', 'ivedasuper', '1qazxdr56yhN', 'tun122'],
  ['192.168.0.123', '22', 'ivedasuper', '1qazxdr56yhN', 'tun123'],
  ['192.168.0.124', '22', 'ivedasuper', '1qazxdr56yhN', 'tunnel4'],
  ['192.168.0.125', '22', 'ivedasuper', '1qazxdr56yhN', 'tunnel5'],
  ['192.168.0.126', '22', 'ivedasuper', '1qazxdr56yhN', 'tunnel6'],
  ['192.168.0.127', '22', 'ivedasuper', '1qazxdr56yhN', 'tunnel7'],
  ['192.168.0.128', '22', 'ivedasuper', '1qazxdr56yhN', 'tunnel8'],
  ['192.168.0.129', '22', 'ivedasuper', '1qazxdr56yhN', 'tunnel9'],
  ['192.168.0.120', '22', 'ivedasuper', '1qazxdr56yhN', 'tunnel120'],
  ['192.168.0.131', '22', 'ivedasuper', '1qazxdr56yhN', 'str131'],
  ['192.168.0.132', '22', 'ivedasuper', '1qazxdr56yhN', 'str132'],
  ['192.168.0.133', '22', 'ivedasuper', '1qazxdr56yhN', 'str133'],
  ['192.168.0.134', '22', 'ivedasuper', '1qazxdr56yhN', 'str134'],
  ['192.168.0.135', '22', 'ivedasuper', '1qazxdr56yhN', 'stream5'],
  ['192.168.0.136', '22', 'ivedasuper', '1qazxdr56yhN', 'stream6'],
  ['192.168.0.137', '22', 'ivedasuper', '1qazxdr56yhN', 'stream7'],
  ['192.168.0.138', '22', 'ivedasuper', '1qazxdr56yhN', 'stream8'],
  ['192.168.0.139', '22', 'ivedasuper', '1qazxdr56yhN', 'stream9'],
  ['192.168.0.130', '22', 'ivedasuper', '1qazxdr56yhN', 'stream130']
  ]
  vWeb = ['192.168.0.100', '22', 'ivedasuper', '1qazxdr56yhN', 'HA web/LB']

elif oem=="j01": #japan.ivedaxpress.com
  vm = [
  ['192.168.100.134', '22', 'ivedasuper', '1qazxdr56yhN', 'db'],
  ['192.168.100.131', '22', 'ivedasuper', '1qazxdr56yhN', 'admin'],
  ['192.168.100.130', '22', 'ivedasuper', '1qazxdr56yhN', 'web'],
  ['192.168.100.132', '22', 'ivedasuper', '1qazxdr56yhN', 'tunnel'],
  ['192.168.100.133', '22', 'ivedasuper', '1qazxdr56yhN', 'stream']
  ]
  
elif oem=="x02": #xpress.megasys.com.tw
  vm = [
  ['192.168.1.140', '22', 'ivedasuper', '1qazxdr56yhN', 'db'],
  ['192.168.1.201', '22', 'ivedasuper', '1qazxdr56yhN', 'admin-01'],
  ['192.168.1.202', '22', 'ivedasuper', '1qazxdr56yhN', 'admin-02'],
  ['192.168.1.101', '22', 'ivedasuper', '1qazxdr56yhN', 'balancer-01'],
  ['192.168.1.102', '22', 'ivedasuper', '1qazxdr56yhN', 'balancer-02'],
  ['192.168.1.111', '22', 'ivedasuper', '1qazxdr56yhN', 'web-1'],
  ['192.168.1.112', '22', 'ivedasuper', '1qazxdr56yhN', 'web-2'],  
  ['192.168.1.113', '22', 'ivedasuper', '1qazxdr56yhN', 'web-3'],
  ['192.168.1.114', '22', 'ivedasuper', '1qazxdr56yhN', 'Web-114'],
  ['192.168.1.115', '22', 'ivedasuper', '1qazxdr56yhN', 'Web-115'],
  ['192.168.1.121', '22', 'ivedasuper', '1qazxdr56yhN', 'tunnel1'],
  ['192.168.1.122', '22', 'ivedasuper', '1qazxdr56yhN', 'tunnel2'],
  ['192.168.1.123', '22', 'ivedasuper', '1qazxdr56yhN', 'tunnel3'],
  ['192.168.1.124', '22', 'ivedasuper', '1qazxdr56yhN', 'tunnel4'],
  ['192.168.1.125', '22', 'ivedasuper', '1qazxdr56yhN', 'tunnel5'],
  #['192.168.1.126', '22', 'ivedasuper', '1qazxdr56yhN', 'Tunnel-126'],
  ['192.168.1.127', '22', 'ivedasuper', '1qazxdr56yhN', 'Tunnel-127'],
  #['192.168.1.128', '22', 'ivedasuper', '1qazxdr56yhN', 'tunnel8'],
  ['192.168.1.131', '22', 'ivedasuper', '1qazxdr56yhN', 'stream1'],
  ['192.168.1.132', '22', 'ivedasuper', '1qazxdr56yhN', 'stream2'],
  ['192.168.1.133', '22', 'ivedasuper', '1qazxdr56yhN', 'stream3'],
  ['192.168.1.134', '22', 'ivedasuper', '1qazxdr56yhN', 'stream4'],
  ['192.168.1.135', '22', 'ivedasuper', '1qazxdr56yhN', 'Stream-135'],
  ['192.168.1.136', '22', 'ivedasuper', '1qazxdr56yhN', 'Stream-136'],
  #['192.168.1.137', '22', 'ivedasuper', '1qazxdr56yhN', 'Stream-137'],
  ['192.168.1.138', '22', 'ivedasuper', '1qazxdr56yhN', 'Stream-138'],
  ['192.168.1.151', '22', 'ivedasuper', '1qazxdr56yhN', 'rtmp01']
  ]
  vAdmin = ['192.168.1.200', '22', 'ivedasuper', '1qazxdr56yhN', 'HA Admin']
  vWeb = ['192.168.1.100', '22', 'ivedasuper', '1qazxdr56yhN', 'HA web/LB']
  
elif oem=="t04": # rpic.taipei
  vm = [
  ['192.168.1.140', '22', 'ivedasuper', '1qazxdr56yhN', 'DB-140'],
  #['192.168.1.149', '22', 'ivedasuper', '1qazxdr56yhN', 'DB-149'],
  ['192.168.1.201', '22', 'ivedasuper', '1qazxdr56yhN', 'Admin-201'],
  ['192.168.1.202', '22', 'ivedasuper', '1qazxdr56yhN', 'Admin-202'],
  ['192.168.1.101', '22', 'ivedasuper', '1qazxdr56yhN', 'LB-101'],
  ['192.168.1.102', '22', 'ivedasuper', '1qazxdr56yhN', 'LB-102'],
  ['192.168.1.111', '22', 'ivedasuper', '1qazxdr56yhN', 'Web-111'],
  ['192.168.1.112', '22', 'ivedasuper', '1qazxdr56yhN', 'Web-112'],  
  ['192.168.1.113', '22', 'ivedasuper', '1qazxdr56yhN', 'Web-113'],
  ['192.168.1.114', '22', 'ivedasuper', '1qazxdr56yhN', 'Web-114'],
  ['192.168.1.115', '22', 'ivedasuper', '1qazxdr56yhN', 'Web-115'],
  ['192.168.1.116', '22', 'ivedasuper', '1qazxdr56yhN', 'Web-116'],
  ['192.168.1.117', '22', 'ivedasuper', '1qazxdr56yhN', 'Web-117'],
  ['192.168.1.118', '22', 'ivedasuper', '1qazxdr56yhN', 'Web-118'],
  ['192.168.1.121', '22', 'ivedasuper', '1qazxdr56yhN', 'tunnel-01'],
  ['192.168.1.122', '22', 'ivedasuper', '1qazxdr56yhN', 'tunnel-02'],
  ['192.168.1.123', '22', 'ivedasuper', '1qazxdr56yhN', 'tunnel-03'],
  ['192.168.1.124', '22', 'ivedasuper', '1qazxdr56yhN', 'tunnel-04'],
  ['192.168.1.125', '22', 'ivedasuper', '1qazxdr56yhN', 'tunnel-05'],
  ['192.168.1.126', '22', 'ivedasuper', '1qazxdr56yhN', 'tunnel-06'],
  ['192.168.1.127', '22', 'ivedasuper', '1qazxdr56yhN', 'tunnel-07'],
  ['192.168.1.128', '22', 'ivedasuper', '1qazxdr56yhN', 'tunnel-08'],
  #['192.168.1.129', '22', 'ivedasuper', '1qazxdr56yhN', 'tunnel-09'],
  ['192.168.1.170', '22', 'ivedasuper', '1qazxdr56yhN', 'Tunnel-170'],
  ['192.168.1.171', '22', 'ivedasuper', '1qazxdr56yhN', 'Tunnel-171'],
  ['192.168.1.172', '22', 'ivedasuper', '1qazxdr56yhN', 'Tunnel-172'],
  ['192.168.1.173', '22', 'ivedasuper', '1qazxdr56yhN', 'Tunnel-173'],
  ['192.168.1.174', '22', 'ivedasuper', '1qazxdr56yhN', 'Tunnel-174'],
  ['192.168.1.175', '22', 'ivedasuper', '1qazxdr56yhN', 'Tunnel-175'],
  ['192.168.1.176', '22', 'ivedasuper', '1qazxdr56yhN', 'Tunnel-176'],
  ['192.168.1.177', '22', 'ivedasuper', '1qazxdr56yhN', 'Tunnel-177'],
  ['192.168.1.178', '22', 'ivedasuper', '1qazxdr56yhN', 'Tunnel-178'],
  ['192.168.1.179', '22', 'ivedasuper', '1qazxdr56yhN', 'Tunnel-179'],
  ['192.168.1.131', '22', 'ivedasuper', '1qazxdr56yhN', 'Stream-131'],
  ['192.168.1.132', '22', 'ivedasuper', '1qazxdr56yhN', 'stream-02'],
  ['192.168.1.133', '22', 'ivedasuper', '1qazxdr56yhN', 'Stream-133'],
  ['192.168.1.134', '22', 'ivedasuper', '1qazxdr56yhN', 'Stream-134'],
  ['192.168.1.135', '22', 'ivedasuper', '1qazxdr56yhN', 'Stream-135'],
  ['192.168.1.136', '22', 'ivedasuper', '1qazxdr56yhN', 'Stream-136'],
  ['192.168.1.137', '22', 'ivedasuper', '1qazxdr56yhN', 'Stream-137'],
  ['192.168.1.138', '22', 'ivedasuper', '1qazxdr56yhN', 'Stream-138'],
  ['192.168.1.139', '22', 'ivedasuper', '1qazxdr56yhN', 'Stream-139'],
  ['192.168.1.180', '22', 'ivedasuper', '1qazxdr56yhN', 'Stream-180'],
  ['192.168.1.181', '22', 'ivedasuper', '1qazxdr56yhN', 'Stream-181'],
  ['192.168.1.182', '22', 'ivedasuper', '1qazxdr56yhN', 'Stream-182'],
  ['192.168.1.183', '22', 'ivedasuper', '1qazxdr56yhN', 'Stream-183'],
  ['192.168.1.184', '22', 'ivedasuper', '1qazxdr56yhN', 'Stream-184'],
  ['192.168.1.185', '22', 'ivedasuper', '1qazxdr56yhN', 'Stream-185'],
  ['192.168.1.186', '22', 'ivedasuper', '1qazxdr56yhN', 'Stream-186'],
  ['192.168.1.187', '22', 'ivedasuper', '1qazxdr56yhN', 'Stream-187'],
  ['192.168.1.188', '22', 'ivedasuper', '1qazxdr56yhN', 'Stream-188'],
  ['192.168.1.189', '22', 'ivedasuper', '1qazxdr56yhN', 'Stream-189'],
  ['192.168.1.141', '22', 'ivedasuper', '1qazxdr56yhN', 'Rtmp-141'],
  ['192.168.1.142', '22', 'ivedasuper', '1qazxdr56yhN', 'Rtmp-142'],
  ['192.168.1.143', '22', 'ivedasuper', '1qazxdr56yhN', 'Rtmp-143']
  ]
  vAdmin = ['192.168.1.200', '22', 'ivedasuper', '1qazxdr56yhN', 'HA Admin']
  vWeb = ['192.168.1.100', '22', 'ivedasuper', '1qazxdr56yhN', 'HA web/LB']
    
elif oem=="t05": #rpic.tycg.gov.tw
  vm = [
  ["192.168.1.111","22","ivedasuper","1qazxdr56yhN","Web-111"],
  ["192.168.1.112","22","ivedasuper","1qazxdr56yhN","Web-112"],
  ["192.168.1.113","22","ivedasuper","1qazxdr56yhN","Web-113"],
  ["192.168.1.121","22","ivedasuper","1qazxdr56yhN","Tunnel-121"],
  ["192.168.1.122","22","ivedasuper","1qazxdr56yhN","Tunnel-122"],
  ["192.168.1.123","22","ivedasuper","1qazxdr56yhN","Tunnel-123"],
  ["192.168.1.124","22","ivedasuper","1qazxdr56yhN","Tunnel-124"],
  ["192.168.1.125","22","ivedasuper","1qazxdr56yhN","Tunnel-125"],
  ["192.168.1.126","22","ivedasuper","1qazxdr56yhN","Tunnel-126"],
  ["192.168.1.151","22","ivedasuper","1qazxdr56yhN","Rtmp-151"],
  ["192.168.1.152","22","ivedasuper","1qazxdr56yhN","Rtmp-152"],
  ["192.168.1.131","22","ivedasuper","1qazxdr56yhN","Stream-131"],
  ["192.168.1.132","22","ivedasuper","1qazxdr56yhN","Stream-132"],
  ["192.168.1.133","22","ivedasuper","1qazxdr56yhN","Stream-133"],
  ["192.168.1.134","22","ivedasuper","1qazxdr56yhN","Stream-134"],
  ["192.168.1.135","22","ivedasuper","1qazxdr56yhN","Stream-135"],
  ["192.168.1.136","22","ivedasuper","1qazxdr56yhN","Stream-136"],
  ["192.168.1.140","22","ivedasuper","1qazxdr56yhN","DB-140"],
  ["192.168.1.142","22","ivedasuper","1qazxdr56yhN","DB-142"],
  ["192.168.1.101","22","ivedasuper","1qazxdr56yhN","LB-101"],
  ["192.168.1.102","22","ivedasuper","1qazxdr56yhN","LB-102"],
  ["192.168.1.201","22","ivedasuper","1qazxdr56yhN","Admin-201"],
  ["192.168.1.202","22","ivedasuper","1qazxdr56yhN","Admin-202"]
  ]
  vAdmin = ['192.168.1.200', '22', 'ivedasuper', '1qazxdr56yhN', 'HA Admin']
  vWeb = ['192.168.1.100', '22', 'ivedasuper', '1qazxdr56yhN', 'HA web/LB']

elif oem=="k01": #kreac.kcg.gov.tw
  vm = [
  ["192.168.2.111","22","ivedasuper","1qazxdr56yhN","Web-111"],
  ["192.168.2.112","22","ivedasuper","1qazxdr56yhN","Web-112"],
  ["192.168.2.113","22","ivedasuper","1qazxdr56yhN","Web-113"],
  ["192.168.2.114","22","ivedasuper","1qazxdr56yhN","Web-114"],
  ["192.168.2.115","22","ivedasuper","1qazxdr56yhN","Web-115"],
  ["192.168.2.121","22","ivedasuper","1qazxdr56yhN","Tunnel-121"],
  ["192.168.2.122","22","ivedasuper","1qazxdr56yhN","Tunnel-122"],
  ["192.168.2.123","22","ivedasuper","1qazxdr56yhN","Tunnel-123"],
  ["192.168.2.124","22","ivedasuper","1qazxdr56yhN","Tunnel-124"],
  ["192.168.2.125","22","ivedasuper","1qazxdr56yhN","Tunnel-125"],
  ["192.168.2.126","22","ivedasuper","1qazxdr56yhN","Tunnel-126"],
  ["192.168.2.127","22","ivedasuper","1qazxdr56yhN","Tunnel-127"],
  ["192.168.2.128","22","ivedasuper","1qazxdr56yhN","Tunnel-128"],
  ["192.168.2.170","22","ivedasuper","1qazxdr56yhN","Tunnel-170"],
  ["192.168.2.171","22","ivedasuper","1qazxdr56yhN","Tunnel-171"],
  ["192.168.2.151","22","ivedasuper","1qazxdr56yhN","Rtmp-151"],
  ["192.168.2.152","22","ivedasuper","1qazxdr56yhN","Rtmp-152"],
  ["192.168.2.131","22","ivedasuper","1qazxdr56yhN","Stream-131"],
  ["192.168.2.132","22","ivedasuper","1qazxdr56yhN","Stream-132"],
  ["192.168.2.133","22","ivedasuper","1qazxdr56yhN","Stream-133"],
  ["192.168.2.134","22","ivedasuper","1qazxdr56yhN","Stream-134"],
  ["192.168.2.135","22","ivedasuper","1qazxdr56yhN","Stream-135"],
  ["192.168.2.136","22","ivedasuper","1qazxdr56yhN","Stream-136"],
  ["192.168.2.137","22","ivedasuper","1qazxdr56yhN","Stream-137"],
  ["192.168.2.138","22","ivedasuper","1qazxdr56yhN","Stream-138"],
  ["192.168.2.180","22","ivedasuper","1qazxdr56yhN","Stream-180"],
  ["192.168.2.181","22","ivedasuper","1qazxdr56yhN","Stream-181"],
  ["192.168.2.140","22","ivedasuper","1qazxdr56yhN","DB-140"],
  #["192.168.2.142","22","ivedasuper","1qazxdr56yhN","DB-142"],
  ["192.168.2.101","22","ivedasuper","1qazxdr56yhN","LB-101"],
  ["192.168.2.102","22","ivedasuper","1qazxdr56yhN","LB-102"],
  ["192.168.2.201","22","ivedasuper","1qazxdr56yhN","Admin-201"],
  ["192.168.2.202","22","ivedasuper","1qazxdr56yhN","Admin-202"]
  ]
  vAdmin = ['192.168.2.200', '22', 'ivedasuper', '1qazxdr56yhN', 'HA Admin']
  vWeb = ['192.168.2.100', '22', 'ivedasuper', '1qazxdr56yhN', 'HA web/LB']
    
elif oem=="v04": # sentirvietnam.vn
  vm = [
  ['192.168.2.140', '22', 'ivedaadmin', 'Its@llG00d!', 'db'],
  ['192.168.2.201', '22', 'ivedaadmin', 'Its@llG00d!', 'Admin1'],
  ['192.168.2.202', '22', 'ivedaadmin', 'Its@llG00d!', 'Admin2'],
  ['192.168.2.160', '22', 'ivedaadmin', 'Its@llG00d!', 'LB1'],
  ['192.168.2.161', '22', 'ivedaadmin', 'Its@llG00d!', 'LB2'],
  ['192.168.2.101', '22', 'ivedaadmin', 'Its@llG00d!', 'web01'],
  ['192.168.2.102', '22', 'ivedaadmin', 'Its@llG00d!', 'web02'],
  ['192.168.2.120', '22', 'ivedaadmin', 'Its@llG00d!', 'tunnel01'],
  ['192.168.2.121', '22', 'ivedaadmin', 'Its@llG00d!', 'tunnel02'],
  ['192.168.2.122', '22', 'ivedaadmin', 'Its@llG00d!', 'tunnel03'],
  ['192.168.2.123', '22', 'ivedaadmin', 'Its@llG00d!', 'tunnel04'],
  ['192.168.2.124', '22', 'ivedaadmin', 'Its@llG00d!', 'tunnel05'],
  ['192.168.2.130', '22', 'ivedaadmin', 'Its@llG00d!', 'stream01'],
  ['192.168.2.131', '22', 'ivedaadmin', 'Its@llG00d!', 'stream02'],
  ['192.168.2.132', '22', 'ivedaadmin', 'Its@llG00d!', 'stream03'],
  ['192.168.2.133', '22', 'ivedaadmin', 'Its@llG00d!', 'stream04'],
  ['192.168.2.134', '22', 'ivedaadmin', 'Its@llG00d!', 'stream05'],
  ['192.168.2.135', '22', 'ivedaadmin', 'Its@llG00d!', 'stream06'],
  ['192.168.2.136', '22', 'ivedaadmin', 'Its@llG00d!', 'stream07'],
  ['192.168.2.137', '22', 'ivedaadmin', 'Its@llG00d!', 'stream08'],
  ['192.168.2.138', '22', 'ivedaadmin', 'Its@llG00d!', 'stream09'],
  ['192.168.2.139', '22', 'ivedaadmin', 'Its@llG00d!', 'stream10'],
  ['192.168.2.150', '22', 'ivedaadmin', 'Its@llG00d!', 'rtmp01'],
  ['192.168.2.151', '22', 'ivedaadmin', 'Its@llG00d!', 'rtmp02'],
  ['192.168.2.152', '22', 'ivedaadmin', 'Its@llG00d!', 'rtmp03']
  ]
  vAdmin = ['192.168.2.200', '22', 'ivedaadmin', 'Its@llG00d!', 'HA Admin']
  vWeb = ['192.168.2.100', '22', 'ivedaadmin', 'Its@llG00d!', 'HA web/LB']
    
elif oem=="v03": #camera.vnphone.vn
  vm = [
  ['192.168.1.140', '22', 'ivedasuper', '1qazxdr56yhN', 'db'],
  ['192.168.1.201', '22', 'ivedasuper', '1qazxdr56yhN', 'Admin1'],
  ['192.168.1.202', '22', 'ivedasuper', '1qazxdr56yhN', 'Admin2'],
  ['192.168.1.101', '22', 'ivedasuper', '1qazxdr56yhN', 'LB1'],
  ['192.168.1.102', '22', 'ivedasuper', '1qazxdr56yhN', 'LB2'],
  ['192.168.1.111', '22', 'ivedasuper', '1qazxdr56yhN', 'web-1'],
  ['192.168.1.112', '22', 'ivedasuper', '1qazxdr56yhN', 'web-2'],  
  ['192.168.1.113', '22', 'ivedasuper', '1qazxdr56yhN', 'web-3'],
  ['192.168.1.114', '22', 'ivedasuper', '1qazxdr56yhN', 'web-4'],
  ['192.168.1.115', '22', 'ivedasuper', '1qazxdr56yhN', 'web-5'],
  ['192.168.1.121', '22', 'ivedasuper', '1qazxdr56yhN', 'tunnel1'],
  ['192.168.1.122', '22', 'ivedasuper', '1qazxdr56yhN', 'tunnel2'],
  ['192.168.1.123', '22', 'ivedasuper', '1qazxdr56yhN', 'tunnel3'],
  ['192.168.1.124', '22', 'ivedasuper', '1qazxdr56yhN', 'tunnel4'],
  ['192.168.1.125', '22', 'ivedasuper', '1qazxdr56yhN', 'tunnel5'],
  ['192.168.1.131', '22', 'ivedasuper', '1qazxdr56yhN', 'stream1'],
  ['192.168.1.132', '22', 'ivedasuper', '1qazxdr56yhN', 'stream2'],
  ['192.168.1.133', '22', 'ivedasuper', '1qazxdr56yhN', 'stream3'],
  ['192.168.1.134', '22', 'ivedasuper', '1qazxdr56yhN', 'stream4'],
  ['192.168.1.135', '22', 'ivedasuper', '1qazxdr56yhN', 'stream5'],
  ['192.168.1.136', '22', 'ivedasuper', '1qazxdr56yhN', 'stream6'],
  ['192.168.1.137', '22', 'ivedasuper', '1qazxdr56yhN', 'stream7'],
  ['192.168.1.138', '22', 'ivedasuper', '1qazxdr56yhN', 'stream8'],
  ['192.168.1.139', '22', 'ivedasuper', '1qazxdr56yhN', 'stream9'],
  ['192.168.1.130', '22', 'ivedasuper', '1qazxdr56yhN', 'stream130'],
  ['192.168.1.150', '22', 'ivedasuper', '1qazxdr56yhN', 'stream150'],
  ['192.168.1.151', '22', 'ivedasuper', '1qazxdr56yhN', 'stream151'],
  ['192.168.1.152', '22', 'ivedasuper', '1qazxdr56yhN', 'stream152'],
  ['192.168.1.153', '22', 'ivedasuper', '1qazxdr56yhN', 'stream153'],
  ['192.168.1.154', '22', 'ivedasuper', '1qazxdr56yhN', 'stream154'],
  ['192.168.1.155', '22', 'ivedasuper', '1qazxdr56yhN', 'stream155'],
  ['192.168.1.156', '22', 'ivedasuper', '1qazxdr56yhN', 'stream156'],
  ['192.168.1.157', '22', 'ivedasuper', '1qazxdr56yhN', 'stream157'],
  ['192.168.1.158', '22', 'ivedasuper', '1qazxdr56yhN', 'stream158'],
  ['192.168.1.159', '22', 'ivedasuper', '1qazxdr56yhN', 'stream159'],
  ['192.168.1.160', '22', 'ivedasuper', '1qazxdr56yhN', 'stream160'],
  ['192.168.1.161', '22', 'ivedasuper', '1qazxdr56yhN', 'stream161']
  ]                                                      
  vAdmin = ['192.168.1.200', '22', 'ivedasuper', '1qazxdr56yhN', 'HA Admin']
  vWeb = ['192.168.1.100', '22', 'ivedasuper', '1qazxdr56yhN', 'HA web/LB']
elif oem=="t06": #workeye
  vm = [
  ['127.0.0.1', '22', 'ivedasuper', '1qazxdr56yhN', 'db'],
  ['127.0.0.1', '22', 'ivedasuper', '1qazxdr56yhN', 'web'],  
  ['127.0.0.1', '22', 'ivedasuper', '1qazxdr56yhN', 'tunnel'],
  ['127.0.0.1', '22', 'ivedasuper', '1qazxdr56yhN', 'stream'],
  ['127.0.0.1', '22', 'ivedasuper', '1qazxdr56yhN', 'rtmp'],
  ['127.0.0.1', '22', 'ivedasuper', '1qazxdr56yhN', 'admin']
  ]

elif oem=="c13": #engeye.chimei.com.tw #10.34.0.50?
  vm = [
  ['127.0.0.1', '22', 'ivedasuper', '1qazxdr56yhN', 'db'],
  ['127.0.0.1', '22', 'ivedasuper', '1qazxdr56yhN', 'web'],
  ['127.0.0.1', '22', 'ivedasuper', '1qazxdr56yhN', 'tunnel'],
  ['127.0.0.1', '22', 'ivedasuper', '1qazxdr56yhN', 'stream'],
  ['127.0.0.1', '22', 'ivedasuper', '1qazxdr56yhN', 'rtmp'],
  ['127.0.0.1', '22', 'ivedasuper', '1qazxdr56yhN', 'admin']
  ]
   
elif oem=="x01": #xpress2.megasys.com.tw
  vm = [
  ['192.168.1.140', '22', 'ivedasuper', '1qazxdr56yhN', 'db'],
  ['192.168.1.201', '22', 'ivedasuper', '1qazxdr56yhN', 'admin-201'],
  ['192.168.1.101', '22', 'ivedasuper', '1qazxdr56yhN', 'LB-101'],
  ['192.168.1.111', '22', 'ivedasuper', '1qazxdr56yhN', 'web-111'],
  ['192.168.1.112', '22', 'ivedasuper', '1qazxdr56yhN', 'web-112'],  
  ['192.168.1.121', '22', 'ivedasuper', '1qazxdr56yhN', 'tunnel121'],
  ['192.168.1.122', '22', 'ivedasuper', '1qazxdr56yhN', 'tunnel122'],
  ['192.168.1.131', '22', 'ivedasuper', '1qazxdr56yhN', 'stream131'],
  ['192.168.1.132', '22', 'ivedasuper', '1qazxdr56yhN', 'stream132'],
  ['192.168.1.134', '22', 'ivedasuper', '1qazxdr56yhN', 'stream134'],
  ['192.168.1.141', '22', 'ivedasuper', '1qazxdr56yhN', 'rtmp01']
  ]
  vAdmin = ['192.168.1.200', '22', 'ivedasuper', '1qazxdr56yhN', 'HA Admin']
  vWeb = ['192.168.1.100', '22', 'ivedasuper', '1qazxdr56yhN', 'HA web/LB']

else:
  print "No matching oem cid\n"

extAdmin = dict()
extAdmin['x01'] = ['xpress2.megasys.com.tw', '11022', 'ivedasuper', '1qazxdr56yhN', 'Admin']
extAdmin['v03'] = ['camera.vinaphone.vn', '8022', 'ivedasuper', '1qazxdr56yhN', 'Admin']
extAdmin['v04'] = ['sentirvietnam.vn', '8122', 'ivedaadmin', 'Its@llG00d!', 'Admin']
extAdmin['z02'] = ['zee.ivedaxpress.com', '8022', 'ivedasuper', 'iltwaiveda', 'Admin']
extAdmin['p04'] = ['videomonitoring.pldtcloud.com', '8122', 'ivedasuper', '1qazxdr56yhN', 'Admin-201']
extAdmin['j01'] = ['japan.ivedaxpress.com', '9131', 'ivedasuper', 'iveda2016168PASS', 'Admin']
extAdmin['x02'] = ['xpress.megasys.com.tw', '8022', 'ivedasuper', '1qazxdr56yhN', 'Admin']
extAdmin['t04'] = ['rpic.taipei', '8022', 'ivedasuper', '1qazxdr56yhN', 'Admin']   
extAdmin['t05'] = ['rpic.tycg.gov.tw', '8022', 'ivedasuper', '1qazxdr56yhN', 'Admin']
extAdmin['k01'] = ['kreac.kcg.gov.tw', '8022', 'ivedasuper', '1qazxdr56yhN', 'Admin']
extAdmin['c13'] = ['engeye.chimei.com.tw', '22', 'ivedasuper', '2wsxcft67ujM', 'Admin']
#original key is IP (0:hostname), changed to list index
if oem!="" : 
  genHosts = dict()
  strHosts = dict()
  tunHosts = dict()
  webHosts = dict()
  rtmpHosts = dict()
  dbHosts = dict()
  lbHosts = dict()
  for i in range(len(vm)):
      genHosts[i] = vm[i]
      if "web" in vm[i][4].lower():
        webHosts[i] = vm[i]
      elif "tun" in vm[i][4].lower():
        tunHosts[i] = vm[i]
      elif "str" in vm[i][4].lower():
        strHosts[i] = vm[i]
      elif "rtmp" in vm[i][4].lower():
        rtmpHosts[i] = vm[i]
      elif "db" in vm[i][4].lower():
        dbHosts[i] = vm[i]
      elif ("lb" in vm[i][4].lower()) or ("balancer" in vm[i][4].lower()) :
        lbHosts[i] = vm[i]