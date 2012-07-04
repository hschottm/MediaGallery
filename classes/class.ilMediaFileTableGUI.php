<?php
/*
        +-----------------------------------------------------------------------------+
        | ILIAS open source                                                           |
        +-----------------------------------------------------------------------------+
        | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
        |                                                                             |
        | This program is free software; you can redistribute it and/or               |
        | modify it under the terms of the GNU General Public License                 |
        | as published by the Free Software Foundation; either version 2              |
        | of the License, or (at your option) any later version.                      |
        |                                                                             |
        | This program is distributed in the hope that it will be useful,             |
        | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
        | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
        | GNU General Public License for more details.                                |
        |                                                                             |
        | You should have received a copy of the GNU General Public License           |
        | along with this program; if not, write to the Free Software                 |
        | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
        +-----------------------------------------------------------------------------+
*/

include_once('./Services/Table/classes/class.ilTable2GUI.php');

/**
*
* @author Helmut Schottmüller <ilias@aurealis.de>
* @version $Id:$
*
* @ingroup ModulesTest
*/

class ilMediaFileTableGUI extends ilTable2GUI
{
	protected $counter;
	protected $plugin;
	protected $customsort;
	
	/**
	 * Constructor
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function __construct($a_parent_obj, $a_parent_cmd)
	{
		parent::__construct($a_parent_obj, $a_parent_cmd);

		global $lng, $ilCtrl;

		$this->lng = $lng;
		$this->ctrl = $ilCtrl;
		include_once "./Services/Component/classes/class.ilPlugin.php";
		$this->plugin = ilPlugin::getPluginObject(IL_COMP_SERVICE, "Repository", "robj", "MediaGallery");
	
		$this->customsort = 1.0;
    $this->setId("xmg_mft_".$a_parent_obj->object->getId());
		$this->setFormName('mediaobjectlist');
		$this->setStyle('table', 'fullwidth');
		$this->counter = 1;
		$this->addColumn('','f','1%');
		$this->addColumn($this->lng->txt("filename"),'entry', '', '', 'xmg_fn');
		$this->addColumn('','', '', '', 'xmg_preview');
		$this->addColumn($this->plugin->txt("sort"),'custom', '', '', 'xmg_custom');
		$this->addColumn($this->lng->txt("id"),'media_id', '', '', 'xmg_id');
		$this->addColumn($this->plugin->txt("topic"),'topic', '', '', 'xmg_topic');
		$this->addColumn($this->lng->txt("title"),'title', '', '', 'xmg_title');
		$this->addColumn($this->lng->txt("description"),'description', '', '', 'xmg_desc');
	
		$this->setRowTemplate("tpl.mediafiles_row.html", 'Customizing/global/plugins/Services/Repository/RepositoryObject/MediaGallery');

		$this->setDefaultOrderField("entry");
		$this->setDefaultOrderDirection("asc");
		$this->setFilterCommand('filterMedia');
		$this->setResetCommand('resetFilterMedia');
		
		$this->addCommandButton('deleteFile', $this->lng->txt('delete'));
		$this->addCommandButton('saveAllFileData', $this->plugin->txt('save_all'));
		$this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));
		$this->setSelectAllCheckbox('file');
//		$this->setExternalSorting(true);

		$this->enable('header');
		$this->initFilter();
	}
	
	public function gallerysort($x, $y) 
	{
		switch ($this->getOrderDirection())
		{
			case 'asc':
				return strnatcasecmp($x[$this->getOrderField()], $y[$this->getOrderField()]);
				break;
			case 'desc':
				return strnatcasecmp($y[$this->getOrderField()], $x[$this->getOrderField()]);
				break;
		}
		return 0;
	} 

	protected function prepareOutput()
	{
		return;
		// use this for external sorting
		$this->determineOffsetAndOrder();
		uasort($this->row_data, array($this, 'gallerysort'));
	}

	function numericOrdering($a_field)
	{
		switch ($a_field)
		{
			case 'custom':
				return true;
			default:
				return false;
		}
	}

	/**
	 * fill row 
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function fillRow($data)
	{
		global $ilUser,$ilAccess;

		$this->plugin->includeClass("class.ilObjMediaGallery.php");
		$this->tpl->setVariable('CB_ID', $this->counter++);
		$this->tpl->setVariable("CB_FILE", ilUtil::prepareFormOutput($data['entry']));
		$this->tpl->setVariable("FILENAME", ilUtil::prepareFormOutput($data['entry']));
		if ($this->parent_obj->object->isImage($data['entry']))
		{
			$this->tpl->setVariable("PREVIEW", $this->parent_obj->object->getPathWeb(LOCATION_THUMBS) . $data['entry']);
		}
		else if ($this->parent_obj->object->isAudio($data['entry']))
		{
			$this->tpl->setVariable("PREVIEW", $this->plugin->getDirectory() . '/templates/images/audio.png');
		}
		else if ($this->parent_obj->object->isVideo($data['entry']))
		{
			$this->tpl->setVariable("PREVIEW", $this->plugin->getDirectory() . '/templates/images/video.png');
		}
		else
		{
			$this->tpl->setVariable("PREVIEW", $this->plugin->getDirectory() . '/templates/images/unknown.png');
		}
		$this->tpl->setVariable("TEXT_PREVIEW", strlen($data['title']) ? ilUtil::prepareFormOutput($data['title']) : ilUtil::prepareFormOutput($data['entry']));
		$this->tpl->setVariable("ID", $data['entry']);
		if ($data['custom'] == 0) 
		{
			$data['custom'] = $this->customsort;
		}
		$this->customsort += 1.0;
		$this->tpl->setVariable("CUSTOM", $this->getTextFieldValue(sprintf("%.1f", $data['custom'])));
		$this->tpl->setVariable("SIZE", ilUtil::prepareFormOutput($this->formatBytes($data['size'])));
		$this->tpl->setVariable("ELEMENT_ID", $this->getTextFieldValue($data['media_id']));
		$this->tpl->setVariable("TOPIC", $this->getTextFieldValue($data['topic']));
		$this->tpl->setVariable("TITLE", $this->getTextFieldValue($data['title']));
		$this->tpl->setVariable("WIDTH", $this->getTextFieldValue($data['width']));
		$this->tpl->setVariable("HEIGHT", $this->getTextFieldValue($data['height']));
		$this->tpl->setVariable("DESCRIPTION", $this->getTextFieldValue($data['description']));
	}
	
	protected function getTextFieldValue($value)
	{
		$res = '';
		if (strlen($value))
		{
			$res = ' value="' . ilUtil::prepareFormOutput($value) . '"';
		}
		return $res;
	}
	
	protected function formatBytes($bytes, $precision = 2) 
	{
		$units = array('B', 'KB', 'MB', 'GB', 'TB');

		$bytes = max($bytes, 0);
		$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
		$pow = min($pow, count($units) - 1);

		$bytes /= pow(1024, $pow);

		return round($bytes, $precision) . ' ' . $units[$pow];
	}

	/**
	* Init filter
	*/
	function initFilter()
	{
		global $lng, $rbacreview, $ilUser;
		
		include_once("./Services/Form/classes/class.ilTextInputGUI.php");
		include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
		// media type
		$options = array(
			'' => $this->plugin->txt('all_media_types'),
			'image' => $this->plugin->txt('image'),
			'audio' => $this->plugin->txt('audio'),
			'video' => $this->plugin->txt('video'),
			'unknown' => $this->plugin->txt('unknown'),
		);
		$si = new ilSelectInputGUI($this->plugin->txt("media_type"), "f_type");
		$si->setOptions($options);
		$this->addFilterItem($si);
		$si->readFromSession();
		$this->filter["f_type"] = $si->getValue();

		// filename
		$entry = new ilTextInputGUI($this->plugin->txt("filename"), "f_entry");
		$entry->setMaxLength(64);
		$entry->setValidationRegexp('/^[^%]+$/is');
		$entry->setSize(20);
		$this->addFilterItem($entry);
		$entry->readFromSession();
		$this->filter["f_entry"] = $entry->getValue();

		// id
		$mid = new ilTextInputGUI($this->plugin->txt("id"), "f_media_id");
		$mid->setMaxLength(64);
		$mid->setValidationRegexp('/^[^%]+$/is');
		$mid->setSize(20);
		$this->addFilterItem($mid);
		$mid->readFromSession();
		$this->filter["f_media_id"] = $mid->getValue();

		// topic
		$topic = new ilTextInputGUI($this->plugin->txt("topic"), "f_topic");
		$topic->setMaxLength(64);
		$topic->setValidationRegexp('/^[^%]+$/is');
		$topic->setSize(20);
		$this->addFilterItem($topic);
		$topic->readFromSession();
		$this->filter["f_topic"] = $topic->getValue();

		// title
		$ti = new ilTextInputGUI($this->plugin->txt("title"), "f_title");
		$ti->setMaxLength(64);
		$ti->setValidationRegexp('/^[^%]+$/is');
		$ti->setSize(20);
		$this->addFilterItem($ti);
		$ti->readFromSession();
		$this->filter["f_title"] = $ti->getValue();
		
		// description
		$ti = new ilTextInputGUI($this->plugin->txt("description"), "f_description");
		$ti->setMaxLength(64);
		$ti->setSize(20);
		$ti->setValidationRegexp('/^[^%]+$/is');
		$this->addFilterItem($ti);
		$ti->readFromSession();
		$this->filter["f_description"] = $ti->getValue();
	}
}
?>