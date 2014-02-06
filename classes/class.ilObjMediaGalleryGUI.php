<?php

include_once("./Services/Repository/classes/class.ilObjectPluginGUI.php");

/**
* User Interface class for gallery repository object.
*
* User interface classes process GET and POST parameter and call
* application classes to fulfill certain tasks.
*
* @author Helmut Schottmüller <ilias@aurealis.de>
*
* $Id$
*
* Integration into control structure:
* - The GUI class is called by ilRepositoryGUI
* - GUI classes used by this class are ilPermissionGUI (provides the rbac
*   screens) and ilInfoScreenGUI (handles the info screen).
*
* @ilCtrl_isCalledBy ilObjMediaGalleryGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
* @ilCtrl_Calls ilObjMediaGalleryGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI
* @ilCtrl_Calls ilObjMediaGalleryGUI: ilCommonActionDispatcherGUI
*
*/
class ilObjMediaGalleryGUI extends ilObjectPluginGUI
{
	protected $plugin;
	protected $sortkey;
	
	/**
	* Initialisation
	*/
	protected function afterConstructor()
	{
		// anything needed after object has been constructed
		// - gallery: append my_id GET parameter to each request
		//   $ilCtrl->saveParameter($this, array("my_id"));
		include_once "./Services/Component/classes/class.ilPlugin.php";
		$this->plugin = ilPlugin::getPluginObject(IL_COMP_SERVICE, "Repository", "robj", "MediaGallery");
	}

	/**
	* Get type.
	*/
	final function getType()
	{
		return "xmg";
	}

	/**
	* Handles all commmands of this class, centralizes permission checks
  */
	function performCommand($cmd)
	{
		switch ($cmd)
		{
			case "editProperties":		// list all commands that need write permission here
			case "mediafiles":
			case "uploadFile":
			case "upload":
			case "deleteFile":
			case "createArchiveFromSelection":
			case "renameArchiveFilename":
			case "setArchiveFilename":
			case "changeArchiveFilename":
			case "saveAllFileData":
			case "updateProperties":
			case "filterMedia":
			case "addPreview":
			case "deletePreview":
			case "uploadPreview":
			case "downloadOriginal":
			case "downloadOther":
			case "resetFilterMedia":
			case "createMissingPreviews":
			case "archives":
			case "deleteArchive":
			case "saveAllArchiveData":
			case "createNewArchive":
				$this->checkPermission("write");
				$this->$cmd();
				break;
			case "download":
			case "gallery":			// list all commands that need read permission here
				$this->checkPermission("read");
				$this->$cmd();
				break;
		}
	}

	/**
	* After object has been created -> jump to this command
	*/
	function getAfterCreationCmd()
	{
		return "editProperties";
	}

	/**
	* Get standard command
  */
	function getStandardCmd()
	{
		return "gallery";
	}


	/**
	* show information screen
	*/
	function infoScreen()
	{
		global $ilAccess, $ilUser, $lng, $ilCtrl, $tpl, $ilTabs;

		$ilTabs->setTabActive("info_short");

		$this->checkPermission("visible");

		include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
		$info = new ilInfoScreenGUI($this);

		$info->addSection($this->txt("plugininfo"));
		$info->addProperty('Name', 'Media Gallery');
		$info->addProperty('Version', xmg_version);
		$info->addProperty('Developer', 'Helmut Schottmüller');
		$info->addProperty('Kontakt', 'ilias@aurealis.de');
		$info->addProperty('&nbsp;', 'Aurealis');
		$info->addProperty('&nbsp;', '');
		$info->addProperty('&nbsp;', "http://www.aurealis.de");



		$info->enablePrivateNotes();

		// general information
		$lng->loadLanguageModule("meta");

		$this->addInfoItems($info);


		// forward the command
		$ret = $ilCtrl->forwardCommand($info);


		//$tpl->setContent($ret);
	}
	//
	// DISPLAY TABS
	//

	protected function setSubTabs($cmd)
	{
		global $ilTabs;
	
		switch ($cmd)
		{
			case "mediafiles":
				$ilTabs->addSubTabTarget("list",
					$this->ctrl->getLinkTarget($this, "mediafiles"),
					array("mediafiles"),
					"", "");
			case 'upload':
				$ilTabs->addSubTabTarget("upload",
					$this->ctrl->getLinkTarget($this, "upload"),
					array("upload", "uploadPreview", "addPreview"),
					"", "");
				break;
		}
	}

	/**
	* Set tabs
	*/
	function setTabs()
	{
		global $ilTabs, $ilCtrl, $ilAccess;

		// tab for the "show content" command
		if ($ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			$ilTabs->addTab("mediafiles", $this->txt("mediafiles"), $ilCtrl->getLinkTarget($this, "mediafiles"));
		}

		if ($ilAccess->checkAccess("read", "", $this->object->getRefId()))
		{
			$ilTabs->addTab("gallery", $this->txt("gallery"), $ilCtrl->getLinkTarget($this, "gallery"));
		}

		if ($ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			$ilTabs->addTab("archives", $this->txt("archives"), $ilCtrl->getLinkTarget($this, "archives"));
		}

		// standard info screen tab
		$this->addInfoTab();

		// a "properties" tab
		if ($ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			$ilTabs->addTab("properties", $this->txt("properties"), $ilCtrl->getLinkTarget($this, "editProperties"));
		}

		// standard epermission tab
		$this->addPermissionTab();
	}


	// THE FOLLOWING METHODS IMPLEMENT SOME EXAMPLE COMMANDS WITH COMMON FEATURES
	// YOU MAY REMOVE THEM COMPLETELY AND REPLACE THEM WITH YOUR OWN METHODS.

	//
	// Edit properties form
	//

	/**
	* Edit Properties. This commands uses the form class to display an input form.
	*/
	function editProperties()
	{
		global $tpl, $ilTabs;

		$ilTabs->activateTab("properties");
		$this->initPropertiesForm();
		$this->getPropertiesValues();
		$tpl->setContent($this->form->getHTML());
	}

