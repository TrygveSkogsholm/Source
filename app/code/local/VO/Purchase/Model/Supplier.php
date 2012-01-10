<?php

class VO_Purchase_Model_Supplier extends Mage_Core_Model_Abstract
{
	public function _construct()
	{
		parent::_construct();
		$this->_init('purchase/supplier');
	}

	//Getters
	/**
	 * Get Company Name
	 * @return string $company name
	 * @author Trygve, Velo Orange
	 */
	public function getName()
	{
		return $this->getcompany_name();
	}

	//Setters
	/**
	 * Set Company Name
	 * param string $name
	 * @author Trygve, Velo Orange
	 */
	public function setName($name)
	{
		return $this->setcompany_name($name);
	}

	//Core functions
	/**
	 * Add product supplier association
	 *
	 * @param int|Mage_Catalog_Model_Product $product magento product-id or model instance
	 * @param string $modelNumber product model
	 * @param float|int $firstCost product first cost
	 * @return bool $succesReport
	 * @author Trygve, Velo Orange
	 */
	public function addProduct($product,$modelNumber = NULL,$firstCost)
	{
		$succesReport = false;
		if ($product instanceof Mage_Catalog_Model_Product)
		{
			$id = $product->getId();
		}
		else
		{
			$id = $product;
		}

		$data = $this->getProductById($id)->getData();
		if (empty($data))
		{
			try
			{
				$SupplierProduct = Mage::getModel('purchase/supplier_product')
				->setsupplier_id($this->getId())
				->setproduct_id($id)
				->setmodel($modelNumber)
				->setfirst_cost($firstCost)
				->save();
				$succesReport = true;
			}
			catch (Exception $e)
			{
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
		}

		return $succesReport;
	}

	/**
	 * Add up all the delays before stock arrives
	 *
	 * @return float $orderDelay (in days)
	 * @author Trygve, Velo Orange
	 */
	public function getOrderingDelay()
	{
		$orderDelay = $this->getbuffer_time() + $this->getlead_time();
		$orderDelay += $this->getShippingDelay();
		return $orderDelay;
	}

	public function getShippingDelay()
	{
		$shipDelay = $this->getshipping_delay();
		if ($shipDelay == 0 || empty($shipDelay) == true )
		{
			$Delay = Mage::getStoreConfig('orders/shipping/'.strtolower($this->getdefault_method()).'');
		}
		else
		{
			$Delay = $shipDelay;
		}
		return $Delay;
	}

	/**
	 * Retrieve addresses with standard array indexes
	 *
	 * @return array $address
	 * @author Trygve, Velo Orange
	 */
	public function getAddress()
	{
		$address = array('name'=>$this->getcompany_name(),'contact'=>$this->getcontact_name(), 'street1' => $this->getaddress_street1(),'street2' => $this->getaddress_street2(),'zip' => $this->getaddress_zip(),'city' => $this->getaddress_city(),'state' => $this->getaddress_state(),'country' => $this->getaddress_country());
		return $address;
	}

	/**
	 * Get all the items that belong to this supplier
	 * @return VO_Purchase_Model_Supplier_Product Collection $collection
	 * @author Trygve, Velo Orange
	 */
	public function getItems()
	{
		$collection = Mage::getModel('purchase/supplier_product')->getCollection()
		->addFieldToFilter('supplier_id',$this->getId());
		return $collection;
	}

	/**
	 * Get a particular item which belongs to this supplier
	 * @param $id int supplier product ID
	 * @return VO_Purchase_Model_Supplier_Product Instance $item
	 * @author Trygve, Velo Orange
	 */
	public function getItem($id)
	{
		$item = Mage::getModel('purchase/supplier_product')->load($id);
		if ($item->getsupplier_id() == $this->getId())
		{
			return $item;
		}
		else {return NULL;}
	}

	public function getProductById($id)
	{
		return $this->getItems()->addFieldToFilter('product_id',$id)->getFirstItem();
	}

	public function getUnassociatedProducts()
	{
		$currentSupplierProducts = array();
		foreach ($this->getItems() as $supProduct)
		{
			$currentSupplierProducts[] = $supProduct->getproduct_id();
		}
		$collection = Mage::getModel('catalog/product')->getCollection()
		->addAttributeToSelect('sku')
		->addAttributeToSelect('name')
		->addFieldToFilter('type_id','simple');
		if (!empty($currentSupplierProducts))
		{
			$collection->addFieldToFilter('entity_id', array('nin' => $currentSupplierProducts));
		}
		return $collection;
	}
}