<?php

class VO_Purchase_Block_Shipments_Popups extends Mage_Adminhtml_Block_Template
{
	public $_purchaseOrders;
	public $_shipment;

	protected function _construct()
	{
		$this->setTemplate('purchase/shipment/popups.phtml');
		$this->_shipment = $this->getShipment();
		$this->_purchaseOrders = $this->getPurchaseOrders();
		return parent::_construct();
	}

	public function getShipment()
	{
		if ( Mage::registry('shipment') )
		{
			return Mage::registry('shipment');
		}
	}

	public function getPurchaseOrders()
	{
		return $this->_shipment->getPurchaseOrders();
	}
}