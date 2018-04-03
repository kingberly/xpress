#<!---jinho add for debug-->  
#<script type="text/javascript" src="js/jquery-migrate-1.4.1.js"></script>
#sed -n '/pattern/p' file

#fix main_menu a bug
sed -i -e 's|var a = $("#main_menu a\[href=" + doc + "]");|var a = $("#main_menu a\[href=\x27 + doc + \x27]");|' /var/www/SAT-CLOUDNVR/js/index.js
sed -i -e 's|var a = $("#main_menu a\[href=" + doc + "]");|var a = $("#main_menu a\[href=\x27 + doc + \x27]");|' /var/www/SAT-CLOUDNVR/iveda/js/index.js
sed -i -e 's|if ($.browser.msie){|if (/msie/.test(navigator.userAgent.toLowerCase())){|'  /var/www/SAT-CLOUDNVR/device_list.php
#disable SR
sed -i -e 's|<div class="btn_sharef" onClick=\x27ApplyModifySchedule();\x27 \/><?php echo _("Apply"); ?><\/div>|<!--div class="btn_sharef" onClick=\x27ApplyModifySchedule();\x27 \/><?php echo _("Apply"); ?><\/div-->|' /var/www/SAT-CLOUDNVR/device.php
sed -i -e 's|<div class="btn_sharef" onClick=\x27ResetScheduleDialog();\x27 \/><?php echo _("Reset"); ?><\/div>|<!--div class="btn_sharef" onClick=\x27ResetScheduleDialog();\x27 \/><?php echo _("Reset"); ?><\/div-->|' /var/www/SAT-CLOUDNVR/device.php
sed -i -e 's|<?php require(\x22.\/ui_component\/schedule_config_dialog.php\x22); ?>|<?php \/\/require(\x22.\/ui_component\/schedule_config_dialog.php\x22); ?>|' /var/www/SAT-CLOUDNVR/device.php
config=$(grep "//ResetScheduleDialog" /var/www/SAT-CLOUDNVR/js/device.js)
if [ -z "$config" ]; then
sed -i -e 's|ResetScheduleDialog();|//ResetScheduleDialog();|g' /var/www/SAT-CLOUDNVR/js/device.js
fi

if [ -f "/var/www/SAT-CLOUDNVR/js/jquery-1.7.2.min.js" ]; then
  cp -avr SAT-CLOUDNVR/ /var/www/
  if [ -f "/var/www/SAT-CLOUDNVR/js/jquery-1.12.4.min.js" ]; then
    sed -i -e 's|jquery-1.7.2.min.js|jquery-1.12.4.min.js|' /var/www/SAT-CLOUDNVR/index.php
    sed -i -e 's|<script type="text\/javascript" src="js\/jquery.tools.overlay.min.js"><\/script>|<script type="text\/javascript" src="js\/jquery.tools.overlay.js"><\/script>|'  /var/www/SAT-CLOUDNVR/index.php
    sed -i -e 's|jquery-1.7.2.min.js|jquery-1.12.4.min.js|' /var/www/SAT-CLOUDNVR/iveda/index.php
    sed -i -e 's|<script type="text\/javascript" src="<?php echo ROOT_URL ?>js\/jquery.tools.overlay.min.js"><\/script>|<script type="text\/javascript" src="<?php echo ROOT_URL ?>js\/jquery.tools.overlay.js"><\/script>|'  /var/www/SAT-CLOUDNVR/iveda/index.php
    sed -i -e 's|jquery-1.7.2.min.js|jquery-1.12.4.min.js|' /var/www/SAT-CLOUDNVR/logout.php
    sed -i -e 's|jquery-1.7.2.min.js|jquery-1.12.4.min.js|' /var/www/SAT-CLOUDNVR/info_system_test.html
    sed -i -e 's|jquery-1.7.2.min.js|jquery-1.12.4.min.js|' /var/www/SAT-CLOUDNVR/p2p_interface.php
    sed -i -e 's|jquery-1.7.2.min.js|jquery-1.12.4.min.js|' /var/www/SAT-CLOUDNVR/redirect_login.php
    sed -i -e 's|jquery-1.7.2.min.js|jquery-1.12.4.min.js|' /var/www/SAT-CLOUDNVR/manage/camera_model_list.php
    sed -i -e 's|jquery-1.7.2.min.js|jquery-1.12.4.min.js|' /var/www/SAT-CLOUDNVR/push_notify/common_js_include.php
    #grep -rl 'jquery-1.7.2.min.js' /var/www/SAT-CLOUDNVR/ | sudo xargs sed -i 's/jquery-1.7.2.min.js/jquery-1.12.4.min.js/g'
    rm /var/www/SAT-CLOUDNVR/js/jquery-1.7.2.min.js
    echo "replace and delete jquery-1.7.2"
  else
    echo "copy jquery-1.12.4 FAIL!!"
  fi
else
  echo "skip jquery-1.12.4 upgrade! jquery-1.7.2 not exist."
fi