	/**
	* Init  form.
	*
	* @param        int        $a_mode        Edit Mode
	*/
	public function initPropertiesForm()
	{
		global $ilCtrl;

		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();

		// title
		$ti = new ilTextInputGUI($this->txt("title"), "title");
		$ti->setRequired(true);
		$this->form->addItem($ti);

		// description
		$ta = new ilTextAreaInputGUI($this->txt("description"), "desc");
		$this->form->addItem($ta);

		// sort
		$so = new ilSelectInputGUI($this->plugin->txt("sort_order"), "sort");
		$so->setOptions(
			array(
				'entry' => $this->txt('filename'),
				'media_id' => $this->txt('id'),
				'topic' => $this->txt('topic'),
				'title' => $this->txt('title'),
				'description' => $this->txt('description'),
				'custom' => $this->txt('individual'),
			)
		);
		$this->form->addItem($so);

		$st = new ilCheckboxInputGUI($this->txt('show_title'), 'show_title');
		$st->setInfo($this->txt("show_title_description"));
		$this->form->addItem($st);

		$sd = new ilCheckboxInputGUI($this->txt('show_download'), 'show_download');
		$sd->setInfo($this->txt("show_download_description"));
		$this->form->addItem($sd);

		// theme
		$theme = new ilSelectInputGUI($this->plugin->txt("gallery_theme"), "theme");
		$theme_options = $this->object->getGalleryThemes();
		$theme->setOptions($theme_options);
		$this->form->addItem($theme);

		$this->form->addCommandButton("updateProperties", $this->txt("save"));

		$this->form->setTitle($this->txt("edit_properties"));
		$this->form->setFormAction($ilCtrl->getFormAction($this));
	}

	/**
	* Get values for edit properties form
	*/
	function getPropertiesValues()
	{
		$values["title"] = $this->object->getTitle();
		$values["desc"] = $this->object->getDescription();
		$values["sort"] = $this->object->getSortOrder();
		$values["show_download"] = $this->object->getDownload();
		$values["show_title"] = $this->object->getShowTitle();
		$values["theme"] = $this->object->getTheme();
		$this->form->setValuesByArray($values);
	}

	/**
	* Update properties
	*/
	public function updateProperties()
	{
		global $tpl, $lng, $ilCtrl;

		$this->initPropertiesForm();
		if ($this->form->checkInput())
		{
			$this->object->setTitle($this->form->getInput("title"));
			$this->object->setDescription($this->form->getInput("desc"));
			$this->object->setSortOrder($this->form->getInput("sort"));
			$this->object->setShowTitle($this->form->getInput("show_title"));
			$this->object->setDownload($this->form->getInput("show_download"));
			$this->object->setTheme($this->form->getInput("theme"));
			$this->object->update();
			ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
			$ilCtrl->redirect($this, "editProperties");
		}

		$this->form->setValuesByPost();
		$tpl->setContent($this->form->getHtml());
	}
	
	function saveAllArchiveData()
	{
		$data = array();
		if (is_array($_POST['download']))
		{
			$data = array_keys($_POST['download']);
		}
		$this->object->saveArchiveData($data);
		ilUtil::sendSuccess($this->plugin->txt('archive_data_saved'), true);
		$this->ctrl->redirect($this, 'archives');
	}
	
	function deleteArchive()
	{
		if (!is_array($_POST['file']))
		{
			ilUtil::sendInfo($this->plugin->txt('please_select_archive_to_delete'), true);
		}
		else
		{
			foreach ($_POST['file'] as $file)
			{
				$this->object->deleteArchive($file);
			}
			ilUtil::sendSuccess(sprintf((count($_POST['file']) == 1) ? $this->plugin->txt('archive_deleted') : $this->plugin->txt('archives_deleted'), count($_POST['file'])), true);
		}
		$this->ctrl->redirect($this, 'archives');
	}
	
	function createNewArchive()
	{
		ilUtil::zip($this->object->getPath(LOCATION_ORIGINALS), $this->object->getPath(LOCATION_DOWNLOADS) . ilUtil::getASCIIFilename(sprintf("%s_%s.zip", $this->object->getTitle(), time())), true);
		$this->ctrl->redirect($this, "archives");
	}
	
	function archives()
	{
		global $ilTabs, $ilToolbar, $ilCtrl;
	
		unset($_SESSION['archiveFilename']);
		$ilTabs->activateTab("archives");
		$this->plugin->includeClass("class.ilMediaFileDownloadArchivesTableGUI.php");
		$table_gui = new ilMediaFileDownloadArchivesTableGUI($this, 'archives');
		$archives = $this->object->getArchives();
		$table_gui->setData($archives);

		$ilToolbar->addButton($this->plugin->txt("new_archive"), $ilCtrl->getLinkTarget($this, "createNewArchive"));
		$ilToolbar->setFormAction($ilCtrl->getFormAction($this));

		$this->tpl->setVariable('ADM_CONTENT', $table_gui->getHTML());	
	}

	function download()
	{
		ilUtil::deliverFile($this->object->getPath(LOCATION_DOWNLOADS).$_POST['archive'], $_POST['archive']);
		$this->ctrl->redirect($this, 'gallery');
	}
	
	function gallerysort($x, $y) 
	{
		return strnatcasecmp($x[$this->sortkey], $y[$this->sortkey]);
	} 

