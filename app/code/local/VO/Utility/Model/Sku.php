<?php
/**
 *
 * This class really only applies to the VO company itself, although someone else might want to create their own
 * version. The VO sku as of the time of this writing is generalized by CC-NNNN(-***) where C is an uppercase
 * character representing the category or type of the product and there often is a direct relationship between the two.
 * FR = frames, FE = fenders and related, BR = brakes etc...
 *
 * NNNN is numeric value that must be unique within the domain of CC. If it is >= 1000 it should mean that the product
 * is not of VO manufacture. The optional -*** is a three character or number extension ussualy specfying sub-products
 * of the master CC-NNNN.
 *
 * This classes most important function is to recognize and parse a list of skus from a string, even a large one, and return
 * data on it, most important being the product ID.
 * @author Trygve
 *
 */
class VO_Utility_Model_Sku extends Mage_Core_Model_Abstract
{
	private $skus = array();
	private $string;

	public function parse($string,$single = true, $model = true, $category = false)
	{
		/*
		 * Searching for a string pattern in a pile of text? (Superman preamble) Regular Expressions!
		 */
		$this->string = $string;
		if ($single == true)
		{
			preg_match('/[A-Za-z]{2}-[0-9]{4}(?:-[^\s]{0,4}\b)?/', $string, $matches);
		}
		else
		{
			preg_match_all('/[A-Za-z]{2}-[0-9]{4}(?:-[^\s]{0,4}\b)?/', $string, $matches);
		}
		$mageModel = Mage::getModel('catalog/product');
		foreach ($matches[0] as $sku)
		{
			try {
				$id = $mageModel->getIdBySku($sku);
				if (!empty($id))
				{
					//Note this automaticaly prevents doubles
					$this->skus[$id] = array('sku'=>$sku);
					if ($model == true)
					{
						$product = Mage::getModel('catalog/product')->load($id);
						$this->skus[$id]['model'] = $product;
					}
					if ($category == true)
					{
						if (isset($product))
						{
							$this->skus[$id]['category'] = $product->getCategory();
						}
						else
						{
							$this->skus[$id]['category'] = Mage::getModel('catalog/product')->load($id)->getCategory();
						}
					}
				}
				else
				{
					Mage::getSingleton('adminhtml/session')->addError(Mage::helper('purchase')->__($sku.' could not be found in the database.'));
				}
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('purchase')->__($e->getMessage()));
			}
		}
		return $this->skus;
	}

	public function getHyperlinkedText($backend = false)
	{

	}
}