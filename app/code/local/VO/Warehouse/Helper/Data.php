<?php

class VO_Warehouse_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function getNoteTypes()
	{
		return array
		(
			1=>'Canceled',
			2=>'Already printed',
			3=>'Not shippable',
			4=>'Manualy excluded',
			5=>'Held',
			6=>'Combined',
			7=>'an Orphan',
			8=>'International',
			9=>'Large',
			10=>'Expedited',
			11=>'Custom',
			12=>'Already Completed'
		);
	}
	
	public function getStoreOptions()
	{
		$stores = Mage::getModel('core/store')->getCollection();
		foreach ($stores as $store)
		{
			
		}
	}
}