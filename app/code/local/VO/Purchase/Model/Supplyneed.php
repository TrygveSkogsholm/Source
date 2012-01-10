<?php

class VO_Purchase_Model_Supplyneed extends Mage_Core_Model_Abstract
{
	/*
	 *	This object represents a supply need identified by the system.
	 *
	 */
	public function _construct()
	{
		parent::_construct();
		$this->_init('purchase/supplyneed');
	}

	/**
	 * Calculate supply needs
	 *
	 * @param null|VO_Purchase_Model_Supplier|Mage_Catalog_Model_Product $product magento product-id or model instance
	 * @return array $supplyNeeds
	 * @author Trygve, Velo Orange
	 */
	public function calculateSupplyNeeds($object)
	{

	}

	public function calculateProductSupplyNeed($product)
	{

	}
	
	public function predictTrend($product_id,$target = null,$model='cyclicgrowth',$modifiers = null)
	{
		/*
		 * This function takes a particular product_id, gets sales data on it and creates a prediction class for it.
		 * 
		 * Method refers to the mathematic assumptions to be made (such as the nature of the function)
		 * 
		 * Modifiers refers to weighting certain regions of time for various reasons.
		 * 
		 * Returns a prediction class that can generate data points and other important conclusions.
		 */
		$prediction = Mage::getModel('purchase/supplyneed_prediction_'.$model,array('product_id'=>$product_id,'target'=>$target));
		$prediction->curveFit();
		return $prediction;
	}
}