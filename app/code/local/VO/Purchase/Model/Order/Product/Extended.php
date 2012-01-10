<?php

class VO_Purchase_Model_Order_Product_Extended extends Mage_Core_Model_Abstract
{
	/*
	 * This class refer to the extended cost which together with first cost constitute second cost.
	 */

	public function _construct()
	{
		parent::_construct();
		$this->_init('purchase/order_product_extended');
	}
	
	public function save()
	{
		$this->setData('date_modified',now());
		parent::save();
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
	
	public function getPoItem()
	{
		return Mage::getModel('puchase/order_product')->load($this->getData('po_item_id'));
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