<?php

class VO_Purchase_Model_Shipment extends Mage_Core_Model_Abstract
{
	public function _construct()
	{
		parent::_construct();
		$this->_init('purchase/shipment');
	}

	//Yea, it's that important
	public function ship($shipDate = NULL, $edoa = NULL)
	{
		//Show stoppers
		if ($this->getItems()->count() < 1)
		{
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('purchase')->__('You want to ship 0 items?....'));
			return;
		}
		if ($this->Status() != 1)
		{
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('purchase')->__('This shipment is... shipped'));
			return;
		}

		//Set Ship Date
		if ($shipDate == NULL)
		{
			$shipDate = now();
		}
		//Calculate default Edoa
		if ($edoa == NULL)
		{
			return $this->getDefaultEdoa($shipDate);
		}

		$this->setdate_shipped($shipDate);
		$this->setedoa($edoa);
		$this->_setStatus(2);
		$this->save();

		//not needed for shipment
		//foreach ($this->getItems() as $item)
		//{
		//	$productAdditional = Mage::getModel('purchase/productadditional')->load($item->getProductId());
		//	$productAdditional->calculateAverageLandedCost();
		//}

		$purchaseOrders = $this->getPurchaseOrders();

		foreach ($purchaseOrders as $purchaseOrder)
		{
			$purchaseOrder->updateStatus();
		}

		return;
	}

	/**
	 * 
	 * This function is crucial to the modules function, it performs all core functions for receiving a shipment
	 * It is intended to be called after (ideally shortly) after the truck has pulled up.
	 * @param datetime(string) $dateReceived
	 */
	public function receive($dateReceived)
	{
		//If status is not shipped
		if ($this->Status() != 2)
		{
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('purchase')->__('This shipment is either already received or hasn\'t been shipped.'));
			return;
		}
		
		$this->setdate_received($dateReceived);

		$freight = $this->getFreight();
		
		//ShipmentTotal = sum of cost + duty
		$shipmentTotal = $this->getExtendedGrandtotal();
		
		$this->_setStatus(3);
		
		//Actually update the magento quantity
		foreach ($this->getItems() as $item)
		{
			$stockItem = $item->getMagentoStockItem();
			$original = $stockItem->getQty();
			$stockItem->setQty($original + $item->getItemQty());
			if ($stockItem->getIsInStock() == FALSE && ($stockItem->getQty()+ $item->getItemQty()) > 0 && Mage::getStoreConfig('orders/restocking_instock'))
			{
				$stockItem->setData('is_in_stock',TRUE);
			}
			$stockItem->save();
			$item->calculateLandedCost($freight,$shipmentTotal);
			$item->save();
			Mage::getModel('purchase/stockmovement')->addStockMovement($original,$item->getItemQty(),$item->getProductId(),'Restocked',$item->getOrderProduct()->getOrder()->getId());
		}
		$this->save();

		//At this point the object is technically received. There are some things we need to update,
		//first among them the average landed cost.
		foreach ($this->getItems() as $item2)
		{
			$productAdditional = $this->loadAdditional($item2->getProductId());
			$productAdditional->calculateAverageLandedCost();
		}
		
		//Second is the PO stati
		$purchaseOrders = $this->getPurchaseOrders();
		foreach ($purchaseOrders as $purchaseOrder)
		{
			$purchaseOrder->updateStatus();
		}
		return;
	}

	public function loadAdditional($id)
	{
		$model = Mage::getModel('purchase/productadditional')->loadByProductId($id);

		if ($model->getproduct_id() != NULL)
		{
			return $model;
		}
		else
		{
			$model->setproduct_id($id);
			return $model;
		}
	}

	public function IsShipped()
	{
		if ($this->getdate_shipped() == NULL)
		{return FALSE;}
		else
		{
			if ($this->IsReceived() == false)
			{
				$this->_setStatus(2);
				$this->save();
			}
			return TRUE;
		}
	}

	public function IsReceived()
	{
		if ($this->getdate_received() == NULL)
		{return FALSE;}
		else
		{
			$this->setStatus(3);
			$this->save();
			return TRUE;
		}
	}

	public function _setStatus($status)
	{
		if (is_int($status) == true)
		{
			$this->setstatus($status);
		}
		else
		{
			foreach (Mage::helper('purchase')->getShipmentStatusOptions() as $key => $value)
			{
				if ($value == $status)
				{
					$this->setstatus($key);
					return;
				}
			}
		}
		return;
	}

	public function Status($info = 'integer')
	{
		if ($info == 'integer')
		{
			$status = $this->getData('status');
			return $status;
		}
		else
		{
			$options = Mage::helper('purchase')->getShipmentStatusOptions();
			return $options[$this->Status()];
		}
	}

	//Orders that could validly be added to the shipment
	public function getValidOrders()
	{
		$randomOrder = $this->getItems()->getFirstItem()->getPurchaseOrder();
		$collection = Mage::getModel('purchase/order')->getCollection();
		$collection ->addFieldToFilter('supplier_id',$this->getsupplier_id());
		$collection ->addFieldToFilter('status',array('lteq'=>3,'gt'=>1));
		return $collection->getAllIds();
		$orderArray = array();
		if ($randomOrder->getId() != NULL)
		{
			foreach ($collection as $order)
			{
				if ($order->getAddress('to')==$randomOrder->getAddress('to') && $order->getAddress('from')==$randomOrder->getAddress('from'))
				{
					$orderArray[] = $order->getId();
				}
			}
		}
		else
		{
			foreach ($collection as $order)
			{
				$orderArray[] = $order->getId();
			}
		}
		return $orderArray;
	}

	public function addItem($orderProductId,$qty=1)
	{
		if ($this->Status() < 2)
		{
			$productId = Mage::getModel('purchase/order_product')->load($orderProductId);
			$orderId = $productId->getOrder()->getId();
			$productId = $productId->getProductId();
			$model = Mage::getModel('purchase/shipment_product');
			$model
			->setshipment_id($this->getId())
			->setproduct_id($productId)
			->setorder_product_id($orderProductId)
			->setqty($qty);
			if (in_array($orderId, $this->getValidOrders()))
			{
				$model->save();
			}
		}
		return;
	}

	public function getItems()
	{
		$collection = Mage::getModel('purchase/shipment_product')->getCollection()
		->addFieldToFilter('shipment_id',$this->getId());
		return $collection;
	}
	
	public function loadCoreItemData()
	{
		$collection = $this->getItems();
		$collection->join('catalog/product','`catalog/product`.entity_id=`main_table`.product_id','sku')
		->setOrder('sku','asc');

		$collection->getSelect()->join(array('catalog/varchar'=>'catalog_product_entity_varchar'), '`catalog/varchar`.entity_id=`main_table`.product_id', array('name'=>'value'));
		$collection->addFieldToFilter('attribute_id', 60);
		return $collection;
	}

	public function getItemsForPurchaseOrder($po)
	{
		if ($po instanceof VO_Purchase_Model_Order)
		{
			return $collection = Mage::getModel('purchase/shipment_product')->getCollection()
			->join('purchase/order_product',
			        'order_product_id=`purchase/order_product`.id',
		        	'po_id')
			->addFieldToFilter('shipment_id',$this->getId())
			->addFieldToFilter('po_id',$po->getId());
		}
		else
		{
			return $collection = Mage::getModel('purchase/shipment_product')->getCollection()
			->join('purchase/order_product',
			        'order_product_id=`purchase/order_product`.id',
		        	'po_id')
			->addFieldToFilter('shipment_id',$this->getId())
			->addFieldToFilter('po_id',$po);
		}

	}

	//Without duty
	public function getSubtotal()
	{
		$subtotal = 0;
		foreach ($this->getItems() as $item)
		{
			$subtotal += $item->getSubtotal();
		}
		return $subtotal;
	}
	//With duty
	public function getGrandtotal()
	{
		$grandtotal = 0;
		foreach ($this->getItems() as $item)
		{
			$grandtotal += $item->getGrandtotal();
		}
		return $grandtotal;
	}
	
	//With duty and extended
	public function getExtendedGrandtotal()
	{
		$extendedGrandtotal = 0;
		foreach ($this->getItems() as $item)
		{
			$extendedGrandtotal += $item->getExtendedGrandtotal();
		}
		return $extendedGrandtotal;
	}
	
	//With duty and freight
	public function getTotal()
	{
		$total = 0;
		foreach ($this->getItems() as $item)
		{
			$total += $item->getTotal();
		}

		return $total;
	}

	public function getPurchaseOrders()
	{
		$duplicateArray = array();
		$orders = array();
		foreach ($this->getItems() as $item)
		{
			$po = $item->getPurchaseOrder();
			$poId = $po->getId();
			if ($po->getId() == NULL)
			{
				$item->delete();
				break;
			}
			if (!in_array($poId, $duplicateArray))
			{
				$duplicateArray[] = $poId;
				$orders[] = $po;
			}
			$po = null;
			$poId = null;
		}
		return $orders;
	}

	public function getSupplier()
	{
		return Mage::getModel('purchase/supplier')->load($this->getsupplier_id());
	}

	public function getDefaultEdoa($shipDate)
	{
		if ($this->getedoa() == NULL)
		{
			$edoa = date( 'Y-m-d H:i:s', Mage::helper('purchase')->calculateNewTime($shipDate,'days',$this->getSupplier()->getShippingDelay()));
		}
		else
		{
			$edoa = $this->getedoa();
		}
		return $edoa;
	}

	public function getFreight()
	{
		return $this->getfreight_cost();
	}
}