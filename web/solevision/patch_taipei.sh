addMySupportPage(){
    config_info=$(grep '<!--jinho fix-->' /var/www/SAT-CLOUDNVR/index.php)
    if [ -z "$config_info" ]; then
      sed -i -e ' 
    /<li><a id="menu_share" class="navi_link open_page bottom_menu" href="shared_matrix.php?mode=<?php echo $mode;?>" onClick="toActive(this.id);"><?php echo _("Share");?><\/a><\/li>/ {
    c\
    <li><a id="menu_share" class="navi_link open_page bottom_menu" href="shared_matrix.php?mode=<?php echo $mode;?>" onClick="toActive(this.id);"><?php echo _("Share");?><\/a><\/li>\n<!--jinho fix-->\n<li><a id="menu_share" class="navi_link open_page bottom_menu" href="<?php echo "'"$2"'";?>" onClick="toActive(this.id);"><?php echo _("'"$1"'");?><\/a><\/li>
    }' /var/www/SAT-CLOUDNVR/index.php
    fi
}

addMySupportPage "使用手冊" "taipei/rpic_web_support.pdf"