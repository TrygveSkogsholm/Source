<?php

class VO_Purchase_Block_Prices_View extends Mage_Adminhtml_Block_Template
{
	public function __construct()
	{
		parent::__construct();
	}

	public function getHeaderText()
	{
			return Mage::helper('purchase')->__("View Price Change Details");
	}
}