	public function gallery()
	{
		global $ilTabs;
	
		$ilTabs->activateTab("gallery");
		$this->tpl->addCss($this->plugin->getStyleSheetLocation("xmg.css"));
		$this->tpl->addCss($this->plugin->getDirectory() . "/js/prettyphoto_3.1.5/css/prettyPhoto.css");
		$this->tpl->addJavascript($this->plugin->getDirectory() . "/js/prettyphoto_3.1.5/js/jquery.prettyPhoto.js");
		$this->tpl->addJavascript($this->plugin->getDirectory() . "/js/html5media-master/domready.js");
		$this->tpl->addJavascript($this->plugin->getDirectory() . "/js/html5media-master/flowplayer.js");
		$this->tpl->addJavascript($this->plugin->getDirectory() . "/js/html5media-master/html5media.js");
		$mediafiles = $this->object->getMediaFiles();
		$template = $this->plugin->getTemplate("tpl.gallery.html");
		$counter = 0;
		$this->sortkey = $this->object->getSortOrder();
		if (!strlen($this->sortkey)) $this->sortkey = 'entry';
		uasort($mediafiles, array($this, 'gallerysort'));
		foreach ($mediafiles as $fn => $fdata)
		{
			$counter++;
			if ($this->object->isImage($fn))
			{
				$tpl_element = $this->plugin->getTemplate("tpl.gallery.img.html");
				$iwidth = $fdata['width'];
				$iheight = $fdata['height'];
				if ($fdata['pwidth'] > 0) 
				{
					$iwidth = $fdata['pwidth'];
					$iheight = $fdata['pheight'];
				}
				if ($iwidth > 0 && $iheight > 0)
				{
					$scale = $this->object->scaleDimensions($iwidth, $iheight, 150);
					$width = $scale['width'];
					$height = $scale['height'];
					$tpl_element->setCurrentBlock('size');
					$tpl_element->setVariable('WIDTH', $width+2);
					$tpl_element->setVariable('HEIGHT', $height+2);
					$tpl_element->setVariable('MARGIN_TOP', round((158.0-$height)/2.0));
					$tpl_element->setVariable('MARGIN_LEFT', round((158.0-$width)/2.0));
					$tpl_element->parseCurrentBlock();
					$tpl_element->setCurrentBlock('imgsize');
					$tpl_element->setVariable('IMG_WIDTH', $width);
					$tpl_element->setVariable('IMG_HEIGHT', $height);
					$tpl_element->parseCurrentBlock();
				}
				else
				{
					$tpl_element->setCurrentBlock('size');
					$tpl_element->setVariable('WIDTH', "150");
					$tpl_element->setVariable('HEIGHT', "150");
					$tpl_element->setVariable('MARGIN_TOP', "4");
					$tpl_element->setVariable('MARGIN_LEFT', "4");
					$tpl_element->parseCurrentBlock();
				}
				$tpl_element->setVariable('URL_FULLSCREEN', $this->object->getPathWeb(LOCATION_SIZE_LARGE) . $fn . "?t=" . time());
				$tpl_element->setVariable('CAPTION', ilUtil::prepareFormOutput($fdata['description']));
				if ($fdata['pwidth'] > 0)
				{
					$tpl_element->setVariable('URL_THUMBNAIL', $this->object->getPathWeb(LOCATION_PREVIEWS) . $fdata['pfilename'] . "?t=" . time());
				}
				else
				{
					$tpl_element->setVariable('URL_THUMBNAIL', $this->object->getPathWeb(LOCATION_THUMBS) . $fn . "?t=" . time());
				}
				$tpl_element->setVariable('ALT_THUMBNAIL', ilUtil::prepareFormOutput($fdata['title']));
			}
			else if ($this->object->isAudio($fn))
			{
				$tpl_element = $this->plugin->getTemplate("tpl.gallery.aud.html");
				$iwidth = $fdata['width'];
				$iheight = $fdata['height'];
				if ($fdata['pwidth'] > 0) 
				{
					$iwidth = $fdata['pwidth'];
					$iheight = $fdata['pheight'];
				}
				if ($iwidth > 0 && $iheight > 0)
				{
					$scale = $this->object->scaleDimensions($iwidth, $iheight, 150);
					$width = $scale['width'];
					$height = $scale['height'];
					$tpl_element->setCurrentBlock('size');
					$tpl_element->setVariable('WIDTH', $width+2);
					$tpl_element->setVariable('HEIGHT', $height+2);
					$tpl_element->setVariable('MARGIN_TOP', round((158.0-$height)/2.0));
					$tpl_element->setVariable('MARGIN_LEFT', round((158.0-$width)/2.0));
					$tpl_element->parseCurrentBlock();
					$tpl_element->setCurrentBlock('imgsize');
					$tpl_element->setVariable('IMG_WIDTH', $width);
					$tpl_element->setVariable('IMG_HEIGHT', $height);
					$tpl_element->parseCurrentBlock();
				}
				else
				{
					$tpl_element->setCurrentBlock('size');
					$tpl_element->setVariable('WIDTH', "150");
					$tpl_element->setVariable('HEIGHT', "150");
					$tpl_element->setVariable('MARGIN_TOP', "4");
					$tpl_element->setVariable('MARGIN_LEFT', "4");
					$tpl_element->parseCurrentBlock();
				}
				$tpl_element->setVariable('INLINE_SECTION', "aud$counter");
				$tpl_element->setVariable('URL_AUDIO', $this->object->getPathWeb(LOCATION_ORIGINALS) . $fn);
				$tpl_element->setVariable('CAPTION', ilUtil::prepareFormOutput($fdata['description']));
				if ($fdata['pwidth'] > 0)
				{
					$tpl_element->setVariable('URL_THUMBNAIL', $this->object->getPathWeb(LOCATION_PREVIEWS) . $fdata['pfilename'] . "?t=" . time());
				}
				else
				{
					$tpl_element->setVariable('URL_THUMBNAIL', $this->plugin->getDirectory() . '/templates/images/audio.png');
				}
				$tpl_element->setVariable('ALT_THUMBNAIL', ilUtil::prepareFormOutput($fdata['title']));
			}
			else if ($this->object->isVideo($fn))
			{
				$file_parts = pathinfo($fn);
				switch(strtolower($file_parts['extension']))
				{
					case "swf":
						$tpl_element = $this->plugin->getTemplate("tpl.gallery.qt.html");
						$iwidth = $fdata['pwidth'];
						$iheight = $fdata['pheight'];
						if ($fdata['pwidth'] > 0) 
						{
							$iwidth = $fdata['pwidth'];
							$iheight = $fdata['pheight'];
						}
						if ($iwidth > 0 && $iheight > 0)
						{
							$scale = $this->object->scaleDimensions($iwidth, $iheight, 150);
							$width = $scale['width'];
							$height = $scale['height'];
							$tpl_element->setCurrentBlock('size');
							$tpl_element->setVariable('WIDTH', $width+2);
							$tpl_element->setVariable('HEIGHT', $height+2);
							$tpl_element->setVariable('MARGIN_TOP', round((158.0-$height)/2.0));
							$tpl_element->setVariable('MARGIN_LEFT', round((158.0-$width)/2.0));
							$tpl_element->parseCurrentBlock();
							$tpl_element->setCurrentBlock('imgsize');
							$tpl_element->setVariable('IMG_WIDTH', $width);
							$tpl_element->setVariable('IMG_HEIGHT', $height);
							$tpl_element->parseCurrentBlock();
						}
						else
						{
							$tpl_element->setCurrentBlock('size');
							$tpl_element->setVariable('WIDTH', "150");
							$tpl_element->setVariable('HEIGHT', "150");
							$tpl_element->setVariable('MARGIN_TOP', "4");
							$tpl_element->setVariable('MARGIN_LEFT', "4");
							$tpl_element->parseCurrentBlock();
						}
						$tpl_element->setVariable('URL_VIDEO', $this->object->getPathWeb(LOCATION_ORIGINALS) . $fn);
						$tpl_element->setVariable('CAPTION', ilUtil::prepareFormOutput($fdata['description']));
						if ($fdata['pwidth'] > 0)
						{
							$tpl_element->setVariable('URL_THUMBNAIL', $this->object->getPathWeb(LOCATION_PREVIEWS) . $fdata['pfilename'] . "?t=" . time());
						}
						else
						{
							$tpl_element->setVariable('URL_THUMBNAIL', $this->plugin->getDirectory() . '/templates/images/video.png');
						}
						$tpl_element->setVariable('ALT_THUMBNAIL', ilUtil::prepareFormOutput($fdata['title']));
						break;
					case "mov":
					default:
						$tpl_element = $this->plugin->getTemplate("tpl.gallery.vid.html");
						$iwidth = $fdata['pwidth'];
						$iheight = $fdata['pheight'];
						if ($fdata['pwidth'] > 0) 
						{
							$iwidth = $fdata['pwidth'];
							$iheight = $fdata['pheight'];
						}
						if ($iwidth > 0 && $iheight > 0)
						{
							$scale = $this->object->scaleDimensions($iwidth, $iheight, 150);
							$width = $scale['width'];
							$height = $scale['height'];
							$tpl_element->setCurrentBlock('size');
							$tpl_element->setVariable('WIDTH', $width+2);
							$tpl_element->setVariable('HEIGHT', $height+2);
							$tpl_element->setVariable('MARGIN_TOP', round((158.0-$height)/2.0));
							$tpl_element->setVariable('MARGIN_LEFT', round((158.0-$width)/2.0));
							$tpl_element->parseCurrentBlock();
							$tpl_element->setCurrentBlock('imgsize');
							$tpl_element->setVariable('IMG_WIDTH', $width);
							$tpl_element->setVariable('IMG_HEIGHT', $height);
							$tpl_element->parseCurrentBlock();
						}
						else
						{
							$tpl_element->setCurrentBlock('size');
							$tpl_element->setVariable('WIDTH', "150");
							$tpl_element->setVariable('HEIGHT', "150");
							$tpl_element->setVariable('MARGIN_TOP', "4");
							$tpl_element->setVariable('MARGIN_LEFT', "4");
							$tpl_element->parseCurrentBlock();
						}
						$tpl_element->setVariable('INLINE_SECTION', "aud$counter");
						$tpl_element->setVariable('URL_VIDEO', $this->object->getPathWeb(LOCATION_ORIGINALS) . $fn);
						switch (strtolower($file_parts['extension']))
						{
							case 'webm':
								$tpl_element->setVariable('TYPE_VIDEO', "video/webm");
								break;
							case 'ogv':
								$tpl_element->setVariable('TYPE_VIDEO', "video/ogg");
								break;
							case 'mov':
								$tpl_element->setVariable('TYPE_VIDEO', "video/mp4; codecs=avc1.42E01E, mp4a.40.2");
								break;
							case 'mp4':
							default:
								$tpl_element->setVariable('TYPE_VIDEO', "video/mp4");
								break;
						}
						$tpl_element->setVariable('CAPTION', ilUtil::prepareFormOutput($fdata['description']));
						if ($fdata['pwidth'] > 0)
						{
							$tpl_element->setVariable('URL_THUMBNAIL', $this->object->getPathWeb(LOCATION_PREVIEWS) . $fdata['pfilename'] . "?t=" . time());
						}
						else
						{
							$tpl_element->setVariable('URL_THUMBNAIL', $this->plugin->getDirectory() . '/templates/images/video.png');
						}
						$tpl_element->setVariable('ALT_THUMBNAIL', ilUtil::prepareFormOutput($fdata['title']));
						break;
				}
			}
			else
			{
				$file_parts = pathinfo($fn);
				$tpl_element = $this->plugin->getTemplate("tpl.gallery.other.html");
				$iwidth = 0;
				$iheight = 0;
				if ($fdata['pwidth'] > 0) 
				{
					$scale = $this->object->scaleDimensions($fdata['pwidth'], $fdata['pheight'], 150);
					$iwidth = $scale['width'];
					$iheight = $scale['height'];
				}
				if ($iwidth > 0 && $iheight > 0)
				{
					$tpl_element->setCurrentBlock('size');
					$tpl_element->setVariable('WIDTH', $iwidth+2);
					$tpl_element->setVariable('HEIGHT', $iheight+2);
					$tpl_element->setVariable('MARGIN_TOP', round((158.0-$iheight)/2.0));
					$tpl_element->setVariable('MARGIN_LEFT', round((158.0-$iwidth)/2.0));
					$tpl_element->parseCurrentBlock();
					$tpl_element->setCurrentBlock('imgsize');
					$tpl_element->setVariable('IMG_WIDTH', $iwidth);
					$tpl_element->setVariable('IMG_HEIGHT', $iheight);
					$tpl_element->parseCurrentBlock();
					$fullwidth = $iwidth;
					$fullheight = $iheight;
					if ($fdata['pwidth'] > 500 || $fdata['pheight'] > 500)
					{
						$scale = $this->object->scaleDimensions($fullwidth, $fullheight, 500);
						$fullwidth = $scale['width'];
						$fullheight = $scale['height'];
					}
					$tpl_element->setCurrentBlock('imgsizeinline');
					$tpl_element->setVariable('IMG_WIDTH', $fullwidth);
					$tpl_element->setVariable('IMG_HEIGHT', $fullheight);
					$tpl_element->parseCurrentBlock();
				}
				else
				{
					$tpl_element->setCurrentBlock('size');
					$tpl_element->setVariable('WIDTH', "150");
					$tpl_element->setVariable('HEIGHT', "150");
					$tpl_element->setVariable('MARGIN_TOP', "4");
					$tpl_element->setVariable('MARGIN_LEFT', "4");
					$tpl_element->parseCurrentBlock();
				}
				$tpl_element->setVariable('CAPTION', ilUtil::prepareFormOutput($fdata['description']));
				if ($fdata['pwidth'] > 0)
				{
					$tpl_element->setVariable('URL_THUMBNAIL', $this->object->getPathWeb(LOCATION_PREVIEWS) . $fdata['pfilename'] . "?t=" . time());
				}
				else
				{
					$tpl_element->setVariable('URL_THUMBNAIL', $this->object->getMimeIconPath($fdata['entry']));
				}
				$tpl_element->setVariable('INLINE_SECTION', "oth$counter");
				$this->ctrl->setParameter($this, 'file', $fdata['entry']);
				$tpl_element->setVariable('URL_DOWNLOAD', $this->ctrl->getLinkTarget($this, "downloadOther"));
				$tpl_element->setVariable('URL_DOWNLOADICON', $this->plugin->getDirectory() . '/templates/images/download.png');
				$tpl_element->setVariable('ALT_THUMBNAIL', ilUtil::prepareFormOutput($fdata['title']));
			}

			$elementtitle = '';
			if ($this->object->getDownload())
			{
				$tpl_title = $this->plugin->getTemplate("tpl.gallery.download.html");
				if ($this->object->getShowTitle() && strlen($fdata['title']))
				{
					$tpl_title->setVariable('MEDIA_TITLE', ilUtil::prepareFormOutput($fdata['title']));
				}
				else
				{
					$tpl_title->setVariable('MEDIA_TITLE', ilUtil::prepareFormOutput($fdata['entry']));
				}
				$this->ctrl->setParameter($this, 'file', $fdata['entry']);
				$tpl_title->setVariable('URL_DOWNLOAD', $this->ctrl->getLinkTarget($this, "downloadOriginal"));
				$elementtitle = $tpl_title->get();
			}
			else if ($this->object->getShowTitle())
			{
				$tpl_title = $this->plugin->getTemplate("tpl.gallery.title.html");
				if (strlen($fdata['title']))
				{
					$tpl_title->setVariable('MEDIA_TITLE', ilUtil::prepareFormOutput($fdata['title']));
				}
				else
				{
					$tpl_title->setVariable('MEDIA_TITLE', '&nbsp;');
				}
				$elementtitle = $tpl_title->get();
			}

			$template->setVariable("TXT_EXPAND_IMAGE_TITLE", $this->plugin->txt("expand_image_title"));
			$template->setVariable("TXT_EXPAND_IMAGE", $this->plugin->txt("expand_image"));
			$template->setVariable("TXT_NEXT", $this->plugin->txt("next"));
			$template->setVariable("TXT_PREVIOUS", $this->plugin->txt("previous"));
			$template->setVariable("TXT_CLOSE", $this->plugin->txt("close"));
			$template->setVariable("TXT_START_SLIDESHOW", $this->plugin->txt("playpause"));
			$template->setCurrentBlock('media');
			$template->setVariable('GALLERY_ELEMENT', $tpl_element->get() . $elementtitle);
			$template->parseCurrentBlock();
		}

		$archives = $this->object->getArchives();
		$downloads = array();
		foreach ($archives as $fn => $fdata)
		{
			if ($fdata['download'])
			{
				$downloads[$fn] = $fn . ' ('.$this->object->formatBytes($fdata['size']).')';
			}
		}
		if (count($downloads))
		{
			global $ilToolbar, $ilCtrl, $lng;
			include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
			$si = new ilSelectInputGUI($this->plugin->txt("archive").':', "archive");
			$si->setOptions($downloads);
			$ilToolbar->addInputItem($si, true);
			$ilToolbar->addFormButton($lng->txt("download"), 'download');
			$ilToolbar->setFormAction($ilCtrl->getFormAction($this));
		}

		$template->setVariable("THEME", $this->object->getTheme());
		$this->tpl->setVariable("ADM_CONTENT", $template->get());
	}
	
