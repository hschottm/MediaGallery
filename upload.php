<?php

if(stristr($_GET['ilClientId'],'/') || stristr($_GET['ilClientId'],'.')) die('error!');
if(stristr($_GET['album_id'],'/') || stristr($_GET['album_id'],'..')) die('error!');

chdir('../../../../../../../data/'.$_GET['ilClientId'].'/lm_data');
if(!file_exists('cache')) {
    mkdir('cache', 0775);
    chmod('cache', 0775);
}
$fn = trim('cache/'.$_GET['album_id'].'_'.$_FILES['Filedata']['name']);

$ext = strtolower(substr($fn, strrpos($fn,'.')));
if($ext=='.jpg') {
    move_uploaded_file($_FILES['Filedata']['tmp_name'], $fn);
    chmod($fn, 0664);
}
?>