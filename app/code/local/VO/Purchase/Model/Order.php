<?php

class VO_Purchase_Model_Order extends Mage_Core_Model_Abstract
{
	public $showComment = false;

	public function _construct()
	{
		parent::_construct();
		$this->_init('purchase/order');
	}

	/**
	 * Get supplier model
	 * @return VO_Purchase_Model_Supplier $supplier
	 * @author Trygve, Velo Orange
	 */
	public function getSupplier()
	{
		$supplier = Mage::getModel('purchase/supplier')->load($this->getsupplier_id());
		return $supplier;
	}

	/**
	 * Get the from or to address for a purchase order
	 * @param string $whichOne 'from' or 'to'
	 * @return array $address indexes are name,contact,street1,street2,zip,city,state,country
	 * @author Trygve, Velo Orange
	 */
	public function getAddress($whichOne)
	{
		switch ($whichOne) {
			case 'from':
				if ($this->isCustomShipAddresses($whichOne) == TRUE)
				{
					$address = array('name'=>$this->getship_from_name(),'contact'=>$this->getship_from_contact(), 'street1' => $this->getship_from_address1(),'street2' => $this->getship_from_address2(),'zip' => $this->getship_from_zip(),'city' => $this->getship_from_city(),'state' => $this->getship_from_state(),'country' => $this->getship_from_country());
				}
				else
				{
					$supplier = $this->getSupplier();
					$address = array('name'=>$supplier->getcompany_name(),'contact'=>$supplier->getcontact_name(), 'street1' => $supplier->getaddress_street1(),'street2' => $supplier->getaddress_street2(),'zip' => $supplier->getaddress_zip(),'city' => $supplier->getaddress_city(),'state' => $supplier->getaddress_state(),'country' => $supplier->getaddress_country());
				}
				break;

			case 'to':
				if ($this->isCustomShipAddresses($whichOne) == TRUE)
				{
					$address = array('name'=>$this->getship_to_name(),'contact'=>$this->getship_to_contact(),'street1' => $this->getship_to_address1(),'street2' => $this->getship_to_address2(),'zip' => $this->getship_to_zip(),'city' => $this->getship_to_city(),'state' => $this->getship_to_state(),'country' => $this->getship_to_country());
				}
				else
				{
					$config = Mage::getStoreConfig('orders/shipping_address');
					$state = $config['state'];
					$address = array('name'=>$config['name'],'contact'=>$config['contact'],'street1' => $config['street1'],'street2' => $config['street2'],'zip' => $config['zip'],'city' => $config['city'],'state' => $state,'country' => $config['country']);
				}
				break;

			default:
				return NULL;
				break;
		}
		return $address;
	}


	/**
	 * Checks to see if the PO is using custom ship to or from addresses,
	 * that is if any of the fields for the custom override are populated it returns
	 * true.
	 * @param string $whichOne 'from' or 'to'
	 * @return bool
	 * @author Trygve, Velo Orange
	 */
	public function isCustomShipAddresses($whichOne)
	{
		if ($whichOne == 'from')
		{
			$address1 = $this->getship_from_address1();
			$address2 = $this->getship_from_address2();
			$city = $this->getship_from_city();
			$zip = $this->getship_from_zip();
			$state = $this->getship_from_state();
			$country = $this->getship_from_country();
			$name = $this->getship_from_name();
			$contact = $this->getship_from_contact();
		}
		else
		{
			$address1 = $this->getship_to_address1();
			$address2 = $this->getship_to_address2();
			$city = $this->getship_to_city();
			$zip = $this->getship_to_zip();
			$state = $this->getship_to_state();
			$country = $this->getship_to_country();
			$name = $this->getship_to_name();
			$contact = $this->getship_to_contact();
		}

		if (!empty($address1) || !empty($address2) || !empty($city) || !empty($zip) || !empty($state) || !empty($country) || !empty($name) || !empty($contact))
		{
			return true;
		}
		else
		{
			return false;
		}
	}


	/**
	 * Get po status
	 * @param string $info determine if you want the database value or the label
	 * @return string|int $status
	 * @author Trygve, Velo Orange
	 */
	public function Status($info = 'integer')
	{
		if ($info == 'integer')
		{
			$status = $this->getData('status');
			return $status;
		}
		else
		{
			$options = Mage::helper('purchase')->getStatusOptions();
			return $options[$this->Status('integer')];
		}
	}

