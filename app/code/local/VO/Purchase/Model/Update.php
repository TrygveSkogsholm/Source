<?php

class VO_Purchase_Model_Update extends Mage_Core_Model_Abstract
{
	private $qtys = array();

	public function repopulateData()
	{
		//This function should fill in data that exists in other objects but was not correctly added to the stock
		//movements.
		ini_set('memory_limit', '1024M');
		//Start with shipments. Note we want to exclude purchase orders which already made their way into the tool.
		$existing = array_unique(Mage::getModel('purchase/stockmovement')->getCollection()->addFieldToFilter('type','Restocked')->getColumnValues('order_num'));
		foreach(Mage::getModel('purchase/shipment')->getCollection()->addFieldToFilter('status',3) as $shipment)
		{
			foreach($shipment->getItems() as $shipmentItem)
			{
				if (in_array($shipmentItem->getPurchaseOrder()->getId(), $existing) == false)
				{
					//echo $shipmentItem->getPurchaseOrder()->getId().' - '.$shipmentItem->getItemQty().'<br>';
					Mage::getModel('purchase/stockmovement')->addStockMovement(0,$shipmentItem->getItemQty(),$shipmentItem->getProductId(),'Restocked',$shipmentItem->getPurchaseOrder()->getId(),$shipment->getdate_received());
				}
			}
		}



		//Final step, fill in sales data from BEFORE
		$lastDate = Mage::getModel('purchase/stockmovement')->getCollection()->addFieldToFilter('type','Ordered')->setOrder('date','asc')->getFirstItem()->getdate();
		foreach(Mage::getModel('sales/order_item')->getCollection()->addFieldToFilter('created_at',array('to'=>$lastDate)) as $item)
		{
			if($item->getproduct_type() == 'simple' && ($item->getQtyOrdered()-$item->getqty_refunded()) > 0)
			{
				Mage::getModel('purchase/stockmovement')->addStockMovement(0,($item->getQtyOrdered()-$item->getqty_refunded())*-1,$item->getproduct_id(),'Ordered',$item->getOrder()->getRealOrderId(),$item->getcreated_at());
				//echo $item->getcreated_at().' '.($item->getQtyOrdered()-$item->getqty_refunded()).' '.$item->getproduct_id().'<br>';
			}
		}

		$stocks = Mage::getModel('cataloginventory/stock_item')->getCollection()->toArray(array('product_id','qty'));
		foreach($stocks['items'] as $stockItem)
		{
			$this->qtys[$stockItem['product_id']] = $stockItem['qty'];
		}
		unset($stocks);

		/*
		 * Next step is to fill in the stock after data, this will not be perfect because return data is not perfect, in the future we should do it manually or fix it.
		 */
		foreach(Mage::getModel('purchase/stockmovement')->getCollection()->setOrder('date') as $movement)
		{
			//going 'back in time' so stock after change is actually stock before we change it.
			$movement->setstockafter($this->qtys[$movement->getproduct_id()]);
			$this->qtys[$movement->getproduct_id()] -= $movement->getmagnitude();
			if ($this->qtys[$movement->getproduct_id()] < 0)
			{
				$this->qtys[$movement->getproduct_id()] = 0;
			}
			$movement->save();
		}
	}
}