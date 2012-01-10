<?php

class VO_Purchase_Model_Productadditional extends Mage_Core_Model_Abstract
{
	public $magentoProduct = NULL;
	public $read;

	public function _construct()
	{
		parent::_construct();
		$this->_init('purchase/productadditional');
	}

	/**
	 * This function calculates the average landed cost. This variable corresponds to a magento product
	 * in general not a specific PO product or shipment product. The term average gives the impression of
	 * imprecise but this should be perfectly accurate at the time of calculation (that is provided it is
	 * called every time a new landed cost data point crops up).
	 *
	 * It is used primarily for pricing and analysis. The main problem being that a temporary or large shift
	 * in the landed cost of one PO does not reflect the general value of all the stock of one type.
	 *
	 * It is used in two distinct ways. First to predict what the ALC would be if as of yet unreceived but planned
	 * shipments were received but it does not save them. Second to actually do the final calculation upon receival and save the AVC into
	 * the magento product 'cost' field. To that end it has an argument which is an array of 'false' shipments
	 * that will be taken into account to predict a not yet applicable AVC. If no argument is passed it will
	 * only consider received shipments. It is called in this context by shipment.php -> receive().
	 * The other context it is called in is in price.php -> predictLandedCost() which passes generated array
	 * describing the shipments that are likely to occur.
	 *
	 * The array takes this form:
	 * shipments:
	 *  	- shipment
	 *  	- shipment
	 *  	- shipment
	 *  		landedcost => value
	 *  		quantity => value
	 * @param array $pseudoShipments
	 */
	public function calculateAverageLandedCost($pseudoShipments = NULL)
	{
		if ($pseudoShipments == NULL)
		{
			$predictive = FALSE;
		}
		else
		{
			$predictive = TRUE;
		}

		//The two crucial values are the total value (SUM of landed cost) and total qty, initiate them.
		$totalValue = 0;
		$totalQty = 0;
		$averageLandedCost = 0;

		/*
		 * This function works by 'counting back' across the shipments that have existed until all current
		 * stock is accounted for. In this way we know how to limit the landed cost data points to real
		 * world stock.
		 */

		//Get current stock qty
		$inventory = $this->getStockValue();

		//Retrieve shipment/product.php models that are that are the same product id (same item).
		$shipmentItems = Mage::getModel('purchase/shipment_product')->getCollection()
		->addFieldToFilter('product_id',$this->getproduct_id())
		->setOrder('id','DESC');

		//If we are predicting, we consider the pseudoshipments first
		if ($predictive == TRUE)
		{
			foreach ($pseudoShipments as $pseudoShipment)
			{
				//Note we don't have to count because $inventory doesn't include these false shipments.
				$totalValue += ($pseudoShipment['landedcost']*$pseudoShipment['qty']);
				$totalQty += $pseudoShipment['qty'];
			}
		}

		//Start counting back over real shipments(the setOrder above accomplished it that the latest PO's are started with)
		foreach ($shipmentItems as $shipmentItem)
		{
			$shipment = $shipmentItem->getShipment();

			if ($shipment->IsReceived() == true && ($inventory - $shipmentItem->getItemQty()) >= 0)
			{
				//Keep going, subtract the accounted for inventory from the still to be accounted for inventory
				$inventory -= $shipmentItem->getItemQty();
			}
			else
			{
				//Done counting, we have accounted for all stock. Don't forget the last datapoint!
				$totalValue += $inventory * $shipmentItem->getLandedCost();
				$totalQty += $inventory;
				break;
			}
			//Add the datapoint to the total.
			$totalValue += $shipmentItem->getTotal();
			$totalQty += $shipmentItem->getItemQty();
		}

		//Calculate the average :)
		$averageLandedCost = $totalValue/$totalQty;

		//The following involves actually saving the update places
		if ($predictive == FALSE)
		{
			$this->setaverage_landed_cost($averageLandedCost);
			$this->save();

			$costField = $this->getMagentoProduct();
			$costField->setData('cost',$averageLandedCost);
			$costField->save();
		}
		return $averageLandedCost;
	}

	/*
	 * Attention, I am trying to switch away from fetching the magento product and saying get->X it takes
	 * too long (I know frustrating)
	 *
	 * Direct queries are how it's going to go for this and the PO module if possible.
	 *
	 * You should be aware then of some things I found out about the database.
	 * Attribute IDs:
	 * 64 = price
	 * 68 = cost
	 * 543 = hts (only for this install)
	 */
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

