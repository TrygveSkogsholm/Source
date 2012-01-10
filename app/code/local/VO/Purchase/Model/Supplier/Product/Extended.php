<?php

class VO_Purchase_Model_Supplier_Product_Extended extends Mage_Core_Model_Abstract
{
	/*
	 * This class refer to the extended cost which together with first cost constitute second cost.
	 */

	public function _construct()
	{
		parent::_construct();
		$this->_init('purchase/supplier_product_extended');
	}
	
	public function __toString()
	{
		return $this->getCost();
	}
	
	public function getCost()
	{
		return $this->getData('cost');
	}
	
	public function getName()
	{
		return $this->getData('name');
	}
	
	public function getDescription()
	{
		return $this->getData('description');
	}
	
	public function getSupProduct()
	{
		return Mage::getModel('puchase/supplier_product')->load($this->getData('sup_item_id'));
	}
	
	public function isDisplayedToSupplier()
	{
		return $this->getData('display_to_supplier');
	}
	
	public function getDateModified()
	{
		return $this->getData('date_modified');
	}
}