#pldt default uuid: 3898a5ea-2934-4846-ba59-1f0f65f80203
#vnpt default uuid: 94f44a76-df1c-444f-b951-879cf91658f8
#vnpt default uuid2: d25f16c5-d00d-4e9b-a1b5-ab0de3c0007a
#??  f95c4faa-06fa-4145-bdec-07354838ed12  /boot
#fix vnpt defulat uuid: 38c0f46a-1a55-452f-b6a0-8abb38daccd6
pldtconfig=$(blkid | grep 3898a5ea-2934-4846-ba59-1f0f65f80203)
vnptconfig1=$(blkid | grep 94f44a76-df1c-444f-b951-879cf91658f8)
vnptconfig2=$(blkid | grep d25f16c5-d00d-4e9b-a1b5-ab0de3c0007a)
vnptconfig3=$(blkid | grep 38c0f46a-1a55-452f-b6a0-8abb38daccd6)

#if [ ! -z "$config" ]; then
uuid_change=0
if [ ! -z "$pldtconfig" ]; then
uuid_change=1
echo "detected pldt default uuid"
elif [ ! -z "$vnptconfig1" -o ! -z "$vnptconfig2" -o ! -z "$vnptconfig3" ]; then
uuid_change=1
echo "detected vnpt default uuid"
else
olduuid=$(cat /etc/fstab | grep "/               ext4" | awk '{print $1}')
echo "vm uuid: $olduuid"
fi

if [ "$1" = "recover" ]; then
  sed -i -e '/ext4/s/'"$2"'/UUID='"$3"'/' /etc/fstab
  sed -i -e 's/'"$2"'/'"$3"'/g' /boot/grub/grub.cfg
  echo "recovered to uuid $3 on /etc/fstab and /boot/grub/grub.cfg"
  exit
else
  echo "usage: sh changeuuid.sh recover currentid targetid"
fi

if [ ${uuid_change} -eq 1 ]; then
#user needs to be root to execute
  config=$(cat /etc/lsb-release  | grep DISTRIB_RELEASE | tail -c 6)
  if [ "$config" = "12.04" ]; then
    #ONLY WORKING on 12.04
    #get/print parameter uuidgen
    newuuid=$(uuidgen)
    #olduuid=$(cat /etc/fstab | grep ext4 | awk '{print $1}')
    olduuid=$(cat /etc/fstab | grep "/               ext4" | awk '{print $1}')
    if [ -z $olduuid ]; then
      echo "auto-detect ext4 / mount point fail."
      exit
    fi
    echo "replace $olduuid to $newuuid on ubuntu 12.04"
    tune2fs /dev/sda1 -U ${newuuid}
    #VNPT  #sudo tune2fs /dev/mapper/camera--vg-root -U ${newuuid}
    #UUID=8c27b52b-d40d-4dc6-a8cf-6c66fce9ab76 /               ext4    errors=remount-ro 0       1
    sed -i -e '/ext4/s/'"$olduuid"'/UUID='"$newuuid"'/' /etc/fstab
    update-grub
  else
    uuid=$(uuidgen)
    #olduuid=$(cat /etc/fstab | grep "ext4    errors=remount-ro" | awk '{print $1}')
    olduuid=$(cat /etc/fstab | grep "/               ext4" | awk '{print $1}')
    if [ -z $olduuid ]; then
      echo "auto-detect ext4 / mount point fail."
      exit
    fi
    echo "replace $olduuid to $uuid on ubuntu 14.04"
    root_disk=$(df /|grep /|cut -d' ' -f1)
    tune2fs -O ^uninit_bg $root_disk
    tune2fs -U $uuid $root_disk
    tune2fs -O +uninit_bg $root_disk
    sed -i -e '/ext4/s/'"$olduuid"'/UUID='"$uuid"'/' /etc/fstab
   #/boot/grub/grub.cfg
    LENolduuid=${#olduuid}
    LENolduuid=$(($LENolduuid - 4)) 
    olduuid=$(echo $olduuid | tail -c $LENolduuid)
    sed -i -e 's/'"$olduuid"'/'"$uuid"'/g' /boot/grub/grub.cfg
    config=$(cat /boot/grub/grub.cfg | grep $olduuid)
    if [ ! -z "$config" ]; then
      echo "Error!! uuid is not replaced!!\n$config"
    fi
  fi
else
  echo "Not Change. vm uuid is new uuid."
fi
#poweroff
#sync;sync;sync;reboot -h