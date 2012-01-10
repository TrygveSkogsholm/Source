<?php

class VO_Purchase_Block_Prices_Load extends Mage_Adminhtml_Block_Template
{
	public function __construct()
	{
		parent::__construct();	
	}
	
	public function getCategoryOptions()
	{
		$collection = Mage::getModel('catalog/category')->getCollection()
		->addAttributeToSelect('name');
		$options = '';
		foreach ($collection as $category)
		{
			$options .= '<option value="'.$category->getId().'">'.$category->getName().'</option>';
		}
		return $options;
	}
	
	public function getAutocompleteOptions()
	{
		$options = array();
		$collection = Mage::getModel('catalog/product')->getCollection()
		->addAttributeToSelect('name')
		->addAttributeToSelect('sku')
		->addFieldToFilter('type_id','simple');
		foreach ($collection as $product)
		{
			$options[] = array('label'=>$product->getSku(),'value'=>$product->getId());
			$options[] = array('label'=>$product->getName(),'value'=>$product->getId());
		}
		return  Zend_Json::encode($options);
	}
}