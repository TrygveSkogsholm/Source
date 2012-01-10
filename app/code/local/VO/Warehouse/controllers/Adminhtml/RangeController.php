<?php

class VO_Warehouse_Adminhtml_RangeController extends Mage_Adminhtml_Controller_action
{

	protected function _initAction() {
		$this->loadLayout()
		->_setActiveMenu('warehouse/ranges')
		->_addBreadcrumb(Mage::helper('adminhtml')->__('Warehouse'), Mage::helper('adminhtml')->__('Ranges'));

		return $this;
	}

	public function indexAction() {
		$this->_initAction();
		//$this->_addContent($this->getLayout()->createBlock('dealers/adminhtml_dealers'));
		$this->renderLayout();
	}

	public function editAction()
	{
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('warehouse/range')->load($id);
		$model->getNotes();

		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);

			Mage::register('range_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('warehouse/ranges');

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('warehouse/adminhtml_ranges_edit'));
			$this->_addContent($this->getLayout()->createBlock('warehouse/adminhtml_ranges_grid'));
			$this->_addContent($this->getLayout()->createBlock('warehouse/adminhtml_ranges_notes'));

			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('warehouse')->__('Range does not exist'));
			$this->_redirect('*/*/');
		}
	}

	public function reprintAction()
	{
		$ids = $this->getRequest()->getParam('ids');
		foreach ($ids as $id)
		{
			$print = Mage::getModel('warehouse/print')->load($id);
			if (!isset($pdf)){
				$pdf = Mage::getModel('warehouse/pdf_PickingList')->getPdf($print->getOrder());
			} else {
				$newPages = Mage::getModel('warehouse/pdf_PickingList')->getPdf($print->getOrder());
				$pdf->pages = array_merge($pdf->pages, $newPages->pages);
			}
		}

		if (isset($pdf))
		{
			return $this->_prepareDownloadResponse('Picking Slips '.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf', $pdf->render(), 'application/pdf');
		}
		else
		{
			Mage::getSingleton('adminhtml/session')->addError('No orders to print.');
			$this->_redirect('*/*/');
		}
	}
}