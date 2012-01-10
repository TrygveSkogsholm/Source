<?php

class VO_Purchase_Block_Orders_Edit_Tab_Shiptab_Item extends Mage_Adminhtml_Block_Template
{
	public $item;
	public $location;
	
	public function _prepareLayout()
    {
    	$this->setTemplate('purchase/order/Shiptab/itemBlock.phtml');
		return parent::_prepareLayout();
    }
    
    public function setItem($item)
    {
    	$this->item = $item;
    }
    
    public function getItem()
    {
    	return $this->item;
    }
}