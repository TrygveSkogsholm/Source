<?php

class VO_Purchase_Model_Order_Product extends Mage_Core_Model_Abstract
{
	public $_order;
	public $read;

	public function _construct()
	{
		parent::_construct();
		$this->_init('purchase/order_product');
	}
	
	public function delete()
	{
		foreach($this->getExtendedCosts() as $extendedCost)
		{
			$extendedCost->delete();
		}
		return parent::delete();
	}

	public function getProductId()
	{
		return $this->getproduct_id();
	}

	/*
	 * Attention, I am trying to switch away from fetching the magento product and saying get->X it takes
	 * too long (I know frustrating)
	 *
	 * Direct queries are how it's going to go for this and the PO module getting data from magento
	 * products if possible. This module itself seems to be fast enough, I don't know what they're doing
	 * or why it's slow and it's not worth looking into when you know you can't change it.
	 *
	 * You should be aware then of some things I found out about the database.
	 * Attribute IDs:
	 * 60 = name
	 * 63 = sku
	 * 64 = price
	 * 68 = cost
	 * 543 = hts (only for this install)
	 * 541 = upc (only for this install)
	 *
	 */
	public function getDatabaseRead()
	{
		if (isset($this->read) == TRUE )
		return $this->read;
		else
		{
			$this->read = Mage::getSingleton('core/resource')->getConnection('core_read');
			return $this->read;
		}
	}

	public function getMagentoProduct()
	{
		return Mage::getModel('catalog/product')->load($this->getProductId());
	}

	public function getSku()
	{
		//Modifications to take advantage of collection selects
		if ($this->getData('sku') == NULL)
		{
			$read = $this->getDatabaseRead();
			$query = 'SELECT sku FROM catalog_product_entity WHERE entity_id = '.$this->getproduct_id();
			$result = $read->fetchAll($query);
			$this->setData('sku',$result[0]['sku']);
		}
		return $this->getData('sku');
		//Slow
		//return $this->getMagentoProduct()->getSku();
	}

	public function getName()
	{
		//Modifications to take advantage of collection selects
		if ($this->getData('name') == NULL)
		{
			$read = $this->getDatabaseRead();
			$query = 'SELECT value FROM catalog_product_entity_varchar WHERE attribute_id = 60 AND entity_id = '.$this->getproduct_id();
			$result = $read->fetchAll($query);
			$this->setData('name',$result[0]['value']);
		}
		return $this->getData('name');
		//Slow
		//return $this->getMagentoProduct()->getName();
	}

	public function getStock()
	{
		//Modifications to take advantage of collection selects
		if ($this->getData('stock') == NULL)
		{
			$read = $this->getDatabaseRead();
			$query = 'SELECT qty FROM cataloginventory_stock_item WHERE product_id = '.$this->getproduct_id();
			$result = $read->fetchAll($query);
			$this->setData('stock',$result[0]['qty']);
		}
		return $this->getData('stock');
		//return $this->getMagentoProduct()->getStockItem()->getQty();
	}

	public function getModelString()
	{
		return $this->getSupplierProduct()->getModelString();
	}
	
	public function getCaseQty()
	{
		return $this->getSupplierProduct()->getCaseQty();
	}

	public function getHtsCode()
	{
		$read = $this->getDatabaseRead();
		$query = 'SELECT value FROM catalog_product_entity_varchar WHERE attribute_id = 543 AND entity_id = '.$this->getproduct_id();
		$result = $read->fetchAll($query);
		$code = $result[0]['value'];
		if ($code != '')
		{return $code;}
		else
		{//Mage::getSingleton('adminhtml/session')->addError('No HTS '.$code.' code for  '.$this->getSku());
		}
	}

	public function getUpcCode()
	{
		$read = $this->getDatabaseRead();
		$query = 'SELECT value FROM catalog_product_entity_varchar WHERE attribute_id = 541 AND entity_id = '.$this->getproduct_id();
		$result = $read->fetchAll($query);
		return $result[0]['value'];
		//return $this->getMagentoProduct()->getData('upc');
	}

	public function getDutyRate()
	{
		if ($this->getOrder()->isDuty() == true)
		{
			try {
				$hts = Mage::getModel('purchase/hts')->load($this->getHtsCode());

				return $hts->getDutyRate();
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError('Error loading  '.$this->getSku());
				return 1;
			}
		}
		else
		{
			return 1;
		}
	}

	/**
	 * Retrieve the order this item belongs to
	 * @return VO_Purchase_Model_Order $order
	 * @author Trygve, Velo Orange
	 */
	public function getOrder()
	{
		if (isset($this->_order))
		{
			return $this->_order;
		}
		else
		{
			$order = Mage::getModel('purchase/order')->load($this->getpo_id());
			$this->_order = $order;
			return $order;
		}
	}

	/**
	 * Retrieve all shipment object items that belong to this product.
	 * @return VO_Purchase_Model_Shipment_Product $model
	 * @author Trygve, Velo Orange
	 */
	public function getAllShipmentObjects()
	{
		return Mage::getModel('purchase/shipment_product')->getCollection()
		->addFieldToFilter('order_product_id',$this->getId());
	}

