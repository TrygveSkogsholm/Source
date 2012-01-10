<?php

class VO_Purchase_Block_Orders_Edit_Tab_Shiptab_Shipment extends Mage_Adminhtml_Block_Template
{
	public $shipment;
	
	public function _prepareLayout()
    {
    	$this->setTemplate('purchase/order/Shiptab/shipmentBlock.phtml');
		return parent::_prepareLayout();
    }
    
    public function setShipment($shipment)
    {
    	$this->shipment = $shipment;
    }
    
    public function getShipment()
    {
    	return $this->shipment;
    }
}