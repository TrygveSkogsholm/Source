<?php
class VO_Purchase_Block_Supplyneeds_Analysis extends Mage_Adminhtml_Block_Template
{
	public function __construct()
	{
		//$this->_headerText = Mage::helper('purchase')->__('Supply Needs');
		$this->setTemplate('purchase/supplyneeds/analysis.phtml');
		//$this->_jsUrl = $this->getJsUrl('vo/purchase/supplyneeds/analysis.js');
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