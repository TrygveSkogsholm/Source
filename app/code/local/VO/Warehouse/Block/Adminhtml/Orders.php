<?php
class VO_Warehouse_Block_Adminhtml_Orders extends Mage_Adminhtml_Block_Widget_Form
{
	public function __construct()
	{
		$this->_controller = 'adminhtml_warehouse';
		$this->_blockGroup = 'warehouse';
		parent::__construct();

		//$this->_formInitScripts[] = "
		//startup();
		//";
	}
	
	public function getAndRenderRanges()
	{
		$loadedRange = Mage::registry('loaded_range');
		if (empty($loadedRange))
		{
			$currentRanges = Mage::getModel('warehouse/range')->getCurrentRanges();
			foreach ($currentRanges as $range)
			{
				$range->getOrders();
			}
			return $currentRanges;
		}
		else
		{
			$loadedRange->getOrders();
			return array($loadedRange);
		}
	}

}