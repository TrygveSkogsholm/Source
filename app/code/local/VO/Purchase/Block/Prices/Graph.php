
<?php

class VO_Purchase_Block_Prices_Graph extends Mage_Adminhtml_Block_Template
{
	public function __construct()
	{
		parent::__construct();
	}

	public function getHeaderText()
	{
			return Mage::helper('purchase')->__("Change Prices");
	}
}