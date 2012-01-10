<?php
class VO_Purchase_Block_Test extends Mage_Adminhtml_Block_Template
{
	public function _prepareLayout()
    {
    	//Mage::getModel('purchase/update')->repopulateData();
		return parent::_prepareLayout();
    }
}
//var_dump( Mage::getModel('purchase/order_product')->load(1)->getAllShipmentObjects()->count());

//echo Mage::getModel('purchase/supplier_product')->load(25)->getOnOrder();