	function downloadOriginal()
	{
		if ($this->object->getDownload())
		{
			ilUtil::deliverFile($this->object->getPath(LOCATION_ORIGINALS) . $_GET['file'], $_GET['file']);
		}
		else
		{
			$this->ctrl->redirect($this, "gallery");
		}
	}

	function downloadOther()
	{
		ilUtil::deliverFile($this->object->getPath(LOCATION_ORIGINALS) . $_GET['file'], $_GET['file']);
	}
	
	function filterMedia()
	{
		$this->plugin->includeClass("class.ilMediaFileTableGUI.php");
		$table_gui = new ilMediaFileTableGUI($this, 'mediafiles');
		$table_gui->resetOffset();
		$table_gui->writeFilterToSession();
		$this->ctrl->redirect($this, 'mediafiles');
	}

	function resetFilterMedia()
	{
		$this->plugin->includeClass("class.ilMediaFileTableGUI.php");
		$table_gui = new ilMediaFileTableGUI($this, 'mediafiles');
		$table_gui->resetOffset();
		$table_gui->resetFilter();
		$this->ctrl->redirect($this, 'mediafiles');
	}
	
	public function mediafiles()
	{
		global $ilTabs;
		if (strcmp($_GET['action'], 'rotateLeft') && strlen($_GET['id']))
		{
			$this->object->rotate($_GET['id'], 0);
			$this->ctrl->setParameter($this, "action", "");
			$this->ctrl->redirect($this, 'mediafiles');
			return;
		}
		else if (strcmp($_GET['action'], 'rotateRight') && strlen($_GET['id']))
		{
			$this->object->rotate($_GET['id'], 1);
			$this->ctrl->setParameter($this, "action", "");
			$this->ctrl->redirect($this, 'mediafiles');
			return;
		}
		$this->setSubTabs("mediafiles");
		$ilTabs->activateTab("mediafiles");
		$count = $this->object->getMediaObjectCount();
		$this->tpl->addCss($this->plugin->getStyleSheetLocation("xmg.css"));
		$this->plugin->includeClass("class.ilMediaFileTableGUI.php");
		$table_gui = new ilMediaFileTableGUI($this, 'mediafiles');
		$arrFilter = array();
		foreach ($table_gui->getFilterItems() as $item)
		{
			if ($item->getValue() !== false)
			{
				$arrFilter[substr($item->getPostVar(), 2)] = $item->getValue();
			}
		}
		$mediafiles = $this->object->getMediaFiles($arrFilter);
		// recalculate custom sort keys
		$tmpsortkey = $this->sortkey;
		$this->sortkey = 'custom';
		uasort($mediafiles, array($this, 'gallerysort'));
		$counter = 1.0;
		foreach ($mediafiles as $fn => $fdata)
		{
			$mediafiles[$fn]['custom'] = $counter;
			$counter += 1.0;
		}
		$this->sortkey = $tmpsortkey;
		$table_gui->setData($mediafiles);
		$this->tpl->setVariable('ADM_CONTENT', $table_gui->getHTML());	
	}
	
