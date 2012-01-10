<?php
/**
 * 
 * This class represents collections of price changes being planned. PO triggered changes will be reflected in this
 * if they are saved.
 * 
 * @author Trygve
 *
 */
class VO_Purchase_Model_Price_Plan extends Mage_Core_Model_Abstract
{
	public function _construct()
	{
		parent::_construct();
		$this->_init('purchase/price_plan');
	}
	
	/**
	 * @return VO_Purchase_Model_Price collection
	 * This function gathers all the individual price changes that belong to this plan.
	 */
	public function getPriceChanges()
	{
		$collection = Mage::getModel('purchase/price')->getCollection()
		->addFieldToFilter('plan_id',$this->getId());
		return $collection;
	}
	
	public function getExplanation()
	{
		return $this->getData('explanation');
	}
	
	public function isActive()
	{
		$activated = true;
		foreach ($this->getPriceChanges() as $change)
		{
			if ($change->getActive() == false)
			{
				$activated = false;
				break;
			}
		}
		return $activated;
	}
}