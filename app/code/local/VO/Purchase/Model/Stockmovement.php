<?php

class VO_Purchase_Model_Stockmovement extends Mage_Core_Model_Abstract
{

	public function _construct()
	{
		parent::_construct();
		$this->_init('purchase/stockmovement');
	}

	/**
	 * Set data and save, yes it's a little odd to have this function save itself; to use you would
	 * create a stock movement object and call this function to save it.
	 *
	 * @param int $magnitude the size of the change i.e. mag 5 is add five, -5 is remove 5.
	 * @param int|Mage_Catalog_Model_Product $product the id or model of the item.
	 * @param string $type can be 'Ordered','Returned','Manual','Restocked'
	 * @param int $orderNum product first cost
	 * @return bool $succesReport
	 * @author Trygve, Velo Orange
	 */
	public function addStockMovement($original,$magnitude,$product,$type,$orderNum = NULL,$date = NULL)
	{
		if($date == NULL)
		{
			$date = now();
		}
		try {
			if ($magnitude < 0)
			{
				switch ($type)
				{
					case 'Returned':
						$succesReport = false;
						Mage::getSingleton('adminhtml/session')->addError(Mage::helper('purchase')->__('Returning items doesn\'t decrease the stock'));
						return $succesReport;
						break;

					case 'Restocked':
						$succesReport = false;
						Mage::getSingleton('adminhtml/session')->addError(Mage::helper('purchase')->__('Restocking an item does not decrease the stock'));
						return $succesReport;
						break;

					default:
						if ($product instanceof Mage_Catalog_Model_Product)
						{
							$this->setproduct_id($product->getId());
						}
						else
						{
							$this->setproduct_id($product);
							$product = Mage::getModel('catalog/product')->load($product);
						}

						$this->setdate($date);

						$this->settype($type);
						$this->setmagnitude($magnitude);

						break;
				}
			}
			elseif ($magnitude > 0)
			{
				switch ($type)
				{
					case 'Ordered':
						$succesReport = false;
						Mage::getSingleton('adminhtml/session')->addError(Mage::helper('purchase')->__('Ordering items doesn\'t increase the stock'));
						return $succesReport;
						break;

					default:
						if ($product instanceof Mage_Catalog_Model_Product)
						{
							$this->setproduct_id($product->getId());
						}
						else
						{
							$this->setproduct_id($product);
							$product = Mage::getModel('catalog/product')->load($product);
						}

						$this->setdate($date);

						$this->settype($type);
						$this->setmagnitude($magnitude);

						break;
				}
			}
			else
			{
				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('purchase')->__('A stock movement of zero or null is no movement at all!'));
			}

			if ($orderNum)
			{
				$this->setorder_num($orderNum);
			}
			
			try
			{
				$this->setstockafter($original+$magnitude);
			}
			catch (Exception $e) 
			{
				Mage::getSingleton('adminhtml/session')->addError('His royal majesties module is utterly failing to create a stock movement, but don\'t worry it will not effect your activities here');
			}
			$this->save();
			$succesReport = true;
		} catch (Exception $e) {
			Mage::getSingleton('adminhtml/session')->addError('Creating a stock movement failed');
			Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			$succesReport = false;
		}
		return $succesReport;
	}

	public function getSku()
	{
		return Mage::getModel('catalog/product')->load($this->getproduct_id())
		->getSku();
	}
}