	public function getPercentShipped()
	{
		$totalQty = 0;
		$totalShipped = 0;
		foreach ($this->getItems() as $item)
		{
			$totalQty += $item->getItemQty();
			$totalShipped += $item->getShippedQty();
		}
		return round(($totalShipped/$totalQty)*100);
	}

	/**
	 * Set po status
	 * @param string|int $status
	 * @author Trygve, Velo Orange
	 */
	public function setOrderStatus($status)
	{
		if (is_int($status) == true)
		{
			$this->setstatus($status);
		}
		else
		{
			foreach (Mage::helper('purchase')->getStatusOptions() as $key => $value)
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

	/**
	 * update the PO status based on the fairly obvious criteria of their names
	 * @author Trygve, Velo Orange
	 */
	public function updateStatus()
	{
		//planning and sent must be manually activated so they may be ignored
		if ($this->Status() >= 2 && $this->getItems()->count() > 0)
		{

			$allItemsShipped = true;
			$allItemsReceived = true;
			$someItemsShipped = false;
			$someItemsReceived = false;
			foreach ($this->getItems() as $item)
			{
				if ($item->getShippedQty() != $item->getItemQty())
				{
					$allItemsShipped = false;
				}
				else
				{
					$item->setis_shipped(true);
				}

				//Some at least?
				if ($item->getShippedQty() > 0)
				{
					$someItemsShipped = true;
				}

				if ($item->getReceivedQty() != $item->getItemQty())
				{
					$allItemsReceived = false;
				}
				else
				{
					$item->setis_shipped(true);
					$item->setis_received(true);
				}
				//Some at least?
				if ($item->getReceivedQty() > 0)
				{
					$someItemsReceived = true;
				}
				$item->save();
			}

			//Now the logic, yes it is supposed to overwrite the lower valued statusi
			$this->setOrderStatus(2);

			if ($someItemsShipped == TRUE)
			{
				$this->setOrderStatus(3);
			}
			if ($allItemsShipped == TRUE)
			{
				$this->setOrderStatus(4);
			}
			if ($someItemsReceived == TRUE)
			{
				$this->setOrderStatus(5);
			}
			if ($allItemsReceived == TRUE)
			{
				$this->setOrderStatus(6);
			}

			if ($this->isPaid() == true && $allItemsReceived == true)
			{
				$this->setOrderStatus(7);
			}
		}
		$this->save();
		return;
	}

	/**
	 * Get all the items that belong to this purchase order
	 * @return VO_Purchase_Model_Order_Product Collection $collection
	 * @author Trygve, Velo Orange
	 */
	public function getItems()
	{
		$collection = Mage::getModel('purchase/order_product')->getCollection()
		->addFieldToFilter('po_id',$this->getId())
		->join('catalog/product','`catalog/product`.entity_id=`main_table`.product_id','sku')
		->join('cataloginventory/stock_item','`cataloginventory/stock_item`.product_id=`main_table`.product_id',array('stock'=>'qty'))
		->setOrder('sku','asc');
		return $collection;
	}

	public function getItemsWithExtendedData()
	{
		$collection = $this->getItems();
		//Include name
		$collection->getSelect()->join(array('catalog/varchar'=>'catalog_product_entity_varchar'), '`catalog/varchar`.entity_id=`main_table`.product_id', array('name'=>'value'));
		$collection->addFieldToFilter('attribute_id', 60);
		return $collection;
	}

	/**
	 * Add an item to a purchase order using supplier product ID.
	 * @param int $supProductId Supplier Product ID
	 * @param int $qty default 1
	 * @return bool $succesReport
	 * @author Trygve, Velo Orange
	 */
	public function addItem($supProductId,$qty = 1)
	{
		if (empty($qty))
		{
			$qty = 1;
		}
		try {
			$supplierProduct = $this->getSupplier()->getItem((int)$supProductId);
			$item = Mage::getModel('purchase/order_product')
			->setproduct_id($supplierProduct->getProductId())
			->setpo_id($this->getId())
			->setqty($qty)
			->setfirst_cost($supplierProduct->getFirstCost());
			$item->save();
			foreach ($supplierProduct->getDefaultExtendedCosts() as $extendedCost)
			{
				$item->addExtendedCost($extendedCost->getCost(),$extendedCost->getName(),$extendedCost->getDescription(),$extendedCost->isDisplayedToSupplier());
			}
			return $item;
		} catch (Exception $e) {
			return false;
		}
	}

	/**
	 * Add an item to a shipment object by creating a shipment_/product object
	 * @param int $shipmentId
	 * @param int $orderProductId Order Product ID
	 * @param int $qty default 1
	 * @return bool $succesReport
	 * @author Trygve, Velo Orange
	 */
	public function shipItem($shipmentId,$orderProductId,$qty = 1)
	{
		try {
			$shipment = Mage::getModel('purchase/shipment')->load($shipmentId);
			if ($shipment->Status() > 1)
			{
				return false;
			}
			$shipment->addItem($orderProductId,$qty);
			return true;
		} catch (Exception $e) {
			return false;
		}
	}

	/**
	 * Retrieve all shipment objects which reference this order (by having an item in them from here)
	 * @return collection VO_Purchase_Model_Shipment
	 * @author Trygve, Velo Orange
	 */
	public function getAllShipments()
	{
		$shipmentObjects = array();
		$shipments = array();
		foreach ($this->getItems() as $orderItem)
		{
			foreach ($orderItem->getAllShipmentObjects() as $shipmentObject)
			{
				$shipments[] = $shipmentObject->getshipment_id();
			}
		}
		$shipments = array_unique($shipments);
		return Mage::getModel('purchase/shipment')->getCollection()->addFieldToFilter('id',array('in'=>$shipments));
	}

	/**
	 * Returns products which the supplier sells but are not yet in this PO.
	 * @return array VO_Purchase_Model_Order_Product $collection
	 * @author Trygve, Velo Orange
	 */
	public function getAvailableProducts()
	{
		$current = array(0);
		try {
			foreach ($this->getItems() as $item)
			{
				$current[] = $item->getProductId();
			}
		} catch (Exception $e) {
		}

		$collection = $this->getSupplier()->getItems()
		->join('catalog/product','`catalog/product`.entity_id=`main_table`.product_id','sku')
		->join('cataloginventory/stock_item','`cataloginventory/stock_item`.product_id=`main_table`.product_id',array('stock'=>'qty'))
		->addFieldToFilter('`main_table`.product_id',array('nin' => $current))
		->setOrder('sku','asc');
		
		$collection->getSelect()->join(array('catalog/varchar'=>'catalog_product_entity_varchar'), '`catalog/varchar`.entity_id=`main_table`.product_id', array('name'=>'value'));
		$collection->addFieldToFilter('attribute_id', 60);
		return $collection;
	}

	/**
	 * Load a product by it's mage ID, only products in this order are considered.
	 * @param int $id mage product ID
	 * @return VO_Purchase_Model_Order_Product
	 * @author Trygve, Velo Orange
	 */
	public function getProductById($id)
	{
		return $this->getItems()->addFieldToFilter('product_id',$id)->getFirstItem();
	}

	/**
	 * Retreive order comments
	 * @return string
	 * @author Trygve, Velo Orange
	 */
	public function getOrderComments()
	{
		return $this->getcomments();
	}

	/**
	 * Checks to see if duty should be factored into this order
	 * based on whether the ship from, to countries are different.
	 * @return bool $isDuty
	 * @author Trygve, Velo Orange
	 */
	public function isDuty()
	{
		$to = $this->getAddress('to');
		$from = $this->getAddress('from');
		if ($to['country'] != $from['country'])
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * calculates order total without duty
	 * @return float $cost
	 * @author Trygve, Velo Orange
	 */
	public function getSubtotal()
	{
		$cost = 0;
		foreach ($this->getItems() as $item)
		{
			$cost += $item->getSubtotal();
		}
		return $cost;
	}

	/**
	 * calculates order total with duty
	 * @return float $cost
	 * @author Trygve, Velo Orange
	 */
	public function getGrandtotal()
	{
		$cost = 0;
		foreach ($this->getItems() as $item)
		{
			$cost += $item->getGrandtotal();
		}
		return $cost;
	}

	public function getTotalDuty()
	{
		return ($this->getGrandtotal() - $this->getSubtotal());
	}

	public function getPaymentMethod()
	{
		return $this->getpayment_method();
	}

	public function send()
	{
		$this->setdate_sent(now());
		$this->setOrderStatus(2);
		$this->updateStatus();
		//$this->save();
		return;
	}

	public function getDateSent()
	{
		return $this->getdate_sent();
	}

	public function isPaid()
	{
		return $this->getis_paid();
	}
}