<#1>
<?php
if (!$ilDB->tableExists('rep_robj_xmg_filedata'))
{
	$fields = array (
	'xmg_id'    => array(
		'type' => 'integer',
		'length'  => 4,
		'notnull' => true,
		'default' => 0),
	'media_id'   => array(
		'type' => 'text',
		'notnull' => false,
		'length' => 255,
		'fixed' => false,
		'default' => NULL),
	'topic'   => array(
		'type' => 'text',
		'notnull' => false,
		'length' => 255,
		'fixed' => false,
		'default' => NULL),
	'title'   => array(
		'type' => 'text',
		'notnull' => false,
		'length' => 255,
		'fixed' => false,
		'default' => NULL),
	'description'   => array(
		'type' => 'text',
		'notnull' => false,
		'length' => 255,
		'fixed' => false,
		'default' => NULL),
	'filename'   => array(
		'type' => 'text',
		'notnull' => true,
		'length' => 255,
		'fixed' => false)
	);
	$ilDB->createTable('rep_robj_xmg_filedata', $fields);
	$ilDB->addIndex("rep_robj_xmg_filedata", array("xmg_id"), "i1");
	$ilDB->addIndex("rep_robj_xmg_filedata", array("filename"), "i2");
}
?>
<#2>
<?php

include_once './Services/Administration/classes/class.ilSetting.php';
$setting = new ilSetting("xmg");
$setting->set('ext_img', 'jpg,jpeg,png');
$setting->set('ext_vid', 'mov,avi,m4v,mp4');
$setting->set('ext_aud', 'mp3');
$setting->set('sort', 'entry');

?>
<#3>
<?php

if(!$ilDB->tableColumnExists('rep_robj_xmg_filedata', 'custom'))
{
	$ilDB->addTableColumn("rep_robj_xmg_filedata",	"custom",
		array(
			"type" => "float",
			"notnull" => true,
			"default" => 0)
	);
}

?>
<#4>
<?php
if (!$ilDB->tableExists('rep_robj_xmg_object'))
{
	$fields = array (
	'obj_fi'    => array(
		'type' => 'integer',
		'length'  => 4,
		'notnull' => true,
		'default' => 0),
	'sortorder'   => array(
		'type' => 'text',
		'notnull' => false,
		'length' => 255,
		'fixed' => false,
		'default' => NULL),
	'show_title'    => array(
		'type' => 'integer',
		'length'  => 1,
		'notnull' => true,
		'default' => 0)
	);
	$ilDB->createTable('rep_robj_xmg_object', $fields);
	$ilDB->addIndex("rep_robj_xmg_object", array("obj_fi"), "i1");
}
?>
<#5>
<?php

if(!$ilDB->tableColumnExists('rep_robj_xmg_filedata', 'mtype'))
{
	$ilDB->addTableColumn("rep_robj_xmg_filedata",	"mtype",
		array(
			'type' => 'text',
			'notnull' => false,
			'length' => 255,
			'fixed' => false,
			'default' => NULL)
	);
}

?>
<#6>
<?php

if(!$ilDB->tableColumnExists('rep_robj_xmg_filedata', 'width'))
{
	$ilDB->addTableColumn("rep_robj_xmg_filedata",	"width",
		array(
			'type' => 'integer',
			'length'  => 4,
			'notnull' => true,
			'default' => 0)
	);
	$ilDB->addTableColumn("rep_robj_xmg_filedata",	"height",
		array(
			'type' => 'integer',
			'length'  => 4,
			'notnull' => true,
			'default' => 0)
	);
}

?>
<#7>
<?php
if (!$ilDB->tableExists('rep_robj_xmg_downloads'))
{
	$fields = array (
	'xmg_id'    => array(
		'type' => 'integer',
		'length'  => 4,
		'notnull' => true,
		'default' => 0),
	'filename'   => array(
		'type' => 'text',
		'notnull' => true,
		'length' => 255,
		'fixed' => false)
	);
	$ilDB->createTable('rep_robj_xmg_downloads', $fields);
	$ilDB->addIndex("rep_robj_xmg_downloads", array("xmg_id"), "i1");
}
?>
<#8>
<?php
if(!$ilDB->tableColumnExists('rep_robj_xmg_object', 'download'))
{
	$ilDB->addTableColumn("rep_robj_xmg_object",	"download",
		array(
			'type' => 'integer',
			'length'  => 2,
			'notnull' => true,
			'default' => 0)
	);
}

?>
<#9>
<?php
if(!$ilDB->tableColumnExists('rep_robj_xmg_filedata', 'pwidth'))
{
	$ilDB->addTableColumn("rep_robj_xmg_filedata",	"pwidth",
		array(
			'type' => 'integer',
			'length'  => 4,
			'notnull' => true,
			'default' => 0)
	);
	$ilDB->addTableColumn("rep_robj_xmg_filedata",	"pheight",
		array(
			'type' => 'integer',
			'length'  => 4,
			'notnull' => true,
			'default' => 0)
	);
}

?>
<#10>
<?php
if(!$ilDB->tableColumnExists('rep_robj_xmg_filedata', 'pfilename'))
{
	$ilDB->addTableColumn("rep_robj_xmg_filedata",	"pfilename",
		array(
			'type' => 'text',
			'notnull' => false,
			'length' => 255,
			'fixed' => false)
	);
}

?>