	/**
	 * Retrieve specific shipment object items that belong to this product.
	 * @return VO_Purchase_Model_Shipment_Product $model
	 * @author Trygve, Velo Orange
	 */
	public function getSpecificShipmentObject($shipmentId)
	{
		return Mage::getModel('purchase/shipment_product')->getCollection()
		->addFieldToFilter('order_product_id',$this->getId())
		->addFieldToFilter('shipment_id',$shipmentId)
		->getFirstItem();
	}

	/**
	 * Retrieves the shipped qty, NOTE! when passing a specific shipment ID the function
	 * will not check to see if the shipment is only being planned!.
	 * @return VO_Purchase_Model_Shipment_Product $model
	 * @author Trygve, Velo Orange
	 */
	public function getShippedQty($shipment = null)
	{
		$count = 0;
		if ($shipment == null)
		{
			foreach ($this->getShippedShipmentObjects() as $shipmentObject)
			{
				$count += $shipmentObject->getItemQty();
			}
		}
		else
		{
			$count = $this->getSpecificShipmentObject($shipment)->getItemQty();
		}
		return $count;
	}

	//A subtle shade of meaning, the shipped qty above will not return the count of received objects when
	// they must be 'shipped' to be 'received'
	public function getAbsoluteShippedQty()
	{
		return $this->getReceivedQty() + $this->getShippedQty();
	}

	public function getUnshippedQty()
	{
		if ($shipped = $this->getAbsoluteShippedQty())
		{
			return $this->getItemQty() - $shipped;
		}
		else
		{
			return $this->getItemQty();
		}
	}

	//This is to retreive not only the shipped and received but what is being planned as well.
	public function getQtyInShipments()
	{
		$qty = 0;
		foreach ($this->getAllShipmentObjects() as $shipmentObject)
		{
			$qty += $shipmentObject->getItemQty();
		}
		return $qty;
	}

	public function getReceivedQty()
	{
		$count = 0;
		foreach ($this->getAllShipmentObjects() as $shipmentObject)
		{
			if ($shipmentObject->getShipment()->Status() == 3)
			{
				$count += $shipmentObject->getItemQty();
			}
		}
		return $count;
	}

	public function getShippedShipmentObjects()
	{
		$objects = array();
		foreach ($this->getAllShipmentObjects() as $shipmentObject)
		{
			if ($shipmentObject->getShipment()->Status() == 2)
			{
				$objects[] = $shipmentObject;
			}
		}
		return $objects;
	}

	public function getReceivedShipmentObjects()
	{
		$objects = array();
		foreach ($this->getAllShipmentObjects() as $shipmentObject)
		{
			if ($shipmentObject->getShipment()->Status() == 3)
			{
				$objects[] = $shipmentObject;
			}
		}
		return $objects;
	}

	public function getSupplierProduct()
	{
		return $this->getOrder()->getSupplier()->getProductById($this->getProductId());
	}

	public function getFirstCost()
	{
		return $this->getfirst_cost();
	}

	public function getSecondCost()
	{
		//Remember this is not included in the PO, only for internal use for landed cost
		$cost = $this->getFirstCost();
		foreach($this->getExtendedCosts() as $extended)
		{
				$cost += $extended->getCost();
		}
		return $cost;
	}
	
	public function addExtendedCost($cost,$name = NULL, $description = NULL,$displayed=false)
	{
		$extended = Mage::getModel('purchase/order_product_extended');
		$extended->setData(array(
			'cost'=>$cost,
			'po_item_id'=>$this->getId(),
			'date_modified'=>now(),
			'name'=>$name,
			'description'=>$description,
			'display_to_supplier'=>$displayed
		));
		$extended->save();
		return $extended;
	}

	public function getExtendedCosts()
	{
		return Mage::getModel('purchase/order_product_extended')->getCollection()->addFieldToFilter('po_item_id',$this->getId());
	}
	
	public function getDisplayedExtendedCost()
	{
		return $this->getExtendedCosts()->addFieldToFilter('display_to_supplier',1);
	}
	
	public function updateFirstCost($cost)
	{
		$this->setFirstCost($cost);
		$supplierProduct = $this->getSupplierProduct();
		$supplierProduct->setFirstCost($cost);
		$supplierProduct->save();
		$this->save();
	}

	public function setFirstCost($cost)
	{
		$this->setfirst_cost($cost);
	}

	public function getItemQty()
	{
		return $this->getqty();
	}

	public function setItemQty($qty)
	{
		$this->setqty((int)$qty);
	}

	public function isEditable()
	{
		//Changed so that even though it is not recommended orders can be changed after they are sent.
		//Changed again so that again sent is not allowed, but allow users to reset to sent with an edit button.
		//return !($this->isShipped() == TRUE || $this->isReceived() == TRUE);
		/*if ($this->isReceived() == TRUE)
		 {
			return FALSE;
			}
			else
			{
			return TRUE;
			}*/
		if ($this->getOrder()->getStatus() == 1)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	public function isShipped()
	{
		if ($this->getis_shipped() == 0)
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	public function isReceived()
	{
		if ($this->getis_received() == 0)
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	public function getOnOrder()
	{
		return $this->getSupplierProduct()->getOnOrder();
	}

	public function getSubtotal()
	{
		return $this->getItemQty() * $this->getFirstCost();
	}

	public function getGrandtotal()
	{
		return $this->getSubtotal() * $this->getDutyRate();
	}
}