	public function createMissingPreviews()
	{
		$this->object->createMissingPreviews();
		$this->ctrl->redirect($this, 'gallery');
	}
	
	public function createArchiveFromSelection()
	{
		if (!is_array($_POST['file']))
		{
			ilUtil::sendInfo($this->plugin->txt('please_select_file_to_create_archive'), true);
			$this->ctrl->redirect($this, 'archives');
		}
		else
		{
			$zipfile = sprintf("%s_%s", $this->object->getTitle(), time());
			$this->object->zipSelectedFiles($_POST['file'], $zipfile . ".zip");
			$_SESSION['archiveFilename'] = $zipfile;
			$this->ctrl->redirect($this, 'setArchiveFilename');
		}
	}
	
	public function addPreview()
	{
		global $tpl, $ilTabs;

		if (!is_array($_POST['file']))
		{
			ilUtil::sendInfo($this->plugin->txt('please_select_file_to_add_preview'), true);
			$this->ctrl->redirect($this, 'mediafiles');
		}
		else
		{
			$_SESSION['previewFiles'] = $_POST['file'];
		}
		$this->setSubTabs("mediafiles");
		$ilTabs->activateTab("mediafiles");
		$this->initPreviewUploadForm();
		$tpl->setContent($this->form->getHTML());
	}

