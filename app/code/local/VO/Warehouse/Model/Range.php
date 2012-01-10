<?php

class VO_Warehouse_Model_Range extends Mage_Core_Model_Abstract
{
	/*
	 *	An order range is a collection of print records for a specific time period and a specific store view.
	 *
	 * 	An order contained within the range limits [start,end] is not neccesarily printed with the range.
	 * 	Such orders are refered to as exceptions.
	 *
	 *	All orders in the range must be accounted for either as exceptions or as printed orders.
	 *
	 *	Exceptions must provide an 'excuse', which takes the form of a note object. Each type of excuse has an ID
	 *	More about exceptions in the note object.
	 *
	 *	Some orders may be from out of the range because they were overdue for printing (called orphans), these should also be marked with a note.
	 *	You can see that the orders to be printed are a seperate function from the range limits, and is instead
	 *  based on whether it was printed or deleted already (and whether it can be shipped i.e. shipping address).
	 */

	public $orders;
	public $notes = array();

	public function _construct()
	{
		parent::_construct();
		$this->_init('warehouse/range');
	}

	public function delete()
	{
		//Deleting a range is a bit more complicated than just erasing it.
	}

	/**
	 * This function returns a collection of all current ranges.
	 */
	public function getCurrentRanges()
	{
		$stores = Mage::getModel('core/store')->getCollection();
		foreach ($stores as $store)
		{
			$range = $this->getLatest($store);
			$ranges[] = $range;
		}
		return $ranges;
	}

	/**
	 * This function will create a new range for a given store view (prefix set)
	 * it will not set the end limits but it will determine
	 * the start limits by searching for previous ranges of the same store, and if none can be found assumes zero.
	 */
	public function startNewRange($storeId)
	{
		$newRange = Mage::getModel('warehouse/range');
		$newRange->setstore_id($storeId);
		$newRange->setstart_date(now());
		$newRange->setlatest(true);
		$newRange->setrange_empty(true);

		/*
		 * It is possible that no orders fall into this range yet, in that case there is a flag 'empty' will default,
		 * If however we find orders that flag should be unset and the other two start limits ought to be set. This will
		 * be taken care of by the updateLimits function which is called now and whenever the range is accessed
		 * via get latest.
		 */
		$newRange->save();
		$newRange->updateLimits();
		return $newRange;
	}

	public function closeRange()
	{
		if ($this->isEmptyRange() == false)
		{
			$this->setData('latest',false);
			$this->save();
		}
		else
		{
			$this->delete();
		}
	}

	public function getLastRange()
	{
		//Search for previous range, this should pull up a collection with the latest first.
		$lastRangeCollection = $this->getCollection()
		->addFieldToFilter('latest',false)
		->addFieldToFilter('store_id',$this->getstore_id())
		->setOrder('end_date');

		if ($lastRangeCollection->count() != 0)
		{
			//There are previous ranges, pick the first item (highest/latest date)
			$lastRange = $lastRangeCollection->getFirstItem();
			return $lastRange;
		}
		else
		{
			return false;
		}
	}

	/**
	 * This function loads the latest range model for the given store, if it does not exist it will create it.
	 * It also updates the end limits.
	 * @param $store int | Mage_Core_Model_Store
	 */
	public function getLatest($store)
	{
		if ($store instanceof Mage_Core_Model_Store)
		{
			$id = $store->getId();
		}
		else
		{
			$id = $store;
		}

		$collection = $this->getCollection()
		->addFieldToFilter('latest',true)
		->addFieldToFilter('store_id',$id);

		if ($collection->count() == 1)
		{
			$range = $collection->getFirstItem();
		}
		else
		{
			$range = $this->startNewRange($id);
		}
		$range->updateLimits();
		return $range;
	}

	/**
	 * This crucial function sets the start increment and id, it does not do the start date however.
	 * It checks to see if any orders exist that are applicably part of this range by linear id.
	 * It is still possible for an order to exist within the range if it is an orphan, but this is not considered.
	 * Start id and start increment is not to be required to print orders, however a range should not be closed just
	 * for an orphan.
	 *
	 * It also updates the end limits as well.
	 */
	public function updateLimits()
	{
		//Don't bother wasting resources if this thing is locked down
		if ($this->isLatest() == true)
		{
			$last = $this->getLastRange();
			if ($last != false)
			{
				$minStartId = ($last->getEndId() + 1);
			}
			else
			{
				$minStartId = 0;
			}

			//Find all orders above min start ID and with the proper store id, order by created_at and set start and end, start is first item.
			$orders = Mage::getResourceModel('sales/order_grid_collection')
			->addFieldToFilter('entity_id',array('gteq'=>$minStartId))
			->addFieldToFilter('status',array('neq'=>'canceled'))
			->addFieldToFilter('shipping_name',array('notnull'=>1))
			->addFieldToFilter('store_id',$this->getStoreId())
			->setOrder('increment_id');

			if ($orders->getSize() >= 1)
			{
				//Update start
				if ($this->isEmptyRange() == true)
				{
					$this->setStart($orders->getLastItem());
					$this->setrange_empty(false);
				}

				//Update end
				$this->setEnd($orders->getFirstItem());
			}
			$this->save();
		}
	}


