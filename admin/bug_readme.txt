[patch 20150430]
Admin patches:


login.php		/login auto focus
oem.php			/display email test result
online_list.php		/fix QXP-286 online time display issue
isat_server.php		/patch AA policy to Manual HA
			/remove HR BR display
log.php			/limit log display to 200
web_server.php		/remove HR BR display
partner_server.php	/remove HR BR display

plugin/service/_auth_.inc	/fix html security
vminfo.py		/replace with correct oem_id
-----------------------------------------------

admin.php		/Update for Profile1

## v2.0.2 fixed
#daily_update_device.php	/QXP-298 [Once][PLDT] database qlync.account_device automatically clean up
#v2.0.2 FIXED patch QXP-298 [Once][PLDT] database qlync.account_device automatically clean up
#cp v151/daily_update_device.php /var/www/qlync_admin/html/common/
#myPrintInfo "patch QXP-298"

delete_user.php		/Fix email/user/pwd format check

menu_update.php		/Fix menu order issue (string to number)

account_list.php    /Fix QXP-247 MAC query issue on Account Device Page

auto_model_list_feature.php    /Fix QXP-165 New Firmware feature will not automatically updated

delete_user.php    /Fix QXP-294 email/name/password check on Add/Delete End User page

mac_check.php    /Add Service Package and Tunnel/Stream server info in the Activation Code Look Up Tool page.

partner_server.php    /Add PHP,Apache, Admin server version information in the Admin Server page.

vlc_server.php        /Fix QXP-296 stream server version and hostname display is missing
		      /replace AA policy to Manual HA


