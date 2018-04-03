<?php
session_start();
require '../include/global.php';
include_once '../include/index_title.php';
?>
<!DOCTYPE HTML>
<html leng="en">
    <head>
        <title><?php echo _('Download');?></title>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
        <link href="download.css" rel="stylesheet">
        <script src="//code.jquery.com/jquery-1.12.4.min.js"></script>
        <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
        <script src="//cdnjs.cloudflare.com/ajax/libs/lodash.js/4.16.6/lodash.min.js"></script>
    </head>
    <body>
<div class="container">
    <div class="row">
        <div class="col-md-4 col-md-offset-4 col-sm-6 col-sm-offset-3">
    <h2 class="form-signin-heading"><?php echo _('下載');?></h2>
    <form class="form-signin" _lpchecked="1" >
        <label for="inputUser" class="sr-only"><?php echo _('Username');?></label>
        <input id="inputUser" class="form-control" name="user" placeholder="<?php echo _('Username');?>" required="" autofocus="" type="text">
        <label for="inputPassword" class="sr-only"><?php echo _('Password');?></label>
        <input id="inputPassword" class="form-control" name="pwd" placeholder="<?php echo _('Password');?>" required="" type="password" AUTOCOMPLETE="OFF">
        <div class="alert-holder">&nbsp;</div>
<?php    //jinho add login count
		//if (!isset($_SESSION['timeout'])) $_SESSION['timeout'] = 0;
		if ($_SESSION['timeout'] + 15 * 60 < time()) $_SESSION["login_err"] = 0;//over 15 mins
		if (intval($_SESSION["login_err"]) < 5) { 
?>
        <button class="btn btn-lg btn-primary btn-block" data-loading-text="<?php echo _('Loading...');?>" type="submit"><?php echo _('Login');?></button>
<?php    //jinho add login count
		}else{
			echo "<font color=red>Fail too many times. Please Try again 15 mins later!</font>";
		}
?>
    </form>
    <div id="download"></div>
        </div>
    </div>
</div>
        <!---jinho added----->
        <script type="text/javascript">
				var login_err = <?php if (isset($_SESSION["login_err"])) echo $_SESSION["login_err"]; else echo "0";?>;
				</script>
        <script src="download.js"></script>

    </body>
</html>
