<?php
/**
 *
 * This class represents changes to magento prices. It deals with four prices:
 * landed cost (derived property of several shipments)
 * distributor cost - magento tier price for distributors
 * wholesale cost - magento tier price for wholesale customers
 * retail cost - magento cost to normal customers
 *
 * It's primary purpose is to record and make abstract the act of chaning costs, specificaly in reference
 * to the relationships between these costs. Landed cost is the only cost derived from the rest of the PO module
 * so it can be thought of as the 'base' cost.
 *
 * @author Trygve
 *
 */
class VO_Purchase_Model_Price extends Mage_Core_Model_Abstract
{
	public function _construct()
	{
		parent::_construct();
		$this->_init('purchase/price');
	}

	/**
	 * This function will apply the changes specified to the magento database.
	 * It takes all of this object's properties and changes the magento database regardless of other price changes.
	 * Therefore what it must do is change all other price changes on the magento object to 'not active'
	 * (purchase_pricing.effective = FALSE)
	 * @param array or string or object
	 * @return success report
	 */
	public function change()
	{
		//Change magento
		$additional = $this->getProductAdditional();
		$additional->setDistributorCost($this->getNewDistributorCost());
		$additional->setWholesaleCost($this->getNewWholesaleCost());
		$additional->setOEMCost($this->getNewOEMCost());
		$product = $this->getMagentoProduct();
		$product->setPrice($this->getNewRetailCost())->save();

		//Change others
		$others = $this->getCollection()
		->addFieldToFilter('product_id',array('eq'=>$this->getProductId()))
		->addFieldToFilter('id',array('neq'=>$this->getId()));
		foreach ($others as $otherPriceChange)
		{
			$otherPriceChange->setData('effective',false);
			$otherPriceChange->save();
		}

		//Save this object
		$this->setData('effective',true);
		$this->setchange_text($this->calculateChangeText());
		$this->save();
		return $product;
	}

	/**
	 * This function gives a human readable report of what changes were made
	 * @return string changeText
	 */
	public function calculateChangeText()
	{
		$text = '';
		foreach ($this->getChanges() as $label=>$change)
		{
			if ($change != false)
			{
				if ($change == 'added' || $change == 'removed')
				{
					$text .= $label.": ".$change." at ".Mage::helper('core')->currency($this->getCost($label,($change == 'removed')),true,false)."<br/>";
				}
				else 
				{
					$text .= $label.": ".($change > 0 ? '+' : '').Mage::helper('core')->currency($change,true,false)."<br/>";
				}
			}
		}
		return $text;
	}

	/**
	 * This function returns an very abstract value representing the magnitude of the change
	 * It assigns certain priority to the differences made, the only absolute definition of the value
	 * would be that no change = 0 and between two given changes the greater ratio has a higher value
	 * @return int sizeOfMargin
	 */
	public function calculateAverageMargin()
	{
		return 34;
	}
	
	public function getCost($tag,$original = false)
	{
		switch ($tag) 
		{
			case 'OEM':
				if ($original == false)
				{
					return $this->getNewOEMCost();
				}
				else
				{
					return $this->getOriginalOEMCost();
				}
			break;
			
			case 'Distributor':
				if ($original == false)
				{
					return $this->getNewDistributorCost();
				}
				else
				{
					return $this->getOriginalDistributorCost();
				}
			break;
			
			case 'Wholesale':
				if ($original == false)
				{
					return $this->getNewWholesaleCost();
				}
				else
				{
					return $this->getOriginalWholesaleCost();
				}
			break;
			
			case 'Retail':
				if ($original == false)
				{
					return $this->getNewRetailCost();
				}
				else
				{
					return $this->getOriginalRetailCost();
				}
			break;
			
			default:
				if ($original == false)
				{
					return $this->getNewRetailCost();
				}
				else
				{
					return $this->getOriginalRetailCost();
				}
			break;
		}
	}

	public function getChanges()
	{
		$changes = array();
		$fundamentalChanges = $this->getFundamentalChanges();

		//Retail
		if (array_key_exists('Retail',$fundamentalChanges))
		{
			//The price was deleted or created
			$changes['Retail'] = $fundamentalChanges['Retail'];
		}
		else
		{
			if ($this->getRetailChange() != 0)
			{
				$changes['Retail'] = $this->getRetailChange();
			}
			else
			{
				$changes['Retail'] = false;
			}
		}

		//Wholesale
		if (array_key_exists('Wholesale',$fundamentalChanges))
		{
			//The price was deleted or created
			$changes['Wholesale'] = $fundamentalChanges['Wholesale'];
		}
		else
		{
			if ($this->getWholesaleChange() != 0)
			{
				$changes['Wholesale'] = $this->getWholesaleChange();
			}
			else
			{
				$changes['Wholesale'] = false;
			}
		}
		//Distributor
		if (array_key_exists('Distributor',$fundamentalChanges))
		{
			//The price was deleted or created
			$changes['Distributor'] = $fundamentalChanges['Distributor'];
		}
		else
		{
			if ($this->getDistributorChange() != 0)
			{
				$changes['Distributor'] = $this->getDistributorChange();
			}
			else
			{
				$changes['Distributor'] = false;
			}
		}
		//OEM
		if (array_key_exists('OEM',$fundamentalChanges))
		{
			//The price was deleted or created
			$changes['OEM'] = $fundamentalChanges['OEM'];
		}
		else
		{
			if ($this->getOEMChange() != 0)
			{
				$changes['OEM'] = $this->getOEMChange();
			}
			else
			{
				$changes['OEM'] = false;
			}
		}
		
		return $changes;
	}