	public function deletePreview()
	{
		global $tpl, $ilTabs;

		if (!is_array($_POST['file']))
		{
			ilUtil::sendInfo($this->plugin->txt('please_select_file_to_delete_preview'), true);
			$this->ctrl->redirect($this, 'mediafiles');
		}
		else
		{
			$_SESSION['previewFiles'] = $_POST['file'];
		}
		$this->object->deletePreview($_SESSION['previewFiles']);
		unset($_SESSION['previewFiles']);
		ilUtil::sendSuccess($this->plugin->txt('previews_deleted'), true);
		$this->ctrl->redirect($this, 'mediafiles');
	}

	public function uploadPreview()
	{
		global $tpl, $lng, $ilCtrl, $ilTabs;

		$this->setSubTabs("mediafiles");
		$ilTabs->activateTab("mediafiles");
		$this->initPreviewUploadForm();
		if ($this->form->checkInput())
		{
			$this->object->uploadPreviewForFiles($_SESSION['previewFiles'], $_FILES['filename']["tmp_name"], $_FILES['filename']['type']);
			unset($_SESSION['previewFiles']);
			$ilCtrl->redirect($this, "mediafiles");
		}

		$this->form->setValuesByPost();
		$tpl->setContent($this->form->getHtml());
	}

	protected function initPreviewUploadForm()
	{
		global $ilCtrl;

		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();

		// filename
		$ti = new ilFileInputGUI($this->txt("filename"), "filename");
		$ti->setRequired(true);
		$ti->setSuffixes(array('jpg','jpeg','png'));
		$this->form->addItem($ti);

		$this->form->addCommandButton("uploadPreview", $this->txt("upload"));
		$this->form->addCommandButton("mediafiles", $this->txt("cancel"));

		$this->form->setTitle($this->plugin->txt("add_preview"));
		$this->form->setFormAction($ilCtrl->getFormAction($this));
	}

	public function changeArchiveFilename()
	{
		global $tpl, $ilTabs;
		
		if (!is_array($_POST['file']))
		{
			ilUtil::sendInfo($this->plugin->txt('please_select_archive_to_rename'), true);
			$this->ctrl->redirect($this, 'archives');
		}
		else if (count($_POST['file']) > 1)
		{
			ilUtil::sendInfo($this->plugin->txt('please_select_archive_to_rename'), true);
			$this->ctrl->redirect($this, 'archives');
		}
		else
		{
			foreach ($_POST['file'] as $file)
			{
				$_SESSION['archiveFilename'] = substr($file, 0, -4);
			}
		}

		$ilTabs->activateTab("archives");
		$this->initArchiveFilenameForm();
		$this->getArchiveFilenameValues();
		$tpl->setContent($this->form->getHTML());
	}

	public function setArchiveFilename()
	{
		global $tpl, $ilTabs;
		
		$ilTabs->activateTab("archives");
		$this->initArchiveFilenameForm();
		$this->getArchiveFilenameValues();
		$tpl->setContent($this->form->getHTML());
	}

	protected function getArchiveFilenameValues()
	{
		$values["filename"] = $_SESSION['archiveFilename'];
		$this->form->setValuesByArray($values);
	}

	public function renameArchiveFilename()
	{
		if ($this->object->downloadArchiveExists($_POST['filename'] . ".zip"))
		{
			ilUtil::sendFailure($this->plugin->txt('please_select_unique_archive_name'), true);
			$this->ctrl->redirect($this, 'setArchiveFilename');
		}
		else
		{
			if (strlen($_SESSION['archiveFilename']) && strlen($_POST['filename']))
			{
				$this->object->renameArchive($_SESSION['archiveFilename'] . ".zip", $_POST['filename'] . ".zip");
				unset($_SESSION['archiveFilename']);
				$this->ctrl->redirect($this, 'archives');
			}
			else
			{
				$this->ctrl->redirect($this, 'archives');
			}
		}
	}

