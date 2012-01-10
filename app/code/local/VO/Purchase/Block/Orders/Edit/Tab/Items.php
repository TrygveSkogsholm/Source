<?php

class VO_Purchase_Block_Orders_Edit_Tab_Items extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareForm()
	{
		$this->setTemplate('purchase/order/items.phtml');
		return parent::_prepareForm();
	}

	public function getAvailibleJSON()
	{
		$javaObject = array();
		$order = $this->getOrder();
		$javaObject['id'] = $order->getId();
		$javaObject['supplier_id'] = $order->getsupplier_id();
		$javaObject['status'] = $order->Status();

		if ($order->Status() == 1)
		{
			foreach ($order->getAvailableProducts() as $item)
			{
				/*
				 * The data required is as follows:
				 * Sku
				 * Model
				 * Name
				 * First Cost
				 * Stock
				 * On Order
				 */
				$itemJS = array(
					'id'=>$item->getId(),
					'active'=>true,
					'sku'=>$item->getSku(),
					'model'=>$item->getModelString(),
					'name'=>$item->getName(),
					'first_cost'=>$item->getFirstCost(),
					'stock'=>$item->getStock(),
					'on_order'=>$item->getOnOrder(),
					'case_qty'=>$item->getCaseQty()
				);
				$javaObject['availible_items']['sup_item_'.$item->getId()] = $itemJS;
			}
		}
		else
		{
				$javaObject['availible_items'] = NULL;
		}

		//We may want to have a JS object for this as some point
		/* foreach ($order->getItems() as $item)
		 {
			$extendedCosts = array();
			foreach ($item->getExtendedCosts() as $extendedCost)
			{
			$extendedCosts[] = array('name'=>$extendedCost->getName(),'description'=>$extendedCost->getDescription(),'cost'=>$extendedCost->getCost());
			}
			$itemJS = array(
			'id'=>$item->getId(),
			'sku'=>$item->getSku(),
			'model'=>$item->getModelString(),
			'name'=>$item->getName(),
			'first_cost'=>$item->getFirstCost(),
			'qty'=>$item->getItemQty(),
			'duty'=>$item->getDutyRate(),
			'subtotal'=>$item->getSubtotal(),
			'extended_costs'=>$extendedCosts,
			'grand_total'=>$item->getGrandtotal()
			);
			$javaObject['items']['po_item_'.$item->getId()] = $itemJS;
			}*/

		foreach ($order->getAllShipments() as $shipment)
		{
			$javaObject['shipments']['shipment_'.$shipment->getId()]['id'] = $shipment->getId();
			$javaObject['shipments']['shipment_'.$shipment->getId()]['status'] = $shipment->Status();
			foreach ($shipment->loadCoreItemData() as $shipmentItem)
			{
				$shipmentItemJS = array(
					'id'=>$shipmentItem->getId(),
					'sku'=>$shipmentItem->getSku(),
					'name'=>$shipmentItem->getName(),
					'qty'=>$shipmentItem->getItemQty()
				);
				$javaObject['shipments']['shipment_'.$shipment->getId()]['items']['ship_item_'.$shipmentItem->getId()] = $shipmentItemJS;
			}
		}
		return Zend_Json::encode($javaObject);
	}

	public function getOrder()
	{
		if ( Mage::getSingleton('adminhtml/session')->getPurchaseOrderData() )
		{
			return Mage::getSingleton('adminhtml/session')->getPurchaseOrderData();
			Mage::getSingleton('adminhtml/session')->setPurchaseOrderData(null);
		} elseif ( Mage::registry('purchase_order_data') ) {
			return Mage::registry('purchase_order_data');
		}
	}
}