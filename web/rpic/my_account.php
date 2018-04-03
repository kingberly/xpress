<?php
include_once( "./include/global.php" );
include_once( "./include/utility.php" );

if (FORCE_HTTPS && !$_SERVER['HTTPS']) {
	header( 'Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ) ;
	exit(0);
}

if (!IsUserLoggedIn()) {header('Location: login.php'); exit;}
isset($_SESSION['google_openid']) ? $google_mode = true : $google_mode = false;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>User List</title>
<?php require( "./include/common_css_include.php" ); ?>
<?php require( "./include/common_js_include.php" ); ?>
<script type="text/javascript" src="js/panel.js"></script>
<script type="text/javascript" src="js/user_common.js"></script>
<script type="text/javascript" src="js/my_account.js?20120524"></script>
<script type="text/javascript">
	$(function(){	
		// display data
		RefreshDisplay();
		GetGoogleDriveEmail();
	});
</script>
</head>
<body>
<div id="toolbar_div">
	<div class="toolbarL">
		<?php
			if (!$google_mode)
				echo '<div id="btn_check" onClick="ApplyModifyMyAccount();"></div>';
		?>
        <div id="btn_refresh" onClick='DisplayMyAccount();'></div>
    </div>
    <div class="toolbarR">
<!---jinho use mode rpic instead personal----->
        <a onclick="parent.toActive('menu_setting');" href="device_list.php?mode=<?php if(isset($_SESSION["user_id"]) && !empty($_SESSION["user_id"])) echo "rpic"; else echo "public"; ?>">
            <div class="btn_sharef">
	            <?php echo _("Device"); ?>
            </div>
        </a>
        <a href="my_account.php?mode=<?php if(isset($_SESSION["user_id"]) && !empty($_SESSION["user_id"])) echo "personal"; else echo "public"; ?>">
            <div class="btn_sharef btn_sharef_active">
                <?php echo _("Account"); ?>
            </div>        
        </a>
    </div>
</div>

<div class="bg_div">
    <div id="my_account_list" class="left_list">
    	<div class="titlelist"><?php echo _("Account"); ?></div>
        <table>
            <tr style="display:none;">
                <td><div class="act_1" onClick='ApplyModifyMyAccount();'><?php echo _("Apply"); ?></div></td>
                <td><div class="act_1" onClick='DisplayMyAccount();'><?php echo _("Reset"); ?></div></td>
            </tr>
        </table>
        <table class="layouttable_2" cellspacing="1" cellpadding="1" id="my_account_config_table">
            <tr>
                <th width="100" scope="col"><?php echo _("Property"); ?></th>
                <th width="100" scope="col"><?php echo _("Content"); ?></th>
            </tr>
			<?php
			if (!$google_mode){
        //remark to disable password change
				echo '
				<tr class="even">
					<td>' . _("Username") . '</td>
					<td>
						<div id="name"></div>
						<input id="name-edit" class="full_size_input" type="hidden" autocomplete="off" maxlength="32" />
					</td>
				</tr>
				<tr class="odd">
					<td>' . _("Password") .'</td>
					<td>
						<input id="pwd-edit" class="full_size_input" disabled type="password" autocomplete="off" maxlength="32" placeholder="預設密碼,欲修改請向管理者申請" />
					</td>
				</tr>
				<tr class="even">
					<td>'. _("Confirm Password") .'</td>
					<td>
						<input id="pwd_confirm-edit" class="full_size_input" disabled type="password" autocomplete="off" maxlength="32" placeholder="預設密碼,欲修改請向管理者申請" />
					</td>
				</tr>
				';
			}
			?>
            <tr class="odd">
                <td><?php echo _("Email"); ?></td>
                <td>
                    <div id="reg_email"></div>
                    <input id="reg_email-edit" class="full_size_input" type="hidden" size="20" maxlength="128" />
                </td>
            </tr>
			<tr class="odd" id="tr-google-drive-email">
                <td><?php echo _("Google Drive Email"); ?></td>
                <td>
                    <div id="google_drive_email"></div>
                    <input id="google_drive_email-edit" class="full_size_input" type="hidden" size="20" maxlength="128" />
                </td>
            </tr>
            <tr class="even">
                <td><?php echo _("Register Date"); ?></td>
                <td id="reg_date-td"></td>
            </tr>
            <tr class="odd">
                <td><?php echo _("Login Date"); ?></td>
                <td id="login_date-td"></td>
            </tr>
            <tr class="even">
                <td><?php echo _("Expire Date"); ?></td>
                <td id="expire_date-td"></td>
            </tr>
        </table>
    </div>
</div>
<?php include_once("./include/tail.php"); ?>
</body>
</html>
