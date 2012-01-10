<?php
/**
 *
 * @author Trygve
 *
 */
class VO_Purchase_Model_Price_List_Price extends Mage_Core_Model_Abstract
{
	public function _construct()
	{
		parent::_construct();
		$this->_init('purchase/price_list_price');
	}

	public function getListId()
	{
		return $this->getlist_id();
	}

	public function getList()
	{
		return Mage::getModel('purchase/price_list')->load($this->getListId());
	}

	public function getSku()
	{
		return $this->getData('sku');
	}

	public function getName()
	{
		return $this->getData('name');
	}

	public function getProductId()
	{
		return $this->getproduct_id();
	}

	public function getMagentoProduct()
	{
		return Mage::getModel('catalog/product')->load($this->getProductId());
	}

	public function getProductAdditional()
	{
		return Mage::getModel('purchase/productadditional')->loadByProductId($this->getProductId());
	}

	public function getDistributorPrice()
	{
		return $this->getProductAdditional()->getDistributorCost();
	}

	public function getWholesalePrice()
	{
		return $this->getProductAdditional()->getWholesaleCost();
	}

	public function getOEMPrice()
	{
		return $this->getProductAdditional()->getOEMCost();
	}

	public function getRetailPrice()
	{
		return $this->getProductAdditional()->getRetailCost();
	}
}