	/**
	 * This is one of the most important functions, it finds all the orders that need to be printed in this range.
	 * The most important thing about this function is the format which it returns:
	 * An array of grouped orders, each group cooresponds to a single shipping address and is itself an array of
	 * normal magento orders but with the keys being the id of the order value.
	 * Most groups will only contain one order but some will contain more, this allows automatic
	 * identification and combination of like orders in a range.
	 * @param bool $model, if set to true this will return a sales/order object instead of data array.
	 */
	public function getOrders($model = false)
	{
		if (isset($this->orders))
		{
			return $this->orders;
		}
		else
		{
			if ($this->isLatest())
			{
				//Make sure everything is as we expect
				Mage::getModel('warehouse/print')->populatePrintRecords();
				$limits = $this->getLimits();
				$orders = array();
				$accounedFor = array();

				//Config variables
				$tooLarge = Mage::getStoreConfig('warehouse_orders/notes/large_definition');
				$companyCountry = Mage::getStoreConfig('shipping/origin/country_id');

				//Order query
				$read = Mage::getSingleton('core/resource')->getConnection('core_read');
				$query = 'SELECT id, address_string, sales_flat_order.`status`, subtotal, customer_email,
					      increment_id, customer_group_id
						  FROM warehouse_print
						  LEFT OUTER JOIN  sales_flat_order ON id = entity_id
						  WHERE sales_flat_order.store_id = '.$this->getStoreId().' AND is_printed = 0;';
				$ordersMarkedAsUnprinted = $read->fetchAll($query);

				//Generate already printed notes:
				$alreadyPrintedInRange = Mage::getModel('warehouse/print')->getCollection()
				->addFieldToFilter('id',array('gteq'=>$limits['start']['id']))
				->addFieldToFilter('id',array('lteq'=>$limits['end']['id']))
				->addFieldToFilter('is_printed',true);
				foreach ($alreadyPrintedInRange as $alreadyPrinted)
				{
					$this->generateNote($alreadyPrinted->getId(),2,$alreadyPrinted->getRangeId());
				}

				foreach ($ordersMarkedAsUnprinted as $order)
				{
					if ($order['status'] != 'canceled' && $order['status'] != 'closed' && $order['status'] != 'complete')
					{
						//Not canceled
						if ($order['address_string'] != '' && strpos($order['address_string'], '21401') == false)
						{
							//Shipable
							if ($order['id'] < $limits['start']['id'])
							{
								//It's an orphan from a previous range.
								$this->generateNote($order['id'],7,null,$order['increment_id']);
							}
							if ($order['status'] == 'holded')
							{
								//It's being held
								$this->generateNote($order['id'],5,null,$order['increment_id']);
							}

							$country = $order['address_string'];
							$country = explode(";",$country);
							$country = end($country);
							if ($country != $companyCountry)
							{
								//International
								$this->generateNote($order['id'],8,null,$order['increment_id']);
							}
							if ($order['subtotal'] >= $tooLarge)
							{
								//Large
								$this->generateNote($order['id'],9,null,$order['increment_id']);
							}
							if ($model == false)
							{
								$orders[$order['address_string']][$order['id']] = array('increment'=>$order['increment_id'],'subtotal'=>$order['subtotal'],'email'=>$order['customer_email'],'group'=>$order['customer_group_id']);
							}
							else
							{
								$orders[$order['address_string']][$order['id']] = Mage::getModel('sales/order')->load($order['id']);
							}
						}
						else
						{
							//Not shipable
							$this->generateNote($order['id'],3,null,$order['increment_id']);
							$print = Mage::getModel('warehouse/print')->load($order['id']);
							$print->markAsPrinted();
							$print->save();
						}
					}
					else
					{
						//canceled closed or complete
						$this->generateNote($order['id'],($order['status']=='complete')?13:1,null,$order['increment_id']);
						$print = Mage::getModel('warehouse/print')->load($order['id']);
						$print->markAsPrinted();
						$print->save();
					}
					$accounedFor[] = $order['id'];
				}
				//Generate combined notes:
				foreach ($orders as $orderGroup)
				{
					if (count($orderGroup) > 1)
					{
						$combinedString = array();
						foreach ($orderGroup as $id=>$combinedOrder)
						{
							$combinedString[] = $id;
						}
						$this->generateNote(min($combinedString),6,implode(",", $combinedString));
					}
				}
			}
			else
			{
				//just load print records
				$orders = array();
				foreach ($this->getPrintRecords() as $printRecord)
				{
					$order = $printRecord->getOrder();
					if ($model == false)
					{
						$orders[$printRecord->getAddressString()][$printRecord->getId()] = array
						(
						'increment'=>$order->getRealOrderId(),
						'subtotal'=>$order->getSubtotal(),
						'email'=>$order->getCustomerEmail(),
						'group'=>$order->getCustomer()->getGroupId()
						);
					}
					else
					{
						$orders[$printRecord->getAddressString()][$printRecord->getId()] = $order;
					}
				}
				$this->getNotes();
			}
			$this->orders = $orders;
			return $this->orders;
		}
	}

