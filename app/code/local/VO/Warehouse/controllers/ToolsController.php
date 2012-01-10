<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2009 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @author : Olivier ZIMMERMANN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VO_Warehouse_ToolsController extends Mage_Adminhtml_Controller_Action
{
	/**
	 * Display massstockeditor grid
	 *
	 */
	public function MassStockEditorAction()
	{
		$this->loadLayout();
		$this->renderLayout();
	}

	/**
	 * Save mass stocks
	 *
	 */
	public function MassStockSaveAction()
	{
		//collect data
		$stringStock = $this->getRequest()->getPost('stock');
		$stringStockMini = $this->getRequest()->getPost('stockmini');

		//process stock
		$t_stock = explode(',', $stringStock);
		foreach($t_stock as $item)
		{
			if ($item != '')
			{
				//retrieve data
				$t = explode('-', $item);
				$productId = $t[0];
				$qty = $t[1];

				//load stockitem and save
				$stockItem = mage::getModel('cataloginventory/stock_item')->loadByProduct($productId);
				if ($stockItem->getId())
				{
					if ($stockItem->getqty() != $qty)
					try {
						Mage::getModel('purchase/stockmovement')->addStockMovement($qty - $stockItem->getqty(),$stockItem->getproduct_id(),'Manual');
					} catch (Exception $e) {
					}
					$stockItem->setqty($qty)->save();
				}
			}
		}

		//process stock mini
		$t_stockMini = explode(',', $stringStockMini);
		foreach($t_stockMini as $item)
		{
			if ($item != '')
			{
				//retrieve data
				$t = explode('-', $item);
				$productId = $t[0];
				$qtyMini = $t[1];

				//load stockitem and save
				$stockItem = mage::getModel('cataloginventory/stock_item')->loadByProduct($productId);
				if ($stockItem->getId())
				{
					$stockItem->setnotify_stock_qty($qtyMini)->setuse_config_notify_stock_qty(0)->save();
				}
			}
		}

	}

	public function downloadPricingAction()
	{
		$fileName   = 'pricing.csv';
		$content    = '"Sku","Name","Landed Cost","Wholesale","Retail","Qty"';
		$content .= "\r\n";
		$collection = Mage::getModel('catalog/product')->getCollection();
		$collection->addAttributeToSelect(array('sku','name','cost','name','type_id','price'));
		$collection->setOrder('sku','asc');
		$collection            ->joinField('qty',
                'cataloginventory/stock_item',
                'qty',
                'product_id=entity_id',
                '{{table}}.stock_id=1',
                'left');

		foreach ($collection as $product)
		{
			$prices = $product->getData('tier_price');

			if (is_null($prices)) {
				$attribute = $product->getResource()->getAttribute('tier_price');
				if ($attribute) {
					$attribute->getBackend()->afterLoad($product);
					$prices = $product->getData('tier_price');
				}
			}

			foreach ($prices as $price) {
				if ($price['cust_group']==2) {
					$WSC  = $price['website_price'];
				}
			}


			$content .= '"'.$product->getSku().'",';
			$content .= '"'.str_replace('"','',$product->getName()).'",';
			$content .= '"'.$product->getData('cost').'",';
			$content .= '"'.$WSC.'",';
			$content .= '"'.$product->getPrice().'",';
			$content .= '"'.$product->getData('qty').'"';
			$content .= "\r\n";
			$prices = null;
			$WSC = null;
		}

		$this->_sendUploadResponse($fileName, $content);
	}

	protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream')
	{
		$response = $this->getResponse();
		$response->setHeader('HTTP/1.1 200 OK','');
		$response->setHeader('Pragma', 'public', true);
		$response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
		$response->setHeader('Content-Disposition', 'attachment; filename='.$fileName);
		$response->setHeader('Last-Modified', date('r'));
		$response->setHeader('Accept-Ranges', 'bytes');
		$response->setHeader('Content-Length', strlen($content));
		$response->setHeader('Content-type', $contentType);
		$response->setBody($content);
		$response->sendResponse();
		die;
	}
}