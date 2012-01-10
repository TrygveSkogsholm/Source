<?php

class VO_Dealers_Adminhtml_DealerController extends Mage_Adminhtml_Controller_action
{
	protected function _initAction() {
		$this->loadLayout()
		->_setActiveMenu('customer/dealers')
		->_addBreadcrumb(Mage::helper('adminhtml')->__('Customer'), Mage::helper('adminhtml')->__('Dealers'));

		return $this;
	}

	public function indexAction() {
		$this->_initAction();
		//$this->_addContent($this->getLayout()->createBlock('dealers/adminhtml_dealers'));
		$this->renderLayout();
	}

	public function editAction() {
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('dealers/dealers')->load($id);

		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$data['latitude'] = NULL;
				$model->setData($data);
			}

			Mage::register('dealers_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('customer/dealers');

			//$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
			//$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			//$this->_addContent($this->getLayout()->createBlock('dealers/adminhtml_dealers_edit_tabs'));
			$this->_addContent($this->getLayout()->createBlock('dealers/adminhtml_dealers_edit'))
			->_addLeft($this->getLayout()->createBlock('dealers/adminhtml_dealers_edit_tabs'));

			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('dealers')->__('Dealer does not exist'));
			$this->_redirect('*/*/');
		}
	}

	public function newAction() {
		$this->_forward('edit');
	}

	public function zsaveAction() {
		var_dump($this->getRequest()->getPost());
		$data = $this->getRequest()->getPost();
		try
		{echo Mage::getModel('customer/customer')->load($data['account_id'])->getGroupId();
		echo 'made it through the try';}
		catch (Exception $e)
		{echo 'not found';}
		$check = Mage::getModel('customer/customer')->load($data['account_id']);

	}

	public function saveAction() {
		if ($data = $this->getRequest()->getPost()) {
			$model = Mage::getModel('dealers/dealers');
			$model->setData($data)
			->setId($this->getRequest()->getParam('id'));
			$nameCheck = Mage::getModel('customer/customer')->load($data['account_id'])->getName();
			if (Mage::getModel('customer/customer')->load($data['account_id'])->getGroupId() == 2){
				try {
					if ($model->getCreatedTime == NULL || $model->getUpdateTime() == NULL) {
						$model->setCreatedTime(now())
						->setUpdateTime(now());
					} else {
						$model->setUpdateTime(now());
					}
					
					if ($model->hasDataChanges())
					{
						//Ask google again
						$model->setis_found(true);
					}

					$model->save();
					Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('dealers')->__('Dealer was successfully saved'));
					Mage::getSingleton('adminhtml/session')->setFormData(false);

					if ($this->getRequest()->getParam('back')) {
						$this->_redirect('*/*/edit', array('id' => $model->getId()));
						return;
					}
					$this->_redirect('*/*/');
					return;
				} catch (Exception $e) {
					Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
					Mage::getSingleton('adminhtml/session')->setFormData($data);
					$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
					return;
				}
			}
			Mage::getSingleton('adminhtml/session')->addError('There is no wholesale customer with that account #');
			$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			return;

		}
		Mage::getSingleton('adminhtml/session')->addError(Mage::helper('dealers')->__('Unable to find item to save'));
		$this->_redirect('*/*/');
	}

	public function approveAction()
	{
		try
		{
			$model = Mage::getModel('dealers/dealers')->load($this->getRequest()->getParam('id'));
			$model->approve();
		}
		catch (Exception $e)
		{
			Mage::getSingleton('adminhtml/session')->addError('Could not approve. '.$e->getMessage());
			$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
		}
		Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('dealers')->__('Dealer application accepted'));
		$this->_redirect('*/*/edit', array('id' => $model->getId()));
		return;
	}

	public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				$model = Mage::getModel('dealers/dealers');
					
				$model->setId($this->getRequest()->getParam('id'))
				->delete();

				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Dealer was successfully deleted'));
				$this->_redirect('*/*/');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}

	public function exportCsvAction()
	{
		$fileName   = 'dealers.csv';
		$content    = $this->getLayout()->createBlock('dealers/adminhtml_dealers_grid')
		->getCsv();

		$this->_sendUploadResponse($fileName, $content);
	}

	public function exportAllCsvAction()
	{
		$fileName   = 'dealers.csv';
		$content    = '"dealer_id","account_id","is_approved","is_primary","is_displayed","type","name","description","country","state","zip","city","address","hours","phone","email","website","longitude","latitude"
        ';
		$collection = Mage::getModel('dealers/dealers')->getCollection();
		foreach ($collection as $dealer)
		{
			$content .= ''.$dealer->getData('dealer_id').',';
			$content .= '"'.$dealer->getData('account_id').'",';
			$content .= '"'.$dealer->getData('is_approved').'",';
			$content .= '"'.$dealer->getData('is_primary').'",';
			$content .= '"'.$dealer->getData('is_displayed').'",';
			$content .= '"'.$dealer->getData('type').'",';
			$content .= '"'.$dealer->getData('name').'",';
			$content .= '"'.$dealer->getData('description').'",';
			$content .= '"'.$dealer->getData('country').'",';
			$content .= '"'.$dealer->getData('state').'",';
			$content .= '"'.$dealer->getData('zip').'",';
			$content .= '"'.$dealer->getData('city').'",';
			$content .= '"'.$dealer->getData('address').'",';
			$content .= '"'.$dealer->getData('hours').'",';
			$content .= '"'.$dealer->getData('phone').'",';
			$content .= '"'.$dealer->getData('email').'",';
			$content .= '"'.$dealer->getData('website').'",';
			$content .= '"'.$dealer->getData('longitude').'",';
			$content .= '"'.$dealer->getData('latitude').'"';
			$content .= '
        	';
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
