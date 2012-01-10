<?php

class VO_Purchase_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function getStatusOptions()
	{
		$options = array
		(
		'1' => 'Planned',
		'2' => 'Sent',
		'3' => 'Partially Shipped',
		'4' => 'Shipped',
		'5' => 'Partially Received',
		'6' => 'Received',
		'7' => 'Completed'
		);

		return $options;
	}

	public function getShipmentStatusOptions()
	{
		$options = array
		(
		'1' => 'Planned',
		'2' => 'Shipped',
		'3' => 'Received'
		);

		return $options;
	}

	public function getShippingMethodOptions($pair = false)
	{
		$options = array('air'=>'Air','land'=>'Land','sea'=>'Sea');
		if ($pair == false)
		{
			return $options;
		}
		else
		{
			$pairedList[] = array('value'=>'','label'=>'please enter');
			foreach ($options as $option)
			{
				$pairedList[] = array('value'=>$option,'label'=>$option);
			}
			return $pairedList;
		}
	}

	public function getSupplierListOptions($pair = false)
	{
		$options = Mage::getModel('purchase/supplier')->getCollection()
		->getColumnValues('company_name');
		if ($pair == false)
		{
			return $options;
		}
		else
		{
			foreach ($options as $option)
			{
				$pairedList[$option] = $option;
			}
			return $pairedList;
		}
	}

	public function getCarrierOptions($pair = false)
	{
		$options = explode(',',Mage::getStoreConfig('orders/shipping/order_carrier'));
		if ($pair == false)
		{
			return $options;
		}
		else
		{
			$pairedList[] = array('value'=>'','label'=>'please enter');
			foreach ($options as $option)
			{
				$pairedList[] = array('value'=>$option,'label'=>$option);
			}
			return $pairedList;
		}
	}

	public function term($term,$plural = false,$lowercase = false)
	{
		return $this->__($term);
	}


	/**
	 * Decode the POST data from the add product to supplier grid and parse it to nice multi-dimensional
	 * array with product_id, model, first cost are the most basic element.
	 *
	 * @param array $data POST data from VO_Purchase_Block_Suppliers_Edit_Tab_AddProducts
	 * @return array $productArray i.e. $productArray[1]['firstcost'] or $productArray[1]['product_id']
	 * @author Trygve, Velo Orange
	 */
	public function decodeNewSupplierProducts($data)
	{
		$productArray = array();

		try {
			$ids = explode(",", rtrim($data['dataString'],","));
			foreach ($ids as $id)
			{
				$productArray[] = array('product_id'=>$id,'model'=>$data[$id.'_model'],'first_cost'=>$data[$id.'_firstcost']);
			}

		} catch (Exception $e) {
			Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
		}
		return $productArray;
	}

	public function formatPercentage($float)
	{
		return (($float*100)-100).'%';
	}

	public function calculateTimeDifference($a,$b,$units)
	{
		$A = strtotime($a);
		$B = strtotime($b);
		$Difference = abs($A - $B);

		switch ($units)
		{
			case 'seconds':
				$Denominator = 1;
				break;

			case 'hours':
				$Denominator = 3600;
				break;

			case 'days':
				$Denominator = 86400;
				break;

			default:
				$Denominator = 86400;
				break;
		}
		return $Difference/$Denominator;

	}

	public function calculateNewTime($time,$units,$difference)
	{
		if (!is_int($time))
		{
			$time = strtotime($time);
		}
		switch ($units)
		{
			case 'seconds':
				$Multiplicand = 1;
				break;

			case 'hours':
				$Multiplicand = 3600;
				break;

			case 'days':
				$Multiplicand = 86400;
				break;

			default:
				$Multiplicand = 86400;
				break;
		}
		$newTime = $time + ($difference * $Multiplicand);
		return $newTime;
	}
}