	protected function initArchiveFilenameForm()
	{
		global $ilCtrl;

		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();

		// filename
		$ti = new ilTextInputGUI($this->txt("filename"), "filename");
		$ti->setRequired(true);
		$ti->setSuffix(".zip");
		$ti->setValue($_SESSION['archiveFilename']);
		$this->form->addItem($ti);

		$this->form->addCommandButton("renameArchiveFilename", $this->txt("save"));
		$this->form->addCommandButton("archives", $this->txt("cancel"));

		$this->form->setTitle($this->plugin->txt("saveArchiveFilename"));
		$this->form->setFormAction($ilCtrl->getFormAction($this));
	}

	public function deleteFile()
	{
		if (!is_array($_POST['file']))
		{
			ilUtil::sendInfo($this->plugin->txt('please_select_file_to_delete'), true);
		}
		else
		{
			foreach ($_POST['file'] as $file)
			{
				$this->object->deleteFile($file);
			}
			ilUtil::sendSuccess(sprintf((count($_POST['file']) == 1) ? $this->plugin->txt('file_deleted') : $this->plugin->txt('files_deleted'), count($_POST['file'])), true);
		}
		$this->ctrl->redirect($this, 'mediafiles');
	}

	public function saveAllFileData()
	{
		foreach ($_POST['id'] as $filename => $file_id)
		{
			$file_topic = $_POST['topic'][$filename];
			$file_title = $_POST['title'][$filename];
			$file_description = $_POST['description'][$filename];
			$file_custom = $_POST['custom'][$filename];
			$file_width = $_POST['width'][$filename];
			$file_height = $_POST['height'][$filename];
			if (!is_numeric($file_custom)) $file_custom = 0.0;
			$this->object->saveFileData($filename, $file_id, $file_topic, $file_title, $file_description, $file_custom, $file_width, $file_height);
		}
		ilUtil::sendSuccess($this->plugin->txt('file_data_saved'), true);
		$this->ctrl->redirect($this, 'mediafiles');
	}
	
	protected function normalizeUtf8String( $s)
	{
		// maps German (umlauts) and other European characters onto two characters before just removing diacritics
		$s    = preg_replace( '@\x{00c4}@u'    , "AE",    $s );    // umlaut Ä => AE
		$s    = preg_replace( '@\x{00d6}@u'    , "OE",    $s );    // umlaut Ö => OE
		$s    = preg_replace( '@\x{00dc}@u'    , "UE",    $s );    // umlaut Ü => UE
		$s    = preg_replace( '@\x{00e4}@u'    , "ae",    $s );    // umlaut ä => ae
		$s    = preg_replace( '@\x{00f6}@u'    , "oe",    $s );    // umlaut ö => oe
		$s    = preg_replace( '@\x{00fc}@u'    , "ue",    $s );    // umlaut ü => ue
		$s    = preg_replace( '@\x{00f1}@u'    , "ny",    $s );    // ñ => ny
		$s    = preg_replace( '@\x{00ff}@u'    , "yu",    $s );    // ÿ => yu


		if (class_exists("Normalizer", $autoload = false))
		{
			$s    = Normalizer::normalize( $s, Normalizer::FORM_C );
		}

		$s    = preg_replace( '@\pM@u'        , "",    $s );    // removes diacritics

		$s    = preg_replace( '@\x{00df}@u'    , "ss",    $s );    // maps German ß onto ss
		$s    = preg_replace( '@\x{00c6}@u'    , "AE",    $s );    // Æ => AE
		$s    = preg_replace( '@\x{00e6}@u'    , "ae",    $s );    // æ => ae
		$s    = preg_replace( '@\x{0132}@u'    , "IJ",    $s );    // ? => IJ
		$s    = preg_replace( '@\x{0133}@u'    , "ij",    $s );    // ? => ij
		$s    = preg_replace( '@\x{0152}@u'    , "OE",    $s );    // Œ => OE
		$s    = preg_replace( '@\x{0153}@u'    , "oe",    $s );    // œ => oe

		$s    = preg_replace( '@\x{00d0}@u'    , "D",    $s );    // Ð => D
		$s    = preg_replace( '@\x{0110}@u'    , "D",    $s );    // Ð => D
		$s    = preg_replace( '@\x{00f0}@u'    , "d",    $s );    // ð => d
		$s    = preg_replace( '@\x{0111}@u'    , "d",    $s );    // d => d
		$s    = preg_replace( '@\x{0126}@u'    , "H",    $s );    // H => H
		$s    = preg_replace( '@\x{0127}@u'    , "h",    $s );    // h => h
		$s    = preg_replace( '@\x{0131}@u'    , "i",    $s );    // i => i
		$s    = preg_replace( '@\x{0138}@u'    , "k",    $s );    // ? => k
		$s    = preg_replace( '@\x{013f}@u'    , "L",    $s );    // ? => L
		$s    = preg_replace( '@\x{0141}@u'    , "L",    $s );    // L => L
		$s    = preg_replace( '@\x{0140}@u'    , "l",    $s );    // ? => l
		$s    = preg_replace( '@\x{0142}@u'    , "l",    $s );    // l => l
		$s    = preg_replace( '@\x{014a}@u'    , "N",    $s );    // ? => N
		$s    = preg_replace( '@\x{0149}@u'    , "n",    $s );    // ? => n
		$s    = preg_replace( '@\x{014b}@u'    , "n",    $s );    // ? => n
		$s    = preg_replace( '@\x{00d8}@u'    , "O",    $s );    // Ø => O
		$s    = preg_replace( '@\x{00f8}@u'    , "o",    $s );    // ø => o
		$s    = preg_replace( '@\x{017f}@u'    , "s",    $s );    // ? => s
		$s    = preg_replace( '@\x{00de}@u'    , "T",    $s );    // Þ => T
		$s    = preg_replace( '@\x{0166}@u'    , "T",    $s );    // T => T
		$s    = preg_replace( '@\x{00fe}@u'    , "t",    $s );    // þ => t
		$s    = preg_replace( '@\x{0167}@u'    , "t",    $s );    // t => t

		// remove all non-ASCii characters
		$s    = preg_replace( '@[^\0-\x80]@u'    , "",    $s ); 

		// possible errors in UTF8-regular-expressions
		if (empty($s))
			return $original_string;
		else
			return $s; 
	}