	public function generateNote($id,$type,$data=null,$increment=null,$comment=null)
	{
		try {
			$note = Mage::getModel('warehouse/note');
			$note->setPrint($id);
			$note->setRange($this->getId());
			$note->setType($type);
			$note->setExtraData($data);
			$note->setComment($comment);
			if ($increment == null)
			{
				$note->getIncrementId();
			}
			else
			{
				$note->incrementId = $increment;
			}
			$this->notes[] = $note;
			return $note;
		} catch (Exception $e) {
			Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
		}
	}

	/**
	 * get all notes pertaining to this range.
	 */
	public function getNotes()
	{
		if (empty($this->notes))
		{
			$this->notes = Mage::getModel('warehouse/note')->getCollection()->addFieldToFilter('range_id',$this->getId());
		}
		return $this->notes;
	}

	public function getPrintRecords()
	{
		$records = Mage::getModel('warehouse/print')->getCollection()
		->addFieldToFilter('range_id',$this->getId());
		return $records;
	}

	/**
	 *
	 * Sets the two start parameters of the range which define it's lower limits.
	 * The first order in the range should be given as argument.
	 * @param $order int|Mage_Sales_Model_Order
	 * @param $increment string
	 * @param $date datetime
	 */
	public function setStart($order,$increment=null)
	{
		if ($order instanceof Mage_Sales_Model_Order)
		{
			$this->setData('start_id',$order->getId());
			if ($increment == null)
			{
				$this->setData('start_increment',$order->getRealOrderId());
			}
			else
			{
				$this->setData('start_increment',$increment);
			}
		}
		else
		{
			$this->setData('start_id',$order);
			if ($increment == null)
			{
				$order = Mage::getModel('sales/order')->load($order);
				$this->setData('start_increment',$order->getRealOrderId());
			}
			else
			{
				$this->setData('start_increment',$increment);
			}
		}
	}

	/**
	 *
	 * Sets the three end parameters of the range which define it's upper limits.
	 * The last order in the range should be given as argument.
	 * @param $order int|Mage_Sales_Model_Order
	 * @param $increment string
	 * @param $date datetime
	 */
	public function setEnd($order,$increment=null,$date=null)
	{
		if ($order instanceof Mage_Sales_Model_Order)
		{
			$this->setData('end_id',$order->getId());
			if ($increment == null)
			{
				$this->setData('end_increment',$order->getRealOrderId());
			}
			else
			{
				$this->setData('end_increment',$increment);
			}
			if ($date == null)
			{
				$this->setData('end_date',now());
			}
			else
			{
				$this->setData('end_date',$date);
			}
		}
		else
		{
			$this->setData('end_id',$order);
			if ($increment == null)
			{
				$order = Mage::getModel('sales/order')->load($order);
				$this->setData('end_increment',$order->getRealOrderId());
			}
			else
			{
				$this->setData('end_increment',$increment);
			}
			if ($date == null)
			{
				$this->setData('end_date',now());
			}
			else
			{
				$this->setData('end_date',$date);
			}
		}
	}

	public function getLimits()
	{
		return array
		(
			'start'=>array('id'=>$this->getStartId(),'increment'=>$this->getStartIncrement(),'date'=>$this->getStartDate()),
			'end'=>array('id'=>$this->getEndId(),'increment'=>$this->getEndIncrement(),'date'=>$this->getEndDate())
		);
	}

	public function isLatest()
	{
		return $this->getData('latest');
	}

	public function isEmptyRange()
	{
		return $this->getData('range_empty');
	}

	public function getStoreId()
	{
		return $this->getstore_id();
	}

	public function getStore()
	{
		return Mage::getModel('core/store')->load($this->getStoreId());
	}

	public function getStartIncrement()
	{
		return $this->getstart_increment();
	}
	public function getStartId()
	{
		return $this->getstart_id();
	}
	public function getStartDate()
	{
		return $this->getstart_date();
	}
	public function getEndIncrement()
	{
		return $this->getend_increment();
	}
	public function getEndId()
	{
		return $this->getend_id();
	}
	public function getEndDate()
	{
		return $this->getend_date();
	}
}