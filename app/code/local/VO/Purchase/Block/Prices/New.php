<?php

class VO_Purchase_Block_Prices_New extends Mage_Adminhtml_Block_Template
{
	public function __construct()
	{
		parent::__construct();	
	}

	public function getHeaderText()
	{
		return Mage::helper('purchase')->__("Change Prices");
	}

	public function getPricingJSON()
	{
		if ($object = Mage::registry('purchase_price_group'))
		{
			//switch between possible arguments
			if ($object instanceof VO_Purchase_Model_Price_Plan)
			{
				//It's a price_plan object, load everything from there.
				$plan = $object;
				$planId = $plan->getId();
				$data = array();
				$collection = Mage::getModel('purchase/price')->getCollection()
				->addFieldToFilter('plan_id',$planId);

				foreach ($collection as $price)
				{
					$data[] = array(
					"id"=>$price->getId(),
					"prod_id" => $price->getProductId(),
					"duty"=>$price->getThisDutyRate(),
					"LC"=>$price->getNewLandedCost(),
					"DC-new"=>$price->getNewDistributorCost(),
					"DC-old"=>$price->getOriginalDistributorCost(),
					"WC-new"=>$price->getNewWholesaleCost(),
					"WC-old"=>$price->getOriginalWholesaleCost(),
					"OEM-new"=>$price->getNewOEMCost(),
					"OEM-old"=>$price->getOriginalOEMCost(),
					"RC-new"=>$price->getNewRetailCost(),
					"RC-old"=>$price->getOriginalRetailCost(),
					"FC"=>NULL,
					"SKU"=>$price->getSku(),
					"name"=>addslashes($price->getName()),
					"comments"=>addslashes($price->getComment()),
					"plan_id"=>$planId,
					"ship_id"=>null,
					"po_id"=>null,
					"active"=>$price->getActive(),
					"suppliers"=>$price->getAvailableSuppliers()
					);
				}

				$JSON = array('plan_id'=>$planId,'po_id'=>null,'ship_id'=>null,'data'=>$data,'explanation'=>$plan->getExplanation());
					
				return Zend_Json::encode($JSON);
			}
			else if ($object instanceof VO_Purchase_Model_Order)
			{
				//It's a order object, load as much as you can from prices grab the rest from the PO.
				$PO = $object;
				$supplier = $PO->getSupplier();
				$data = array();

				$collection = Mage::getModel('purchase/price')->getCollection()
				->addFieldToFilter('po_id',$PO->getId())
				->addFieldToFilter('effective',false);


				$InPriceTable = array();
				$InPriceTable[] = 0;
				foreach ($collection as $price)
				{
					$data[] = array(
					"id"=>$price->getId(),
					"prod_id" => $price->getProductId(),
					"duty"=>$price->getThisDutyRate(),
					"LC"=>$price->getNewLandedCost(),
					"DC-new"=>$price->getNewDistributorCost(),
					"DC-old"=>$price->getOriginalDistributorCost(),
					"WC-new"=>$price->getNewWholesaleCost(),
					"WC-old"=>$price->getOriginalWholesaleCost(),
					"OEM-new"=>$price->getNewOEMCost(),
					"OEM-old"=>$price->getOriginalOEMCost(),
					"RC-new"=>$price->getNewRetailCost(),
					"RC-old"=>$price->getOriginalRetailCost(),
					"FC"=>$price->getFirstCost($supplier),
					"SKU"=>$price->getSku(),
					"name"=>addslashes($price->getName()),
					"comments"=>addslashes($price->getComment()),
					"plan_id"=>$price->getPlanId(),
					"ship_id"=>null,
					"po_id"=>$PO->getId(),
					"active"=>$price->getActive(),
					"suppliers"=>array(array
					(
						"id" => $supplier->getId(),
						"name" => $supplier->getName(),
						"firstcost" => $price->getFirstCost($supplier)
					)
					));
					$InPriceTable[] = $price->getProductId();
				}
				
				$leftOvers = $PO->getItems()->addFieldToFilter('`main_table`.`product_id`',array('nin'=>$InPriceTable));

				foreach ($leftOvers as $item)
				{
					$additional = Mage::getModel('purchase/productadditional')->loadByProductId($item->getProductId());
					$data[] = array(
					"id"=>NULL,
					"prod_id" => $item->getProductId(),
					"duty"=>$item->getDutyRate(),
					"LC"=>$additional->getLandedCost(),
					"DC-new"=>NULL,
					"DC-old"=>$additional->getDistributorCost(),
					"WC-new"=>NULL,
					"WC-old"=>$additional->getWholesaleCost(),
					"OEM-new"=>NULL,
					"OEM-old"=>$additional->getOEMCost(),
					"RC-new"=>NULL,
					"RC-old"=>$additional->getRetailCost(),
					"FC"=>$item->getFirstCost(),
					"SKU"=>$item->getSku(),
					"name"=>addslashes($item->getName()),
					"comments"=>NULL,
					"plan_id"=>null,
					"ship_id"=>null,
					"po_id"=>$PO->getId(),
					"active"=>false,
					"suppliers"=>array(array
					(
					"id" => $supplier->getId(),
					"name" => $supplier->getName(),
					"firstcost" => $item->getFirstCost()
					)
					));
				}
				
				$JSON = array('plan_id'=>null,'po_id'=>$PO->getId(),'ship_id'=>null,'total'=>$PO->getGrandtotal(),'data'=>$data);
					
				return Zend_Json::encode($JSON);
			}
			else if ($object instanceof VO_Purchase_Model_Shipment)
			{
				//It's a shipment object, load as much as you can from prices grab the rest from the PO.
				$shipment = $object;
				$supplier = $shipment->getSupplier();
				$data = array();

				$collection = Mage::getModel('purchase/price')->getCollection()
				->addFieldToFilter('ship_id',$shipment->getId())
				->addFieldToFilter('effective',false);


				$InPriceTable = array();
				$InPriceTable[] = 0;
				foreach ($collection as $price)
				{
					$data[] = array(
					"id"=>$price->getId(),
					"prod_id" => $price->getProductId(),
					"duty"=>$price->getThisDutyRate(),
					"LC"=>$price->getNewLandedCost(),
					"DC-new"=>$price->getNewDistributorCost(),
					"DC-old"=>$price->getOriginalDistributorCost(),
					"WC-new"=>$price->getNewWholesaleCost(),
					"WC-old"=>$price->getOriginalWholesaleCost(),
					"OEM-new"=>$price->getNewOEMCost(),
					"OEM-old"=>$price->getOriginalOEMCost(),
					"RC-new"=>$price->getNewRetailCost(),
					"RC-old"=>$price->getOriginalRetailCost(),
					"FC"=>$price->getFirstCost($supplier),
					"SKU"=>$price->getSku(),
					"name"=>addslashes($price->getName()),
					"comments"=>addslashes($price->getComment()),
					"plan_id"=>$price->getPlanId(),
					"ship_id"=>$shipment->getId(),
					"po_id"=>null,
					"active"=>$price->getActive(),
					"suppliers"=>array(array
					(
						"id" => $supplier->getId(),
						"name" => $supplier->getName(),
						"firstcost" => $price->getFirstCost($supplier)
					)
					));
					$InPriceTable[] = $price->getProductId();
				}

				$leftOvers = $shipment->getItems()->addFieldToFilter('product_id',array('nin'=>$InPriceTable));

				foreach ($leftOvers as $item)
				{
					$additional = Mage::getModel('purchase/productadditional')->loadByProductId($item->getProductId());
					$data[] = array(
					"id"=>NULL,
					"prod_id" => $item->getProductId(),
					"duty"=>$item->getOrderProduct()->getFirstCost(),
					"LC"=>$additional->getLandedCost(),
					"DC-new"=>NULL,
					"DC-old"=>$additional->getDistributorCost(),
					"WC-new"=>NULL,
					"WC-old"=>$additional->getWholesaleCost(),
					"OEM-new"=>NULL,
					"OEM-old"=>$additional->getOEMCost(),
					"RC-new"=>NULL,
					"RC-old"=>$additional->getRetailCost(),
					"FC"=>$item->getOrderProduct()->getDutyRate(),
					"SKU"=>$item->getSku(),
					"name"=>$item->addslashes(getName()),
					"comments"=>NULL,
					"plan_id"=>null,
					"ship_id"=>$shipment->getId(),
					"po_id"=>null,
					"active"=>false,
					"suppliers"=>array(array
					(
						"id" => $supplier->getId(),
						"name" => $supplier->getName(),
						"firstcost" => $item->getFirstCost()
					)
					));
				}
				$JSON = array('plan_id'=>null,'po_id'=>null,'ship_id'=>$shipment->getId(),'total'=>$shipment->getGrandtotal(),'data'=>$data);
					
				return Zend_Json::encode($JSON);
			}
			else if (is_array($object)==TRUE)
			{
				//It's just a list of product id's
				foreach ($object as $id)
				{
					$price = Mage::getModel('purchase/price');
					$price->setData('product_id',$id);
					$additional = $price->getProductAdditional();
					$data[] = array(
					"id"=>NULL,
					"prod_id" => $id,
					"duty"=>$price->getThisDutyRate(),
					"LC"=>$additional->getLandedCost(),
					"DC-new"=>NULL,
					"DC-old"=>$additional->getDistributorCost(),
					"WC-new"=>NULL,
					"WC-old"=>$additional->getWholesaleCost(),
					"OEM-new"=>NULL,
					"OEM-old"=>$additional->getOEMCost(),
					"RC-new"=>NULL,
					"RC-old"=>$additional->getRetailCost(),
					"FC"=>NULL,
					"SKU"=>$additional->getSku(),
					"name"=>$additional->getName(),
					"comments"=>NULL,
					"plan_id"=>null,
					"ship_id"=>null,
					"po_id"=>null,
					"active"=>false,
					"suppliers"=>$price->getAvailableSuppliers()
					);
				}
				$JSON = array('plan_id'=>null,'po_id'=>null,'ship_id'=>null,'data'=>$data);
				try {
					$JSON = Zend_Json::encode($JSON);
				} catch (Exception $e) {
					Mage::getSingleton('adminhtml/session')->addError(Mage::helper('purchase')->__($e));
				}
				return $JSON;
			}
		}
	}
	public function getDefaultFreightMargin()
	{
		return Mage::getStoreConfig('pricing/margins/freight_margin');
	}
	
	public function getDefaultRetailMargin()
	{
		//LC margin
		return Mage::getStoreConfig('pricing/margins/retail_above_lc');
	}
	public function getDefaultWholesaleMargin()
	{
		return Mage::getStoreConfig('pricing/margins/wholesale_below_retail');
	}
	public function getDefaultDistributorMargin()
	{
		return Mage::getStoreConfig('pricing/margins/distributor_below_wholesale');
	}
	public function getDefaultOEMMargin()
	{
		return Mage::getStoreConfig('pricing/margins/oem_below_distributor');
	}
	public function getDivergence()
	{
		return Mage::getStoreConfig('pricing/margins/divergence');
	}
}