	public function getMargins()
	{

	}

	public function getRetailChange()
	{
		return ($this->getNewRetailCost() - $this->getOriginalRetailCost());
	}

	public function getWholesaleChange()
	{
		return ($this->getNewWholesaleCost() - $this->getOriginalWholesaleCost());
	}

	public function getDistributorChange()
	{
		return ($this->getNewDistributorCost() - $this->getOriginalDistributorCost());
	}

	public function getOEMChange()
	{
		return ($this->getNewOEMCost() - $this->getOriginalOEMCost());
	}

	public function getDeletions()
	{
		$deletions = array();
		//Deleltions are characterized as going from original non-null to new null
		if ($this->getOriginalRetailCost() != null && $this->getNewRetailCost() == null)
		{
			$deletions[] = 'Retail';
		}
		if ($this->getOriginalWholesaleCost() != null && $this->getNewWholesaleCost() == null)
		{
			$deletions[] = 'Wholesale';
		}
		if ($this->getOriginalDistributorCost() != null && $this->getNewDistributorCost() == null)
		{
			$deletions[] = 'Distributor';
		}
		if ($this->getOriginalOEMCost() != null && $this->getNewOEMCost() == null)
		{
			$deletions[] = 'OEM';
		}
		return $deletions;
	}

	public function getCreations()
	{
		$creations = array();
		//creations are characterized as going from  original null to new non-null
		if ($this->getOriginalRetailCost() == null && $this->getNewRetailCost() != null)
		{
			$creations[] = 'Retail';
		}
		if ($this->getOriginalWholesaleCost() == null && $this->getNewWholesaleCost() != null)
		{
			$creations[] = 'Wholesale';
		}
		if ($this->getOriginalDistributorCost() == null && $this->getNewDistributorCost() != null)
		{
			$creations[] = 'Distributor';
		}
		if ($this->getOriginalOEMCost() == null && $this->getNewOEMCost() != null)
		{
			$creations[] = 'OEM';
		}
		return $creations;
	}

	public function getFundamentalChanges()
	{
		$fundamentalChanges = array();
		$creations = $this->getCreations();
		$deletions = $this->getDeletions();
		foreach ($creations as $creation)
		{
			$fundamentalChanges[$creation] = 'added';
		}
		foreach ($deletions as $deletion)
		{
			$fundamentalChanges[$deletion] = 'removed';
		}
		return $fundamentalChanges;
	}

	/**
	 * @param array || float $data
	 * @deprecated TRUE
	 * This function predicts the landed cost in one of two ways.
	 * It either uses a percentage increase over first cost or uses a shipping cost
	 * and quantity to predict individual landed cost. It determines from the data (array vs float)
	 * which it is being asked to do.
	 */
	public function predictLandedCost($data)
	{
		if (is_array($data))
		{
			//@todo Finish this function.
			//$itemPrice = $data[];
			$landedCost = ( 1 + ($data['freight']/$shipmentTotal) ) * $itemPrice;
			return $landedCost;
		}
	}

	/*
	 * Related object functions:
	 */

	/**
	 * @return Mage_Catalog_Model_Product magento product
	 * Retreives the magento object associated with this price change
	 */
	public function getMagentoProduct()
	{
		$model = Mage::getModel('catalog/product')->load($this->getProductId());
		return $model;
	}

	public function getProductAdditional()
	{
		$model = Mage::getModel('purchase/productadditional')->loadByProductId($this->getProductId());
		return $model;
	}

	public function getLandedCost()
	{
		return $this->getProductAdditional()->getLandedCost();
	}

	public function getAvailableSuppliers()
	{
		$data = array();
		$collection = Mage::getModel('purchase/supplier_product')->getCollection()
		->addFieldToFilter('product_id',$this->getProductId());
		foreach ($collection as $supplierProduct)
		{
			$data[] = array
			(
			"id" => $supplierProduct->getSupplier()->getId(),
			"name" => $supplierProduct->getSupplier()->getName(),
			"firstcost" => $supplierProduct->getFirstCost()
			);
		}
		return $data;
	}

	public function getFirstCost($supplier)
	{
		return $supplier->getProductById($this->getProductId())->getFirstCost();
	}

	public function getThisDutyRate()
	{
		try {
			$hts = Mage::getModel('purchase/hts')->load($this->getProductAdditional()->getHtsCode());

			return $hts->getDutyRate();
		} catch (Exception $e) {
			Mage::getSingleton('adminhtml/session')->addError('Error loading  '.$this->getThisSku());
			return 1;
		}
	}

	/*
	 * Property Change Functions:
	 */

