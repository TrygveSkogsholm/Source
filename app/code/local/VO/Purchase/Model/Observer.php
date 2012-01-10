<?php
class VO_Purchase_Model_Observer
{
	public function __construct()
	{

	}

	public function updateStockMovementFromOrder($observer)
	{
		$order = $observer->getEvent()->getOrder();

		$products = $order->getAllItems();

		foreach ($products as $product)
		{
			if ($product->getproduct_type() == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE)
			{
				$original = Mage::getModel('cataloginventory/stock_item')->load($product->getData('product_id'));
				$original = $original->getQty() + $product->getQtyOrdered();
				Mage::getModel('purchase/stockmovement')->addStockMovement($original,$product->getQtyOrdered()*-1,$product->getproduct_id(),'Ordered',$order->getRealOrderId());
			}
		}
		return $this;
	}

	public function updateStockMovementFromCreditMemo($observer)
	{
		try
		{
			$creditmemo = $observer->getEvent()->getCreditmemo();
			$items = array();
			foreach ($creditmemo->getAllItems() as $item) {
				$return = false;
				if ($item->hasBackToStock()) {
					if ($item->getBackToStock() && $item->getQty()) {
						$return = true;
					}
				} elseif (Mage::helper('cataloginventory')->isAutoReturnEnabled()) {
					$return = true;
				}
				if ($return) {
					if (isset($items[$item->getProductId()])) {
						$items[$item->getProductId()]['qty'] += $item->getQty();
					} else {
						$items[$item->getProductId()] = array(
                        'qty' => $item->getQty(),
                        'item'=> null,
						);
					}
				}
			}
			$qtys = array();
			foreach ($items as $productId => $item) {
				if (empty($item['item'])) {
					$stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($productId);
				} else {
					$stockItem = $item['item'];
				}
				$canSubtractQty = $stockItem->getId() && $stockItem->canSubtractQty();
				if ($canSubtractQty && Mage::helper('catalogInventory')->isQty($stockItem->getTypeId())) {
					$qtys[$productId] = $item['qty'];
				}
			}

			foreach($qtys as $productId => $qty)
			{
				$original = Mage::getModel('cataloginventory/stock_item')->load($productId);
				Mage::getModel('purchase/stockmovement')->addStockMovement($original->getQty()-$qty,$qty,$productId,'Returned',$creditmemo->getId());
			}
			return $this;
		}
		catch (Exception $e)
		{
			Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
		}
	}


	public function updateStockMovementFromAdmin($observer)
	{
		try {
			$product = $observer->getEvent()->getproduct();

			$stockData = $product->getData('stock_data');

			if (isset($stockData['original_inventory_qty']))
			{
				$original = $stockData['original_inventory_qty'];
			}
			else
			{
				$original = 0;
			}
			if (isset($stockData['qty']))
			{
				$current = $stockData['qty'];
			}
			else
			{
				$current = 0;
			}
			$magnitude = $current - $original;

		} catch (Exception $e) {
			Mage::getSingleton('core/adminhtml')->addError($e->getMessage());
		}

		if ($magnitude != 0)
		{
			Mage::getModel('purchase/stockmovement')->addStockMovement($original,$magnitude,$product->getId(),'Manual');
		}
		return $this;
	}
}