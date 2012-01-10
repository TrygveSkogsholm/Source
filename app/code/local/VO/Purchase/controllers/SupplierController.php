<?php

class VO_Purchase_SupplierController extends Mage_Adminhtml_Controller_action
{
	protected function _initAction() {
		$this->loadLayout()
		->_setActiveMenu('purchase/suppliers')
		->_addBreadcrumb(Mage::helper('adminhtml')->__('Purchase'), Mage::helper('adminhtml')->__('Suppliers'));
		return $this;
	}

	public function indexAction() {
		$this->_initAction()
		->renderLayout();
	}

	public function editAction() {
		$id     = $this->getRequest()->getParam('id');
		$tab    = '';
		$model  = Mage::getModel('purchase/supplier')->load($id);

		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}

			Mage::register('supplier_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('purchase/suppliers');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Purchase'), Mage::helper('adminhtml')->__('Supplier'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);


			$tabs = $this->getLayout()->createBlock('purchase/suppliers_edit_tabs','supplier-tabs',array('tab'=>$tab));

			$this->_addContent($this->getLayout()->createBlock('purchase/suppliers_edit'))
			->_addLeft($tabs);

			$this->renderLayout();


		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('purchase')->__('Supplier does not exist'));
			$this->_redirect('*/*/');
		}
	}

	public function producteditAction()
	{
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('purchase/supplier_product')->load($id);

		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}

			Mage::register('supplier_product_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('purchase/suppliers');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Purchase'), Mage::helper('adminhtml')->__('Supplier'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);


			$this->_addContent($this->getLayout()->createBlock('purchase/suppliers_edit_tab_products_edit'));
			$this->_addContent($this->getLayout()->createBlock('purchase/suppliers_edit_tab_products_extended'));

			$this->renderLayout();


		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('purchase')->__('Supplier/Product association does not exist'));
			$this->_redirect('*/*/');
		}
	}

	public function productsaveAction()
	{
		if ($data = $this->getRequest()->getPost()) {
			$model = Mage::getModel('purchase/supplier_product');

			$model->setData($data)
			->setId($this->getRequest()->getParam('id'));

			try {

				$model->save();
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('purchase')->__('Supplier/Product association was successfully saved'));
				Mage::getSingleton('adminhtml/session')->setFormData(false);

				$this->_redirect('*/*/edit',array('id' => $this->getRequest()->getParam('supId')));
				return;
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				Mage::getSingleton('adminhtml/session')->setFormData($data);
				$this->_redirect('*/*/productedit', array('id' => $this->getRequest()->getParam('id')));
				return;
			}
		}
		Mage::getSingleton('adminhtml/session')->addError(Mage::helper('purchase')->__('Unable to find Supplier/Product association to save'));
		$this->_redirect('*/*/');
	}



	//Extended
	public function addExtendedAction()
	{
		//$this->getRequest()->getParam('supplier_product_id');
		$this->_forward('editExtended');
	}

	public function editExtendedAction()
	{
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('purchase/supplier_product_extended')->load($id);
		$product  = Mage::getModel('purchase/supplier_product')->load($this->getRequest()->getParam('supplier_product_id'));
		
		if (!($model->getId()) || $id == 0)
		{
			$model = Mage::getModel('purchase/supplier_product_extended');
		}
		
		$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
		if (!empty($data))
		{
			$model->setData($data);
		}
		
		if ($model->getData('sup_item_id') == NULL)
		{
			$model->setData('sup_item_id',$product->getId());
		}

		Mage::register('supplier_product_extended_data', $model);

		$this->loadLayout();
		$this->_setActiveMenu('purchase/suppliers');

		$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Purchase'), Mage::helper('adminhtml')->__('Extended'));

		$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

		$this->_addContent($this->getLayout()->createBlock('purchase/suppliers_edit_tab_products_extended_edit'));

		$this->renderLayout();
	}

	public function saveExtendedAction()
	{
		if ($data = $this->getRequest()->getPost()) {
			$model = Mage::getModel('purchase/supplier_product_extended');

			$model->setData($data)
			->setId($this->getRequest()->getParam('id'))
			->setData('sup_item_id',$this->getRequest()->getParam('supplier_product_id'));

			try
			{
				$model->save();
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('purchase')->__('Extended cost was saved'));
				Mage::getSingleton('adminhtml/session')->setFormData(false);

				$this->_redirect('*/*/productedit',array('id' => $this->getRequest()->getParam('supplier_product_id')));
				return;
			}
			catch (Exception $e)
			{
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				Mage::getSingleton('adminhtml/session')->setFormData($data);
				$this->_redirect('*/*/editExtended', array('id' => $this->getRequest()->getParam('id')));
				return;
			}
		}
		Mage::getSingleton('adminhtml/session')->addError(Mage::helper('purchase')->__('Unable to save extended cost'));
		$this->_redirect('*/*/');
	}

	public function newAction() {
		$this->_forward('edit');
	}

	public function saveAction()
	{
		if ($data = $this->getRequest()->getPost()) {
			$model = Mage::getModel('purchase/supplier');

			$params = $this->getRequest()->getParams();

			$model
			->setcompany_name($params['company_name'])
			->setbuffer_time($params['buffer_time'])
			->setlead_time($params['lead_time'])
			->setshipping_delay($params['shipping_delay'])
			->setdefault_projection_time($params['default_projection_time'])
			->setdefault_carrier($params['default_carrier'])
			->setdefault_method($params['default_method'])
			->setaddress_country($params['address_country'])
			->setaddress_state($params['address_state'])
			->setaddress_street1($params['address_street1'])
			->setaddress_street2($params['address_street2'])
			->setaddress_zip($params['address_zip'])
			->setaddress_city($params['address_city'])
			->setaddress_additional($params['address_additional'])
			->setemail($params['email'])
			->setfax($params['fax'])
			->setcontact_name($params['contact_name'])
			->setphone($params['phone'])
			->setis_manufacturer($params['is_manufacturer']);

			if ($this->getRequest()->getParam('id') != 0 && $this->getRequest()->getParam('id') != null)
			{
				$model->setId($this->getRequest()->getParam('id'));
			}


			try {

				$model->save();

				$newProducts = Mage::helper('purchase')->decodeNewSupplierProducts($data);
				foreach ($newProducts as $newProduct)
				{
					$model->addProduct($newProduct['product_id'],$newProduct['model'],$newProduct['first_cost']);
				}
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('purchase')->__('Supplier was successfully saved'));
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
		Mage::getSingleton('adminhtml/session')->addError(Mage::helper('purchase')->__('Unable to find supplier to save'));
		$this->_redirect('*/*/');
	}

	public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				$model = Mage::getModel('purchase/supplier');

				$model->setId($this->getRequest()->getParam('id'))
				->delete();

				$products = Mage::getModel('purchase/supplier_product')->getCollection()
				->addFieldToFilter('supplier_id',$this->getRequest()->getParam('id'));

				foreach ($products as $product)
				{
					$product->delete();
				}

				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Supplier was successfully deleted'));
				$this->_redirect('*/*/');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}

	public function massRemoveAction()
	{
		$Ids = $this->getRequest()->getParam('product');

		if (!is_array($Ids)) {
			$this->_getSession()->addError($this->__('Please select product(s).'));
		}
		else {
			try {
				foreach ($Ids as $productId) {
					$product = Mage::getSingleton('purchase/supplier_product')->load($productId);
					$product->delete();
				}
				$this->_getSession()->addSuccess(
				$this->__('Total of %d supplier <-> product associations have been deleted.', count($Ids))
				);
			} catch (Exception $e) {
				$this->_getSession()->addError($e->getMessage());
			}
		}

		$Supid = $this->getRequest()->getParam('Sup');
		$this->_redirect('*/*/edit',array('id'=>$Supid));
	}
}