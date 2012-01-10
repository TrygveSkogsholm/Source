
<?php

class VO_Warehouse_Block_Adminhtml_Ranges_Notes extends Mage_Adminhtml_Block_Template
{
	public function __construct()
	{
		$this->setTemplate('warehouse/notes.phtml');
		parent::__construct();
	}

	public function getHeaderText()
	{
			return Mage::helper('purchase')->__("Change Prices");
	}
	
	public function getNotes()
	{
		return Mage::registry('range_data')->getNotes();
	}
}