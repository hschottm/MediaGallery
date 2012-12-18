<?php

include_once("./Services/Component/classes/class.ilPluginConfigGUI.php");
 
/**
 * MediaGallery configuration user interface class
 *
 * @author Helmut Schottmüller <ilias@aurealis.de>
 * @version $Id$
 *
 */
class ilMediaGalleryConfigGUI extends ilPluginConfigGUI
{
	/**
	* Handles all commmands, default is "configure"
	*/
	function performCommand($cmd)
	{

		switch ($cmd)
		{
			case "configure":
			case "save":
				$this->$cmd();
				break;

		}
	}

	/**
	 * Configure screen
	 */
	function configure()
	{
		global $tpl;

		$form = $this->initConfigurationForm();
		$tpl->setContent($form->getHTML());
	}
	
	//
	// From here on, this is just an gallery implementation using
	// a standard form (without saving anything)
	//
	
	/**
	 * Init configuration form.
	 *
	 * @return object form object
	 */
	public function initConfigurationForm()
	{
		global $lng, $ilCtrl;
		
		$pl = $this->getPluginObject();
		$pl->includeClass("class.ilObjMediaGallery.php");
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->addCommandButton("save", $lng->txt("save"));
	                
		$form->setTitle($pl->txt("mediagallery_plugin_configuration"));
		$form->setFormAction($ilCtrl->getFormAction($this));
	
		$ext_img = new ilTextInputGUI($pl->txt("ext_img"), "ext_img");
		$ext_img->setValue(ilObjMediaGallery::_getConfigurationValue('ext_img'));
		$ext_img->setRequired(TRUE);
		$form->addItem($ext_img);
		
		$ext_vid = new ilTextInputGUI($pl->txt("ext_vid"), "ext_vid");
		$ext_vid->setValue(ilObjMediaGallery::_getConfigurationValue('ext_vid'));
		$ext_vid->setRequired(TRUE);
		$form->addItem($ext_vid);
		
		$ext_aud = new ilTextInputGUI($pl->txt("ext_aud"), "ext_aud");
		$ext_aud->setValue(ilObjMediaGallery::_getConfigurationValue('ext_aud'));
		$ext_aud->setRequired(TRUE);
		$form->addItem($ext_aud);
		
		$ext_oth = new ilTextInputGUI($pl->txt("ext_oth"), "ext_oth");
		$ext_oth->setValue(ilObjMediaGallery::_getConfigurationValue('ext_oth'));
		$ext_oth->setRequired(TRUE);
		$form->addItem($ext_oth);
		
		return $form;
	}
	
	/**
	 * Save form input (currently does not save anything to db)
	 *
	 */
	public function save()
	{
		global $tpl, $lng, $ilCtrl;
	
		$pl = $this->getPluginObject();
		
		$form = $this->initConfigurationForm();
		if ($form->checkInput())
		{
			$pl->includeClass("class.ilObjMediaGallery.php");
			ilObjMediaGallery::_setConfiguration('ext_img', $_POST['ext_img']);
			ilObjMediaGallery::_setConfiguration('ext_vid', $_POST['ext_vid']);
			ilObjMediaGallery::_setConfiguration('ext_aud', $_POST['ext_aud']);
			ilObjMediaGallery::_setConfiguration('ext_oth', $_POST['ext_oth']);
			ilUtil::sendSuccess($pl->txt("configuration_saved"), true);
			$ilCtrl->redirect($this, "configure");
		}
		else
		{
			$form->setValuesByPost();
			$tpl->setContent($form->getHtml());
		}
	}

}
?>