	public function getMagentoProduct()
	{
		return $this->magentoProduct = Mage::getModel('catalog/product')->load($this->getproduct_id());
		if ($this->magentoProduct != NULL)
		{
			return $this->magentoProduct;
		}
		else
		{
			$this->magentoProduct = Mage::getModel('catalog/product')->load($this->getproduct_id());
			return $this->magentoProduct;
		}
	}

	public function getStockValue()
	{
		return $this->getMagentoProduct()->getStockItem()->getQty();
	}

	public function loadByProductId($id)
	{
		$additional =  $this->getCollection()
		->addFieldToFilter('product_id',$id)
		->getFirstItem();
		if ($additional->getproduct_id() != NULL)
		{
			return $additional;
		}
		else
		{
			if ($id != '')
			{
				$this->setproduct_id($id);
				$this->save();
				return $this;
			}
		}

	}

	public function getTierPriceObject()
	{
		$object = Mage::getModel('catalog/resource_eav_mysql4_product_attribute_backend_tierprice');
		return $object;
	}

	public function getTierPriceArray()
	{
		$array = $this->getTierPriceObject()->loadPriceData($this->getproduct_id());
		return $array;
	}

	public function getDistributorCost($full = FALSE)
	{
		foreach ($this->getTierPriceArray() as $tierPrice)
		{
			if ($tierPrice['cust_group'] == 4 && $tierPrice['price_qty'] = 1)
			{
				if ($full == FALSE)
				{
					return $tierPrice['price'];
				}
				else
				{
					return $tierPrice;
				}
			}
		}
	}

	public function setDistributorCost($value)
	{
		$object = $this->getTierPriceObject();
		$data = $this->getDistributorCost(TRUE);


		if (isset($data['price_id']))
		{
			//There is a tier price to speak of
			if (!empty($value))
			{
				//There really is a value, let's update the old
				$price = new Varien_Object(array(
          		'value_id'  => $data['price_id'],
          		'value'     => $value
				));
				$object->savePriceData($price);
			}
			else
			{
				//No value, means price should be deleted.
				$object->deletePriceData($this->getproduct_id(), null, $data['price_id']);
			}
		}
		else
		{
			//There is no tier price, yet; if value exist create new tier price
			if (!empty($value))
			{
				$product = $this->getMagentoProduct();
				$existingTierPrice = $product->tier_price;
				$newTierPrices[] = array(
	             'website_id'  => 0,
				 'all_groups'  => false,
	             'cust_group'  => 4,
	             'price_qty'   => 1.0000,
	             'price'       => $value,
				 'website_price'=>$value
	           );
	 
				// Merge existing and new tier prices to update
				$tierPrices=array_merge($existingTierPrice,$newTierPrices);
				$product->tier_price = $tierPrices;
				$product->save();
			}
		}
	}

	public function getWholesaleCost($full = FALSE)
	{
		foreach ($this->getTierPriceArray() as $tierPrice)
		{
			if ($tierPrice['cust_group'] == 2 && $tierPrice['price_qty'] = 1)
			{
				if ($full == FALSE)
				{
					return $tierPrice['price'];
				}
				else
				{
					return $tierPrice;
				}
			}
		}
	}

	public function setWholesaleCost($value)
	{
		$object = $this->getTierPriceObject();
		$data = $this->getWholesaleCost(TRUE);


		if (isset($data['price_id']))
		{
			//There is a tier price to speak of
			if (!empty($value))
			{
				//There really is a value, let's update the old
				$price = new Varien_Object(array(
          		'value_id'  => $data['price_id'],
          		'value'     => $value
				));
				$object->savePriceData($price);
			}
			else
			{
				//No value, means price should be deleted.
				$object->deletePriceData($this->getproduct_id(), null, $data['price_id']);
			}
		}
		else
		{
			//There is no tier price, yet; if value exist create new tier price
			if (!empty($value))
			{
				$product = $this->getMagentoProduct();
				$existingTierPrice = $product->tier_price;
				$newTierPrices[] = array(
	             'website_id'  => 0,
				 'all_groups'  => false,
	             'cust_group'  => 2,
	             'price_qty'   => 1.0000,
	             'price'       => $value,
				 'website_price'=>$value
	           );
	 
				// Merge existing and new tier prices to update
				$tierPrices=array_merge($existingTierPrice,$newTierPrices);
				$product->tier_price = $tierPrices;
				$product->save();
			}
		}
	}

