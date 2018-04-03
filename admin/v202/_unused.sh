#patch focus value in admin signin partner, not working after v2.0.2
config_info=$(grep 'checkInput' /var/www/qlync_admin/html/member/login.php)
if [ -z "$config_info" ]; then
#<input class=input_1 name=check_num value='Please enter 4 digits number' onfocus='javascript:checkInput(this);' onfocusout='javascript:checkInputExit(this);'>
  sed -i -e 's/<input class=input_1 name=check_num value=\x27Please enter 4 digits number\x27>/<input class=input_1 name=check_num value=\x27Please enter 4 digits number\x27 onfocus=\x27javascript:checkInput(this);\x27 onfocusout=\x27javascript:checkInputExit(this);\x27>/' /var/www/qlync_admin/html/member/login.php
  #after v2.0.2 with chinese
  #sed -i -e 's/<input class=input_1 name=check_num value=\x27".gettext("Please enter 4 digits number")."\x27>/<input class=input_1 name=check_num value=\x27".gettext("Please enter 4 digits number")."\x27 onfocus=\x27javascript:checkInput(this);\x27 onfocusout=\x27javascript:checkInputExit(this);\x27>/' /var/www/qlync_admin/html/member/login.php
  sed -i -e '
/<\/body>/ {
i\
echo "<script>";
i\
echo "function checkInput(obj)\{ if (obj.value==\\"Please enter 4 digits number\\") obj.value =\\"\\";\}";
i\
echo "function checkInputExit(obj)\{if (obj.value==\\"\\") obj.value =\\"Please enter 4 digits number\\";\}";
i\
echo "</script>";
  }' /var/www/qlync_admin/html/member/login.php
  myPrintInfo "patch login page javascript focus\n"
fi