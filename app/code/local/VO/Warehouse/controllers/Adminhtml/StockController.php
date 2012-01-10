<?php

class VO_Warehouse_Adminhtml_StockController extends Mage_Adminhtml_Controller_action
{

	protected function _initAction() {
		$this->loadLayout()
		->_setActiveMenu('warehouse/stock')
		->_addBreadcrumb(Mage::helper('adminhtml')->__('Warehouse'), Mage::helper('adminhtml')->__('Stock'));

		return $this;
	}

	public function inventoryAction() {
		$this->_initAction()
		->renderLayout();
	}

	public function BinSaveAction()
	{
		$product = Mage::getModel('catalog/product')->load($this->getRequest()->getParam('id'));
		$product->setData('binlocation',$this->getRequest()->getParam('value'));
		$product->save();
	}

	public function indexAction() {
		$this->_initAction()
		->renderLayout();
	}

	public function editAction() {
		$this->loadLayout();
		$this->_setActiveMenu('warehouse/stock');
		$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
		$this->_addContent($this->getLayout()->createBlock('warehouse/adminhtml_stock_edit'))
		->_addLeft($this->getLayout()->createBlock('warehouse/adminhtml_stock_edit_tabs'));
		$this->renderLayout();
	}


	public function printA8460Action()
	{
		$pdf = null;
		$productIds = $this->getRequest()->getPost('product_ids');
		foreach ($productIds as $productId)
		{
			$product = Mage::getModel('catalog/product')->load($productId);
				
			if (!isset($pdf)){
				$pdf = Mage::getModel('warehouse/pdf_a8460')->getPdf($product);
			} else {
				$pages = Mage::getModel('warehouse/pdf_a8460')->getPdf($product);
				$pdf->pages = array_merge ($pdf->pages, $pages->pages);
			}
		}
		return $this->_prepareDownloadResponse('A8460 Labels '.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf', $pdf->render(), 'application/pdf');
		//$this->_redirect('*/*/');
	}


	public function printA5163Action()
	{
		$pdf = null;
		$productIds = $this->getRequest()->getPost('product_ids');
		foreach ($productIds as $productId)
		{
			$product = Mage::getModel('catalog/product')->load($productId);
				
			if (!isset($pdf)){
				$pdf = Mage::getModel('warehouse/pdf_a5163')->getPdf($product);
			} else {
				$pages = Mage::getModel('warehouse/pdf_a5163')->getPdf($product);
				$pdf->pages = array_merge ($pdf->pages, $pages->pages);
			}
		}
		return $this->_prepareDownloadResponse('A5163 Labels '.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf', $pdf->render(), 'application/pdf');
		$this->_redirect('*/*/');
	}
	public function printA5168Action()
	{
		$pdf = null;
		$productIds = $this->getRequest()->getPost('product_ids');
		foreach ($productIds as $productId)
		{
			$product = Mage::getModel('catalog/product')->load($productId);
				
			if (!isset($pdf)){
				$pdf = Mage::getModel('warehouse/pdf_a5168')->getPdf($product);
			} else {
				$pages = Mage::getModel('warehouse/pdf_a5168')->getPdf($product);
				$pdf->pages = array_merge ($pdf->pages, $pages->pages);
			}
		}
		return $this->_prepareDownloadResponse('A5168 Labels '.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf', $pdf->render(), 'application/pdf');
		$this->_redirect('*/*/');
	}
	public function printThermAction()
	{
		$pdf = null;
		$productIds = $this->getRequest()->getPost('product_ids');
		foreach ($productIds as $productId)
		{
			$product = Mage::getModel('catalog/product')->load($productId);
				
			if (!isset($pdf)){
				$pdf = Mage::getModel('warehouse/pdf_therm')->getPdf($product);
			} else {
				$pages = Mage::getModel('warehouse/pdf_therm')->getPdf($product);
				$pdf->pages = array_merge ($pdf->pages, $pages->pages);
			}
		}
		return $this->_prepareDownloadResponse('Thermal Labels '.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf', $pdf->render(), 'application/pdf');
		$this->_redirect('*/*/');
	}
	public function printCSAction()
	{
		$pdf = null;
		$productIds = $this->getRequest()->getPost('product_ids');
		foreach ($productIds as $productId)
		{
			$product = Mage::getModel('catalog/product')->load($productId);
				
			if (!isset($pdf)){
				$pdf = Mage::getModel('warehouse/pdf_cs')->getPdf($product);
			} else {
				$pages = Mage::getModel('warehouse/pdf_cs')->getPdf($product);
				$pdf->pages = array_merge ($pdf->pages, $pages->pages);
			}
		}
		return $this->_prepareDownloadResponse('Small Cards '.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf', $pdf->render(), 'application/pdf');
		$this->_redirect('*/*/');
	}
	public function printCNAction()
	{
		$pdf = null;
		$productIds = $this->getRequest()->getPost('product_ids');
		foreach ($productIds as $productId)
		{
			$product = Mage::getModel('catalog/product')->load($productId);
				
			if (!isset($pdf)){
				$pdf = Mage::getModel('warehouse/pdf_cn')->getPdf($product);
			} else {
				$pages = Mage::getModel('warehouse/pdf_cn')->getPdf($product);
				$pdf->pages = array_merge ($pdf->pages, $pages->pages);
			}
		}
		return $this->_prepareDownloadResponse('Normal Cards '.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf', $pdf->render(), 'application/pdf');
		$this->_redirect('*/*/');
	}
	public function printCLAction()
	{
		$pdf = null;
		$productIds = $this->getRequest()->getPost('product_ids');
		foreach ($productIds as $productId)
		{
			$product = Mage::getModel('catalog/product')->load($productId);
				
			if (!isset($pdf)){
				$pdf = Mage::getModel('warehouse/pdf_cl')->getPdf($product);
			} else {
				$pages = Mage::getModel('warehouse/pdf_cl')->getPdf($product);
				$pdf->pages = array_merge ($pdf->pages, $pages->pages);
			}
		}
		return $this->_prepareDownloadResponse('Large Cards '.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf', $pdf->render(), 'application/pdf');
		$this->_redirect('*/*/');
	}

	public function printSmallUPCAction()
	{
		$pdf = null;
		$productIds = $this->getRequest()->getPost('product_ids');
		foreach ($productIds as $productId)
		{
			$product = Mage::getModel('catalog/product')->load($productId);
				
			if (!isset($pdf)){
				$pdf = Mage::getModel('warehouse/pdf_supc')->getPdf($product);
			} else {
				$pages = Mage::getModel('warehouse/pdf_supc')->getPdf($product);
				$pdf->pages = array_merge ($pdf->pages, $pages->pages);
			}
		}
		return $this->_prepareDownloadResponse('Product Labels (UPC) '.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf', $pdf->render(), 'application/pdf');
		$this->_redirect('*/*/');
	}

	public function printMediumUPCAction()
	{
		$pdf = null;
		$productIds = $this->getRequest()->getPost('product_ids');
		foreach ($productIds as $productId)
		{
			$product = Mage::getModel('catalog/product')->load($productId);
				
			if (!isset($pdf)){
				$pdf = Mage::getModel('warehouse/pdf_mupc')->getPdf($product);
			} else {
				$pages = Mage::getModel('warehouse/pdf_mupc')->getPdf($product);
				$pdf->pages = array_merge ($pdf->pages, $pages->pages);
			}
		}
		return $this->_prepareDownloadResponse('Product Labels (UPC) '.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf', $pdf->render(), 'application/pdf');
		$this->_redirect('*/*/');
	}

}