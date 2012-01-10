<?php

class VO_Purchase_StockController extends Mage_Adminhtml_Controller_action
{
	protected function _initAction() {
		$this->loadLayout()
		->_setActiveMenu('catalog/stock')
		->_addBreadcrumb(Mage::helper('adminhtml')->__('Catalog'), Mage::helper('adminhtml')->__('Stock Movement'));
		return $this;
	}
	
	public function editAction()
	{
		$model = Mage::getModel('purchase/stockmovement')->load($this->getRequest()->getParam('id'));
		switch ($model->getData('type'))
		{
			case 'Ordered':
				$order = Mage::getModel('sales/order')->loadByIncrementId($model->getData('order_num'));
				$this->_redirect('grandcru_admin/sales_order/view',array('order_id'=>$order->getId()));
				break;
			case 'Returned':
				$this->_redirect('grandcru_admin/sales_creditmemo/view',array('creditmemo_id'=>$model->getData('order_num')));
				break;
			case 'Restocked':
				$this->_redirect('*/order/edit',array('id'=>$model->getData('order_num')));
				break;
			default:
		}
	}

	public function indexAction() {
		$this->_initAction()
		->renderLayout();
	}

	public function exportCsvAction()
	{
		$fileName   = 'stock-movements.csv';
		$content    = $this->getLayout()->createBlock('purchase/stockmovements')
		->getCsv();

		$this->_sendUploadResponse($fileName, $content);
	}

	public function exportAllCsvAction()
	{
		$fileName   = 'stock-movements.csv';
		set_time_limit(250);
		//The format is row = sku column = month

		$collection = Mage::getModel('purchase/stockmovement')->getCollection()
		->addFieldToFilter('type','Ordered');
		$data = array();
		foreach ($collection as $stockmovement)
		{
			$month = date_parse($stockmovement->getdate());
			$month = $month['month'];
			$data[$stockmovement->getSku()][$month] += abs($stockmovement->getmagnitude());
			$month = NULL;
		}

		$content = '"","January","February","March","April","May","June","July","August","September","October","November","December"';
		$content .= "\r\n";

		foreach ($data as $sku => $row)
		{
			//start the row with the sku
			$content .= '"'.$sku.'"';

			for ($i = 1; $i <= 12; $i++)
			{
				//the $row[$i] is the items sold that month
				if (isset($row[$i]) == true)
				{
					$content .= ',"'.$row[$i].'"';
				}
				else
				{
				$content .= ',"0"';
				}
			}
			$content .= "\r\n";
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