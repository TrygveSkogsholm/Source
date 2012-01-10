<?php

class VO_Purchase_Model_Shipment_Product extends Mage_Core_Model_Abstract
{
	public $read;

	public function _construct()
	{
		parent::_construct();
		$this->_init('purchase/shipment_product');
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

	public function getShipment()
	{
		return Mage::getModel('purchase/shipment')->load($this->getshipment_id());
	}

	public function IsReceived()
	{
		return $this->getShipment()->IsReceived();
	}

	public function calculateLandedCost($freight = NULL,$shipmentTotal = NULL)
	{
		if (empty($shipmentTotal))
		{
			$shipmentTotal = $this->getShipment()->getExtendedGrandtotal();
		}
		if (empty($freight))
		{
			$freight = $this->getShipment()->getFreight();
		}
		$itemPrice = $this->getExtendedGrandtotal()/$this->getItemQty();
		$landedCost = ( 1 + ($freight/$shipmentTotal) ) * $itemPrice;
		$this->setlanded_cost($landedCost);
		return $landedCost;
	}

	public function getLandedCost()
	{
		return $this->getlanded_cost();
	}

	public function getSubtotal()
	{
		return $this->getItemQty() * $this->getOrderProduct()->getFirstCost();
	}
	
	/**
	 * subtotal including extended costs, subtotal does not include duty.
	 * @return number
	 */
	public function getExtendedSubtotal()
	{
		return $this->getItemQty() * $this->getOrderProduct()->getSecondCost();
	}
	
	/**
	 * returns only the sum of the extended costs themselves by subtracting the base subtotal
	 * @return number
	 */
	public function getTotalExtendedCost()
	{
		return $this->getExtendedSubtotal() - $this->getSubtotal();
	}

	public function getGrandtotal()
	{
		return $this->getSubtotal() * $this->getOrderProduct()->getDutyRate();
	}
	
	public function getExtendedGrandtotal()
	{
		//Doesn't include duty on extended costs.
		return $this->getGrandtotal() + $this->getTotalExtendedCost();
	}

	public function getTotal()
	{
		return $this->getLandedCost() * $this->getItemQty();
	}

	public function getSupplier()
	{
		return $this->getOrder()->getSupplier();
	}

	public function getOrder()
	{
		return $this->getOrderProduct()->getOrder();
	}

	public function setItemQty($qty)
	{
		$this->setqty((int)$qty);
		return;
	}

	public function getItemQty()
	{
		return $this->getqty();
	}

	public function getProductId()
	{
		return $this->getproduct_id();
	}

	public function getMagentoProduct()
	{
		return Mage::getModel('catalog/product')->load($this->getProductId());
	}

	public function getMagentoStockItem()
	{
		return $this->getMagentoProduct()->getStockItem();
	}

	public function getSku()
	{
		if ($this->getData('sku') == NULL)
		{
			$read = $this->getDatabaseRead();
			$query = 'SELECT sku FROM catalog_product_entity WHERE entity_id = '.$this->getproduct_id();
			$result = $read->fetchAll($query);
			$this->setData('sku',$result[0]['sku']);
		}
		return $this->getData('sku');
		//return $this->getMagentoProduct()->getSku();
	}

	public function getName()
	{
		if ($this->getData('name') == NULL)
		{
			$read = $this->getDatabaseRead();
			$query = 'SELECT value FROM catalog_product_entity_varchar WHERE attribute_id = 60 AND entity_id = '.$this->getproduct_id();
			$result = $read->fetchAll($query);
			$this->setData('name',$result[0]['value']);
		}
		return $this->getData('name');
		//return $this->getMagentoProduct()->getName();
	}

	public function getStock()
	{
		$read = $this->getDatabaseRead();
		$query = 'SELECT qty FROM cataloginventory_stock_item WHERE product_id = '.$this->getproduct_id();
		$result = $read->fetchAll($query);
		return $result[0]['qty'];
		//return $this->getMagentoProduct()->getStockItem()->getQty();
	}

	public function getOrderProduct()
	{
		return Mage::getModel('purchase/order_product')->load($this->getorder_product_id());
	}

	public function getPurchaseOrder()
	{
		return $this->getOrderProduct()->getOrder();
	}
}