<?php
class VO_Purchase_Block_Supplyneeds_Graph extends Mage_Adminhtml_Block_Template
{
	public function __construct()
	{
		parent::__construct();
	}
	
	public function getSkuOptions()
	{
		$options = array();
		$collection = Mage::getModel('catalog/product')->getCollection()
		->addAttributeToSelect('name')
		->addAttributeToSelect('sku');
		foreach ($collection as $product)
		{
			$options[] = array('label'=>$product->getSku(),'desc'=>$product->getName(),'value'=>$product->getId());
			$options[] = array('label'=>$product->getName(),'desc'=>$product->getSku(),'value'=>$product->getId());
		}
		return  Zend_Json::encode($options);
	}
}