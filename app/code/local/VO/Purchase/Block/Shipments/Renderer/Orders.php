<?php

class VO_Purchase_Block_Shipments_Renderer_Orders
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
    	$Id = $row->getId();
    	$retour = "";
    	foreach (Mage::getModel('purchase/shipment')->load($Id)->getPurchaseOrders() as $purchaseOrder)
    	{
    		$retour .= $purchaseOrder->getId().'<br/>';
    	}

		return $retour;
    }

}