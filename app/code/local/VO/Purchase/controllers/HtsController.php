<?php

class VO_Purchase_HtsController extends Mage_Adminhtml_Controller_action
{
	protected function _initAction()
	{
		$this->loadLayout()
		->_setActiveMenu('purchase/hts')
		->_addBreadcrumb(Mage::helper('adminhtml')->__('Purchase'), Mage::helper('adminhtml')->__('Shipments'));
		return $this;
	}

	//opening page, xml specifies block/shipments/grid as block.
	public function indexAction()
	{
		$this->_initAction()
		->renderLayout();
	}

	public function editAction()
	{
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('purchase/hts')->load($id);

		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}

			Mage::register('hts_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('purchase/hts');

			//$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
			//$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('purchase/hts_edit'));

			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('purchase')->__('Code does not exist'));
			$this->_redirect('*/*/');
		}
	}

	public function newAction() {
		$this->_forward('edit');
	}

	public function saveAction()
	{
		if ($data = $this->getRequest()->getPost()) {
			Mage::getSingleton('core/resource')->getConnection('core_write')->query('INSERT INTO hts (code, rate) VALUES (\''.$data['code'].'\', '.$data['rate'].'); ');
			if ($this->getRequest()->getParam('back')) {
				$this->_redirect('*/*/edit', array('id' => $data['code']));
				return;
			}
			$this->_redirect('*/*/');
			return;
		}
		Mage::getSingleton('adminhtml/session')->addError(Mage::helper('dealers')->__('Unable to find item to save'));
		$this->_redirect('*/*/');
	}
	
	public function deleteAction() 
	{
		$model  = Mage::getModel('purchase/hts')->load($this->getRequest()->getParam('id'));
		$model->delete();
		$this->_redirect('*/*/');
	}
}