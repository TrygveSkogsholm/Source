<?php

class VO_Purchase_Block_Shipments_Renderer_Selectname
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
    	$Id = $row->getId();
		return Mage::getModel('purchase/order_product')->load($Id)->getName();
    }

}