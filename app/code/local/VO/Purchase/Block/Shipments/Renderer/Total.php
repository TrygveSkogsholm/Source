<?php

class VO_Purchase_Block_Shipments_Renderer_Total
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
    	$Id = $row->getId();
    	$retour = Mage::helper('core')->currency(Mage::getModel('purchase/shipment')->load($Id)->getTotal());
		return $retour;
    }

}