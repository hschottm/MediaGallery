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

class ilMediaFileDownloadArchivesTableGUI extends ilTable2GUI
{
	protected $counter;
	protected $plugin;
	
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
	
    $this->setId("xmg_arch_".$a_parent_obj->object->getId());
		$this->setFormName('downloadarchives');
		$this->setStyle('table', 'fullwidth');
		$this->counter = 1;
		$this->addColumn('','f','1%');
		$this->addColumn($this->plugin->txt("filename"),'filename', '', '', 'xmg_arch_filename');
		$this->addColumn($this->plugin->txt("size"),'size', '', '', 'xmg_arch_size');
		$this->addColumn($this->plugin->txt("download_archive"),'download', '', '', 'xmg_arch_download');
		$this->addColumn($this->plugin->txt("created"),'created', '', '', 'xmg_arch_created');
	
		$this->setRowTemplate("tpl.mediafiles_archive_row.html", 'Customizing/global/plugins/Services/Repository/RepositoryObject/MediaGallery');

		$this->setDefaultOrderField("filename");
		$this->setDefaultOrderDirection("asc");
		
		$this->addCommandButton('deleteArchive', $this->lng->txt('delete'));
		$this->addCommandButton('saveAllArchiveData', $this->plugin->txt('save_all'));
		$this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));
		$this->setSelectAllCheckbox('file');
//		$this->setExternalSorting(true);

		$this->enable('header');
		$this->initFilter();
	}

	function numericOrdering($a_field)
	{
		switch ($a_field)
		{
			case 'size':
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
		$this->tpl->setVariable("SIZE", ilUtil::prepareFormOutput($this->formatBytes($data['size'])));
		$this->tpl->setVariable("CREATED", ilDatePresentation::formatDate(new ilDate($data["created"],IL_CAL_UNIX)));
		if ($data['download'])
		{
			$this->tpl->setVariable("CHECKED_DOWNLOAD", ' checked="checked"');
		}
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
}
?>