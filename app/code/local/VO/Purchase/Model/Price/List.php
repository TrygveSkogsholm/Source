<?php
/**
 * @author Trygve
 *
 */
class VO_Purchase_Model_Price_List extends Mage_Core_Model_Abstract
{
	public function _construct()
	{
		parent::_construct();
		$this->_init('purchase/price_list');
	}

	public function getProducts()
	{
		return Mage::getModel('purchase/price_list_price')->getCollection()
		->addFieldToFilter('list_id',$this->getId());
	}

	public function addProduct($product)
	{
		$price = Mage::getModel('purchase/price_list_price');
		if (!($product instanceof Mage_Catalog_Model_Product))
		{
			$product = Mage::getModel('catalog/product')->load($product);
		}
		if ($this->getProducts()->addFieldToFilter('product_id',$product->getId())->count() == 0)
		{
			$price->setData(array('list_id'=>$this->getId(),'product_id'=>$product->getId(),'sku'=>$product->getSku(),'name'=>$product->getName()));
			$price->save();
		}
		else
		{
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('purchase')->__($product->getSku().' is already in this list.'));
		}
	}

	public function getName()
	{
		return $this->getData('name');
	}
	
	public function getPricingCsvArray()
	{
		//Use utility csv writed, so here we are just responsible for assembling the array.
		$data = array();
		$headers = array('Id','Sku','Name','OEM','Distributor','Wholesale','Retail');
		foreach ($this->getProducts() as $product)
		{
			$data[] = array
			(
				$product->getProductId(),
				$product->getSku(),
				$product->getName(),
				$product->getOEMPrice(),
				$product->getDistributorPrice(),
				$product->getWholesalePrice(),
				$product->getRetailPrice()
			);
		}
		return array('data'=>$data,'headers'=>$headers);
	}
}