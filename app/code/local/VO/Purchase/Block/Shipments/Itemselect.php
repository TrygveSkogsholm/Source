<?php

class VO_Purchase_Block_Shipments_Itemselect extends Mage_Adminhtml_Block_Template
{
	public $_itemsJson;

	protected function _construct()
	{
		$this->setTemplate('purchase/shipment/Itemselect.phtml');
		$this->_itemsJson = $this->getJson();
		return parent::_construct();
	}

	public function getJson()
	{
		$purchaseOrder = Mage::registry('order');
		$shipmentId = Mage::registry('shipmentId');
		$items = array();
		foreach ($purchaseOrder->getItems() as $item)
		{
			$items[] = array('id'=>$item->getId(), 'localShipped'=>$item->getShippedQty($shipmentId) ? $item->getShippedQty($shipmentId):0,'qtyTotal'=>$item->getItemQty(),'qtyShipped'=>($item->getAbsoluteShippedQty()),'sku'=>$item->getSku(),'name'=>$item->getName());
		}
		$object = array('id'=>$purchaseOrder->getId(),'items'=>$items);
		return Zend_Json::encode($object);
	}
}