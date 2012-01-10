<?php

class VO_Purchase_Model_Supplier_Product extends Mage_Core_Model_Abstract
{
	public $read;

	public function _construct()
	{
		parent::_construct();
		$this->_init('purchase/supplier_product');
	}

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
		//return Mage::getModel('catalog/product')->load($this->getproduct_id())->getSku();
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
		return $this->getmodel();
	}

	public function getMagentoProduct()
	{
		return Mage::getModel('catalog/product')->load($this->getProductId());
	}

	public function getProductId()
	{
		return $this->getproduct_id();
	}

	public function getFirstCost()
	{
		return $this->getfirst_cost();
	}

	public function addExtendedCost($cost,$name = NULL, $description = NULL,$displayed=false)
	{
		$extended = Mage::getModel('purchase/supplier_product_extended');
		$extended->setData(array(
			'cost'=>$cost,
			'sup_item_id'=>$this->getId(),
			'date_modified'=>now(),
			'name'=>$name,
			'description'=>$description,
			'display_to_supplier'=>$displayed
		));
		$extended->save();
		return $extended;
	}

	public function getDefaultExtendedCosts()
	{
		return Mage::getModel('purchase/supplier_product_extended')->getCollection()->addFieldToFilter('sup_item_id',$this->getId());
	}

	public function setFirstCost($cost)
	{
		$this->setfirst_cost($cost);
	}

	public function getSupplier()
	{
		return Mage::getModel('purchase/supplier')->load($this->getsupplier_id());
	}

	/**
	 * Retrieve the number of outstanding itmes belong to orders
	 * Outstanding being ordered but not received.
	 * @return int $number
	 * @author Trygve, Velo Orange
	 */
	public function getOnOrder()
	{
		$productId = $this->getProductId();

		$collection = Mage::getModel('purchase/order_product')->getCollection()
		->addFieldToFilter('product_id',$productId)
		->addFieldToFilter('is_received',0);

		//Must get partially recieved items, i.e. get the count
		//of the above that have already arrived.
		//Note the above filter already ignores items who are completly received.
		$count = 0;
		$partialReceived = 0;
		foreach ($collection as $item)
		{
			foreach ($item->getAllShipmentObjects() as $shippedItem)
			{
				if($shippedItem->getShipment()->IsReceived() == TRUE)
				{
					$partialReceived += $shippedItem->getItemQty();
				}
			}
			$count += $item->getItemQty() - $partialReceived;
		}
		//Partial received is all the items that aren't actually outstanding but still aren't completly receieved
		//so they got into the collection filters.
		//return $count - $partialReceived;

		return $count;
	}
	
	public function getCaseQty()
	{
		return $this->getData('case_qty');
	}
	
	public function setCaseQty($qty)
	{
		$this->setData('case_qty',$qty);
	}
}