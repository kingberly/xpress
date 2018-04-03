if [ ! -z $1 ]; then

if [ "${1}" -eq 1 ]; then
lanipall=$(ifconfig | awk '/inet addr/{print substr($2,6)}')
LocalIP=$(echo $lanipall | awk '{print $1}')
SUBNET3=$(echo $LocalIP | awk -F '.' 'BEGIN { OFS = ".";}{print $1,$2,$3}') 
  config_info=$(grep '127.0.0.1'  /var/www/SAT-CLOUDNVR/Mailer/configuration.php)
  config_info2=$(grep '$SUBNET3'  /var/www/SAT-CLOUDNVR/Mailer/configuration.php)
  if [ ! -z "$config_info" -o ! -z "$config_info2" ]; then
    echo "NOTICE: Mailer IP is set as LAN IP ($config_info $config_info2)"
  else
    echo "Mailer IP is set as External IP"
  fi
    config_info1=$(grep '\/\/\$mail->SMTPAuth = true;'  /var/www/SAT-CLOUDNVR/Mailer/mailer.php)
    if [ -z "$config_info1" ]; then #-z test if its empty string
      sed -i -e 's/$mail->SMTPAuth = true;/\/\/$mail->SMTPAuth = true;/'  /var/www/SAT-CLOUDNVR/Mailer/mailer.php
      echo "Force disable SMTP Authentication on Mailer for Mail Server\n"
    else
      echo "SMTP Authentication on Mailer Disabled.\n"
    fi
    config_info2=$(grep '\/\/\$mail->SMTPAuth = true;'  /var/www/SAT-CLOUDNVR/include/mail_class.php)
    if [ -z "$config_info2" ]; then #-z test if its empty string
      sed -i -e 's/$mail->SMTPAuth = true;/\/\/$mail->SMTPAuth = true;/' /var/www/SAT-CLOUDNVR/include/mail_class.php
      echo "Force disable SMTP Authentication on qlync mailer for Mail Server\n"
    else
      echo "SMTP Authentication on qlync mailer Disabled.\n"
    fi
    echo "To Enable SMTP Authentication, type 'sh patch_zeeSMTP.sh 0'\n"

else
  #for web notificaiton mailer
  sed -i -e 's/\/\/$mail->SMTPAuth = true;/$mail->SMTPAuth = true;/'  /var/www/SAT-CLOUDNVR/Mailer/mailer.php
  #for admin to set create account mailer
  sed -i -e 's/\/\/$mail->SMTPAuth = true;/$mail->SMTPAuth = true;/' /var/www/SAT-CLOUDNVR/include/mail_class.php
  echo "Enable SMTP Authentication\n"  
fi

else
  echo "usage: (disable)sh patch_internalSMTP.sh 1\nsh patch_internalSMTP.sh 0"
fi