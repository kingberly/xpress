oemstr=$(sed -n '/oem_id/p' /var/www/SAT-CLOUDNVR/include/index_title.php | tail -c 6 | head -c 3)

sed -i -e 's|define("OEM_ID","T05");|define("OEM_ID","T04");|' /var/www/SAT-CLOUDNVR/manage/manage_share.php
sed -i -e 's|$_GET[\x21oem_id\x21]="T05";|$_GET[\x21oem_id\x21]="T04";|' /var/www/SAT-CLOUDNVR/backstage_login_tpe.php  