	public function uploadFile()
	{
		global $ilLog;
		// HTTP headers for no cache etc
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");

		// Settings
		$targetDir = $this->object->getPath(LOCATION_ORIGINALS);
		$cleanupTargetDir = true; // Remove old files
		$maxFileAge = 5 * 3600; // Temp file age in seconds

		// 5 minutes execution time
		@set_time_limit(5 * 60);

		// Get parameters
		$chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
		$chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;
		$fileName = isset($_REQUEST["name"]) ? $_REQUEST["name"] : '';

		// Clean the fileName for security reasons
		$fileName = $this->normalizeUtf8String($fileName);
		$fileName = preg_replace('/[^\w\._]+/', '_', $fileName);

		// Make sure the fileName is unique but only if chunking is disabled
		if ($chunks < 2 && file_exists($targetDir . $fileName)) {
			$ext = strrpos($fileName, '.');
			$fileName_a = substr($fileName, 0, $ext);
			$fileName_b = substr($fileName, $ext);

			$count = 1;
			while (file_exists($targetDir . $fileName_a . '_' . $count . $fileName_b))
				$count++;

			$fileName = $fileName_a . '_' . $count . $fileName_b;
		}

		$filePath = $targetDir . $fileName;

		// Create target dir
		if (!file_exists($targetDir))
			@mkdir($targetDir);

		// Remove old temp files	
		if ($cleanupTargetDir && is_dir($targetDir) && ($dir = opendir($targetDir))) {
			while (($file = readdir($dir)) !== false) {
				$tmpfilePath = $targetDir . $file;

				// Remove temp file if it is older than the max age and is not the current file
				if (preg_match('/\.part$/', $file) && (filemtime($tmpfilePath) < time() - $maxFileAge) && ($tmpfilePath != "{$filePath}.part")) {
					@unlink($tmpfilePath);
				}
			}

			closedir($dir);
		} else
			die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory."}, "id" : "id"}');


		// Look for the content type header
		if (isset($_SERVER["HTTP_CONTENT_TYPE"]))
			$contentType = $_SERVER["HTTP_CONTENT_TYPE"];

		if (isset($_SERVER["CONTENT_TYPE"]))
			$contentType = $_SERVER["CONTENT_TYPE"];

		// Handle non multipart uploads older WebKit versions didn't support multipart in HTML5
		if (strpos($contentType, "multipart") !== false) 
		{
			if (isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) 
			{
				// Open temp file
				$out = fopen("{$filePath}.part", $chunk == 0 ? "wb" : "ab");
				if ($out) {
					// Read binary input stream and append it to temp file
					$in = fopen($_FILES['file']['tmp_name'], "rb");

					if ($in) {
						while ($buff = fread($in, 4096))
							fwrite($out, $buff);
					} else
					{
						$ilLog->write('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
						die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
					}
					fclose($in);
					fclose($out);
					@unlink($_FILES['file']['tmp_name']);
				} else
				{
					$ilLog->write('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
					die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
				}
			} 
			else
			{
				$ilLog->write('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
				die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
			}
		} else {
			// Open temp file
			$out = fopen("{$filePath}.part", $chunk == 0 ? "wb" : "ab");
			if ($out) {
				// Read binary input stream and append it to temp file
				$in = fopen("php://input", "rb");

				if ($in) {
					while ($buff = fread($in, 4096))
						fwrite($out, $buff);
				} else
				{
					$ilLog->write('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
					die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
				}

				fclose($in);
				fclose($out);
			} else
			{
				$ilLog->write('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
				die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
			}
		}

		// Check if file has been uploaded
		if (!$chunks || $chunk == $chunks - 1) {
			// Strip the temp .part suffix off 
			rename("{$filePath}.part", $filePath);
			$this->object->processNewUpload($filePath);
		}
		// Return JSON-RPC response
		die('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');
	}

	protected function return_bytes($val) {
		$val = trim($val);
		$last = strtolower($val[strlen($val)-1]);
		switch($last) {
			case 'g':
				$val *= 1024;
			case 'm':
				$val *= 1024;
			case 'k':
				$val *= 1024;
		}
		return $val;
	}

	public function upload()
	{
		global $ilTabs, $ilCtrl;

		$this->setSubTabs("mediafiles");
		$ilTabs->activateTab("mediafiles");
		$template = $this->plugin->getTemplate("tpl.upload.html");
		$template->setVariable("FILE_ALERT", $this->plugin->txt('upload_file_alert'));
		$this->plugin->includeClass("class.ilObjMediaGallery.php");
		$ext_img = ilObjMediaGallery::_getConfigurationValue('ext_img');
		$ext_vid = ilObjMediaGallery::_getConfigurationValue('ext_vid');
		$ext_aud = ilObjMediaGallery::_getConfigurationValue('ext_aud');
		$ext_oth = ilObjMediaGallery::_getConfigurationValue('ext_oth');
		$template->setVariable("FILTERS", 'filters: [' .
			'{title : "' . $this->plugin->txt('image_files') . '", extensions : "' . $ext_img . '"},' .
			'{title : "' . $this->plugin->txt('video_files') . '", extensions : "' . $ext_vid . '"},' .
			'{title : "' . $this->plugin->txt('audio_files') . '", extensions : "' . $ext_aud . '"},' .
			'{title : "' . $this->plugin->txt('other_files') . '", extensions : "' . $ext_oth . '"}' .
			'],');
		$template->setVariable("UPLOAD_URL", html_entity_decode(ILIAS_HTTP_PATH . "/" . $ilCtrl->getLinkTarget($this, 'uploadFile')));
		$M = $this->return_bytes(ini_get('upload_max_filesize'));
		$M = $M / (1024 * 1024);
		$template->setVariable("MAX_FILE_SIZE_IN_MB", $M);
		$this->tpl->addCss($this->plugin->getDirectory() . "/js/jquery.plupload.queue/css/jquery.plupload.queue.css");
		$this->tpl->addJavascript($this->plugin->getDirectory() . "/js/plupload.full.js");
		$this->tpl->addJavascript($this->plugin->getDirectory() . "/js/jquery.plupload.queue/jquery.plupload.queue.js");
		$this->tpl->setVariable("ADM_CONTENT", $template->get());
	}
}
?>