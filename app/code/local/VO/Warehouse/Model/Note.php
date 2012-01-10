<?php

class VO_Warehouse_Model_Note extends Mage_Core_Model_Abstract
{
	public function _construct()
	{
		parent::_construct();
		$this->_init('warehouse/note');
	}

	/**
	 *
	 * Sets the type of the note
	 * @param int|string $type
	 */
	public function setType($type)
	{
		if (is_int($type))
		{
			$this->setData('type',$type);
			return;
		}
		elseif (is_string($type))
		{
			$types = Mage::helper('warehouse')->getNoteTypes();
			foreach ($types as $id => $string)
			{
				if ($type == $string)
				{
					$this->setData('type',$id);
					return;
				}
			}
		}
	}

	public function setRange($range)
	{
		if ($range instanceof VO_Warehouse_Model_Range)
		{
			$this->setData('range_id',$range->getId());
		}
		else
		{
			$this->setData('range_id',$range);
		}
	}

	public function setPrint($print)
	{
		if ($print instanceof VO_Warehouse_Model_Print)
		{
			$this->setData('print_id',$print->getId());
		}
		else
		{
			$this->setData('print_id',$print);
		}
	}

	public function setComment($comment)
	{
		$this->setData('comment',$comment);
	}

	public function setExtraData($data)
	{
		$this->setData('data',$data);
	}

	public function getType($format = 'int')
	{
		if ($format == 'int')
		{
			return $this->getData('type');
		}
		else
		{
			$index = Mage::helper('warehouse')->getNoteTypes();
			return $index[$this->getData('type')];
		}
	}

	public function getNoteHtml()
	{
		$dataHtml = '';
		if ($this->getType() == 6)
		{
			$dataHtml = " with ".implode(',', $this->getCombinedIncrements());
		}
		if ($this->getType() == 7)
		{
			$dataHtml = " from range #".$this->getExtraData();
		}
		$html = "<span>Order #".$this->getIncrementId()." is ".$this->getType('text').$dataHtml."</span>";
		return $html;
	}

	public function getCombinedOrders()
	{
		if ($this->getType() == 6)
		{
			$orders = array();
			foreach (explode(',', $this->getExtraData()) as $orderId)
			{
				$orders[] = Mage::getModel('sales/order')->load($orderId);
			}
			return $orders;
		}
		return false;
	}

	public function getCombinedIncrements()
	{
		if ($this->getType() == 6)
		{
			$increments = array();
			foreach ($this->getCombinedOrders() as $order)
			{
				$increments[] = $order->getRealOrderId();
			}
			return $increments;
		}
		return false;
	}

	public function isMissing()
	{
		if (in_array($this->getType(), array(1,2,3,4)))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	public $incrementId;
	public function getIncrementId()
	{
		if (!isset($this->incrementId))
		{
			$order = Mage::getModel('sales/order')->load($this->getPrintId());
			$this->incrementId = $order->getRealOrderId();
		}
		return $this->incrementId;
	}

	public function getRangeId()
	{
		return $this->getData('range_id');
	}

	public function getPrintId()
	{
		return $this->getData('print_id');
	}

	public function getComment()
	{
		return $this->getData('comment');
	}

	public function getExtraData()
	{
		return $this->getData('data');
	}
}