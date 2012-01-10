<?php

class VO_Purchase_Block_Shipments_Orderselect extends Mage_Adminhtml_Block_Template
{
	public $_shipment;

	protected function _construct()
	{
		$this->setTemplate('purchase/shipment/Orderselect.phtml');
		$this->_shipment = $this->getShipment();
		return parent::_construct();
	}

	public function getShipment()
	{
		if ( Mage::registry('shipmentId') )
		{
			return Mage::getModel('purchase/shipment')->load(Mage::registry('shipmentId'));
		}
	}

}