	public function getOEMCost($full = FALSE)
	{
		foreach ($this->getTierPriceArray() as $tierPrice)
		{
			if ($tierPrice['cust_group'] == 6 && $tierPrice['price_qty'] = 1)
			{
				if ($full == FALSE)
				{
					return $tierPrice['price'];
				}
				else
				{
					return $tierPrice;
				}
			}
		}
	}

	public function setOEMCost($value)
	{
		$object = $this->getTierPriceObject();
		$data = $this->getOEMCost(TRUE);


		if (isset($data['price_id']))
		{
			//There is a tier price to speak of
			if (!empty($value))
			{
				//There really is a value, let's update the old
				$price = new Varien_Object(array(
          		'value_id'  => $data['price_id'],
          		'value'     => $value
				));
				$object->savePriceData($price);
			}
			else
			{
				//No value, means price should be deleted.
				$object->deletePriceData($this->getproduct_id(), null, $data['price_id']);
			}
		}
		else
		{
			//There is no tier price, yet; if value exist create new tier price
			if (!empty($value))
			{
				$product = $this->getMagentoProduct();
				$existingTierPrice = $product->tier_price;
				$newTierPrices[] = array(
	             'website_id'  => 0,
				 'all_groups'  => false,
	             'cust_group'  => 6,
	             'price_qty'   => 1.0000,
	             'price'       => $value,
				 'website_price'=>$value
	           );
	 
				// Merge existing and new tier prices to update
				$tierPrices=array_merge($existingTierPrice,$newTierPrices);
				$product->tier_price = $tierPrices;
				$product->save();
			}
		}
	}

	public function getRetailCost()
	{
		try {
			$read = $this->getDatabaseRead();
			$query = 'SELECT value FROM catalog_product_entity_decimal WHERE attribute_id = 64 AND entity_id = '.$this->getproduct_id();
			$result = $read->fetchAll($query);
			if (isset($result[0]))
			{return $result[0]['value'];}
			//return $this->getMagentoProduct()->getPrice();
		} catch (Exception $e) {
		}
	}


	public function getLandedCost()
	{
		$cost = $this->getaverage_landed_cost();
		if (empty($cost) == FALSE)
		{
			return $cost;
		}
		else
		{
			try {
				$read = $this->getDatabaseRead();
				$query = 'SELECT value FROM catalog_product_entity_decimal WHERE attribute_id = 68 AND entity_id = '.$this->getproduct_id();
				$result = $read->fetchAll($query);
				if (isset($result[0]))
				{return $result[0]['value'];}
			} catch (Exception $e) {
			}

		}
	}

	//There is no setLandedCost on purpose, it is considered a reliable value for other functions and must be set only
	//through calc average.


	public function getType()
	{
		$read = $this->getDatabaseRead();
		$query = 'SELECT value FROM catalog_product_entity_varchar WHERE attribute_id = 4 AND entity_id = '.$this->getproduct_id();
		$result = $read->fetchAll($query);
		$code = $result[0]['type_id'];
		if ($code != '')
		{return $code;}
		else
		{//Mage::getSingleton('adminhtml/session')->addError('No HTS '.$code.' code for  '.$this->getSku());
		}
	}
	
	public function getHtsCode()
	{
		$read = $this->getDatabaseRead();
		$query = 'SELECT value FROM catalog_product_entity_varchar WHERE attribute_id = 543 AND entity_id = '.$this->getproduct_id();
		$result = $read->fetchAll($query);
		$code = $result[0]['value'];
		if ($code != '')
		{return $code;}
		else
		{//Mage::getSingleton('adminhtml/session')->addError('No HTS '.$code.' code for  '.$this->getSku());
		}
	}

	public function getSku()
	{
		try {
			$read = $this->getDatabaseRead();
			$query = 'SELECT sku FROM catalog_product_entity WHERE entity_id = '.$this->getproduct_id();
			$result = $read->fetchAll($query);
			if (isset($result[0]))
			{return $result[0]['sku'];}
		} catch (Exception $e) {
		}

	}

	public function getName()
	{
		try {
			$read = $this->getDatabaseRead();
			$query = 'SELECT value FROM catalog_product_entity_varchar WHERE attribute_id = 60 AND entity_id = '.$this->getproduct_id();
			$result = $read->fetchAll($query);
			if (isset($result[0]))
			{return $result[0]['value'];}
		} catch (Exception $e) {
		}
	}
}