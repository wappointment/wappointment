#!/bin/bash

remove_unwanted(){
  for k in $(jq "$1 | .[]" "$mainfolder/build.json") 
  do
    temp="${k%\"}"
    temp="${temp#\"}"
    echo "[BUILD] removed $2/$temp"
     rm -Rf "$2/$temp"
  done
}

copy_folders(){
  for k in $(jq "$1 | .[]" "$mainfolder/build.json") 
  do
    temp="${k%\"}"
    temp="${temp#\"}"
    echo "[BUILD] copied folder $temp"
    cp -R "$mainfolder/$temp" "$2/$temp"
  done
}

copy_files(){
  for k in $(jq "$1 | .[]" "$mainfolder/build.json") 
  do
    temp="${k%\"}"
    temp="${temp#\"}"
    echo "[BUILD] copied file $temp"
    cp "$mainfolder/$temp" "$2/$temp"
  done
}


plugin_name='wappointment'
currentfolder=$PWD
tmpfolder="$currentfolder/$plugin_name"
mainfolder=$( dirname $currentfolder )
echo $tmpfolder
echo $currentfolder
echo $mainfolder

echo '[BUILD] Removing previous build'
rm -f $plugin_name.zip

echo '[BUILD] Creating temporary directory'
rm -Rf $tmpfolder
mkdir -p $tmpfolder

echo '[BUILD] look for translations'
wp i18n make-pot $mainfolder $mainfolder/wappointment.pot --domain="wappointment" --include="app,database" --exclude="included,dist,vendor,resources,bin,node_modules,docs" --debug

echo '[BUILD] Copy entire folders to tmp folder and move to it'
copy_folders '.must_have.folders' $tmpfolder
copy_folders '.process_only.folders' $tmpfolder

echo '[BUILD] Copy files to tmp folder'
copy_files '.must_have.files' $tmpfolder
copy_files '.process_only.files' $tmpfolder

echo '[BUILD] move to temp folder'
cd $tmpfolder

echo '[BUILD] Reinstall composer package'
composer install --no-dev

echo '[BUILD] Scoping PHP files'
php-scoper add-prefix -vvv

echo '[BUILD] remove base app and vendor folders'
rm -Rf "$tmpfolder/app"
rm -Rf "$tmpfolder/vendor"

echo '[BUILD] move processed app and vendor folders'
mv "$tmpfolder/build/"* "$tmpfolder"

echo '[BUILD] Composer dump autoload'
composer dumpautoload

echo '[BUILD] Remove unwanted vendors folders and files'
remove_unwanted '.must_remove.folders' $tmpfolder
remove_unwanted '.must_remove.files' $tmpfolder

echo '[BUILD] replace global __composer_autoload_files'
sed 's/__composer_autoload_files/__wappo_autoload_files/g' "$tmpfolder/vendor/composer/autoload_real.php" > "$tmpfolder/vendor/composer/autoload_realNEW.php"
rm "$tmpfolder/vendor/composer/autoload_real.php"
mv "$tmpfolder/vendor/composer/autoload_realNEW.php" "$tmpfolder/vendor/composer/autoload_real.php"

echo '[BUILD] Reinstall node_modules folder to avoid conflict'
npm i

echo '[BUILD] Generating dist folder'
npm run scss
echo '[BUILD] Generating dist folder'
npm run copy

echo '[BUILD] Generating dist folder'
npm run client:prod

echo '[BUILD] Remove unwanted folders and files'
remove_unwanted '.process_only.folders' $tmpfolder
remove_unwanted '.process_only.files' $tmpfolder
remove_unwanted '.must_remove.folders' $tmpfolder
remove_unwanted '.must_remove.files' $tmpfolder

echo '[BUILD] Creating final release zip'
cd $currentfolder
zip -r $plugin_name.zip "./$plugin_name"

echo '[BUILD] Build finished!'
