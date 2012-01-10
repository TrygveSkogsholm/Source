<?php

class VO_Warehouse_Model_Print extends Mage_Core_Model_Abstract
{
	public $order;
	public $notes;
	private $read;

	/*
	 * This model represents a printed order, it is possible that the instance exists without it being printed
	 * even neccesary (i.e. the print list should be populated with every order when called upon).
	 * The ID is always equal to an order id.
	 */

	public function _construct()
	{
		parent::_construct();
		$this->_init('warehouse/print');
	}

	/**
	 * Sets the status as printed
	 */
	public function markAsPrinted()
	{
		$this->setis_printed(true);
		$this->setData('date',now());
	}

	/**
	 * Sets the status as not printed
	 */
	public function unmarkAsPrinted()
	{
		$this->setis_printed(false);
	}


	/**
	 * 
	 * This function populates the warehouse_print table
	 */
	public function populatePrintRecords()
	{
		/*$alreadyRecords = $this->getCollection()->getAllIds();
		$ordersWithNoRecords = Mage::getModel('sales/order')->getCollection()
		->addFieldToFilter('entity_id',array('nin'=>$alreadyRecords))
		->getAllIds();*/
		try {
			//Populate print records
			$read = $this->getDatabaseRead();
			$write = Mage::getSingleton('core/resource')->getConnection('core_write');
			$query = 'SELECT sales_flat_order.entity_id, CONCAT_WS(\';\',CAST(region_id AS CHAR),CAST(postcode AS CHAR),street,city,CAST(country_id AS CHAR)) 
					  AS address_string FROM sales_flat_order
					  LEFT OUTER JOIN warehouse_print ON sales_flat_order.entity_id = id
					  LEFT OUTER JOIN sales_flat_order_address ON shipping_address_id = sales_flat_order_address.entity_id
					  WHERE warehouse_print.id IS NULL;';
			$ordersWithNoRecords = $read->fetchAll($query);
				
			$insertRowCount = 0;
			$saveQuery = '';
			foreach ($ordersWithNoRecords as $orderWithNoRecord)
			{
				//Send queries in chunks of 200
				$saveQuery .= 'INSERT INTO warehouse_print (id, address_string) VALUES ('.$orderWithNoRecord['entity_id'].', \''.str_replace("'", "", $orderWithNoRecord['address_string']).'\');';
				$insertRowCount ++;
				if ($insertRowCount == 200)
				{
					$write->query($saveQuery);
					$insertRowCount = 0;
					$saveQuery = '';
				}
			}
			if ($insertRowCount != 0)
			{
			$write->query($saveQuery);
			}
		} catch (Exception $e) {
			Mage::getSingleton('adminhtml/session')->addError($e->getMessage().' ID: '.$orderWithNoRecord['entity_id']);
		}
	}

	public function getDatabaseRead()
	{
		if (isset($this->read) == TRUE )
		return $this->read;
		else
		{
			$this->read = Mage::getSingleton('core/resource')->getConnection('core_read');
			return $this->read;
		}
	}

	/**
	 * Assign record to range
	 * @param int | VO_Warehouse_Model_Range range to assign to
	 */
	public function assignToRange($range)
	{
		if ($range instanceof VO_Warehouse_Model_Range)
		{
			$this->setrange_id($range->getId());
		}
		else
		{
			$this->setrange_id($range);
		}
	}

	/**
	 * Returns an array of all records
	 * @return array records int
	 */
	public function getIdArray()
	{
		return $this->getCollection()->getAllIds();
	}

	/**
	 * Get order which is being reported as printed
	 * @return Mage_Sales_Model_Order
	 */
	public function getOrder()
	{
		return Mage::getModel('sales/order')->load($this->getId());
	}
	
	public function setAddressString($string)
	{
		$this->setData('address_string',$string);
	}

	/**
	 * Get address string
	 * @return string
	 */
	public function getAddressString()
	{
		if ($this->getaddress_string() == NULL)
		{
			$address = $this->getOrder()->getShippingAddress();
			$this->setaddress_string($address->getStreetFull().$address->getRegion().$address->getCountry());
		}
		return $this->getaddress_string();
	}

	/**
	 * Returns range identifier
	 * @return int range_id
	 */
	public function getRangeId()
	{
		return $this->getrange_id();
	}

	/**
	 * Checks to see if model has been printed
	 * @return bool is_printed
	 */
	public function getStatus()
	{
		return $this->getis_printed();
	}
}