	/**
	 * @param bool active
	 * Sets the active
	 */
	public function setActive($active)
	{
		$this->seteffective($active);
	}

	/**
	 * @return bool active
	 * Gets the active
	 */
	public function getActive()
	{
		return $this->geteffective();
	}

	/**
	 * @param string name
	 * Sets the name
	 */
	public function setName($name)
	{
		$this->setData('name',$name);
	}

	/**
	 * @return string name
	 * Gets the name
	 */
	public function getName()
	{
		return $this->getData('name');
	}

	/**
	 * @param string comment
	 * Sets the comment
	 */
	public function setComment($comment)
	{
		$this->setData('comment',$comment);
	}

	/**
	 * @return string comment
	 * Gets the comment
	 */
	public function getComment()
	{
		return $this->getData('comment');
	}

	/**
	 * @param string sku
	 * Sets the sku
	 */
	public function setSku($sku)
	{
		$this->setData('sku',$sku);
	}

	/**
	 * @return string sku
	 * Gets the sku
	 */
	public function getSku()
	{
		return $this->getData('sku');
	}

	/**
	 * @param string id
	 * Sets the product_id
	 */
	public function setProductId($id)
	{
		$this->setproduct_id((int)$id);
	}

	/**
	 * @return string id
	 * Gets the product_id
	 */
	public function getProductId()
	{
		return $this->getproduct_id();
	}


	/**
	 * @param float value
	 * Sets the OEM cost before this price change.
	 */
	public function setOriginalOEMCost($value)
	{
		$this->setold_oem_cost($value);
	}

	/**
	 * @param float value
	 * Sets the OEM cost after this price change.
	 */
	public function setNewOEMCost($value)
	{
		$this->setnew_oem_cost($value);
	}

	/**
	 * @param float value
	 * Sets the landed cost before this price change.
	 */
	public function setOriginalLandedCost($value)
	{
		$this->setold_landed_cost($value);
	}

	/**
	 * @param float value
	 * Sets the distributor cost before this price change.
	 */
	public function setOriginalDistributorCost($value)
	{
		$this->setold_distributor_cost($value);
	}

	/**
	 * @param float value
	 * Sets the wholesale cost before this price change.
	 */
	public function setOriginalWholesaleCost($value)
	{
		$this->setold_wholesale_cost($value);
	}

	/**
	 * @param float value
	 * Sets the retail cost before this price change.
	 */
	public function setOriginalRetailCost($value)
	{
		$this->setold_retail_cost($value);
	}

	/**
	 * @param float value
	 * Sets the landed cost after this price change.
	 */
	public function setNewLandedCost($value)
	{
		$this->setnew_landed_cost($value);
	}

	/**
	 * @param float value
	 * Sets the distributor cost after this price change.
	 */
	public function setNewDistributorCost($value)
	{
		$this->setnew_distributor_cost($value);
	}

	/**
	 * @param float value
	 * Sets the wholesale cost after this price change.
	 */
	public function setNewWholesaleCost($value)
	{
		$this->setnew_wholesale_cost($value);
	}

	/**
	 * @param float value
	 * Sets the retail cost after this price change.
	 */
	public function setNewRetailCost($value)
	{
		$this->setnew_retail_cost($value);
	}

	/**
	 * @return float value
	 * gets the OEM cost before this price change.
	 */
	public function getOriginalOEMCost()
	{
		return $this->getold_oem_cost();
	}

	/**
	 * @return float value
	 * gets the OEM cost after this price change.
	 */
	public function getNewOEMCost()
	{
		return $this->getnew_oem_cost();
	}

	/**
	 * @return float value
	 * gets the landed cost before this price change.
	 */
	public function getOriginalLandedCost()
	{
		return $this->getold_landed_cost();
	}

	/**
	 * @return float value
	 * gets the distributor cost before this price change.
	 */
	public function getOriginalDistributorCost()
	{
		return $this->getold_distributor_cost();
	}

	/**
	 * @return float value
	 * gets the wholesale cost before this price change.
	 */
	public function getOriginalWholesaleCost()
	{
		return $this->getold_wholesale_cost();
	}

	/**
	 * @return float value
	 * gets the retail cost before this price change.
	 */
	public function getOriginalRetailCost()
	{
		return $this->getold_retail_cost();
	}

	/**
	 * @return float value
	 * gets the landed cost after this price change.
	 */
	public function getNewLandedCost()
	{
		return $this->getnew_landed_cost();
	}

	/**
	 * @return float value
	 * gets the distributor cost after this price change.
	 */
	public function getNewDistributorCost()
	{
		return $this->getnew_distributor_cost();
	}

	/**
	 * @return float value
	 * gets the wholesale cost after this price change.
	 */
	public function getNewWholesaleCost()
	{
		return $this->getnew_wholesale_cost();
	}

	/**
	 * @return float value
	 * gets the retail cost after this price change.
	 */
	public function getNewRetailCost()
	{
		return $this->getnew_retail_cost();
	}

	public function getPlanId()
	{
		return $this->getplan_id();
	}
	public function getShipId()
	{
		return $this->getship_id();
	}
	public function getPOId()
	{
		return $this->getpo_id();
	}
}