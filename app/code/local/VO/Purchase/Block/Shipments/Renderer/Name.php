<?php

class VO_Purchase_Block_Shipments_Renderer_Name
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
    	$Id = $row->getId();
		return Mage::getModel('purchase/shipment_product')->load($Id)->getName();
    }

}