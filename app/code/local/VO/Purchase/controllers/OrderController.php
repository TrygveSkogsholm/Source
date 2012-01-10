<?php

class VO_Purchase_OrderController extends Mage_Adminhtml_Controller_action
{
	protected function _initAction() {
		$this->loadLayout()
		->_setActiveMenu('purchase/orders')
		->_addBreadcrumb(Mage::helper('adminhtml')->__('Purchase'), Mage::helper('adminhtml')->__('Orders'));
		return $this;
	}

	public function indexAction() {
		$this->_initAction()
		->renderLayout();
	}

	public function testAction() {
		$this->_initAction()
		->renderLayout();
	}

	public function editAction()
	{
		$id     = $this->getRequest()->getParam('id');
		$tab    = $this->getRequest()->getParam('tab');
		$model  = Mage::getModel('purchase/order')->load($id);

		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}

			Mage::register('purchase_order_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('purchase/orders');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Purchase'), Mage::helper('adminhtml')->__('Order'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
			$this->_addContent($this->getLayout()->createBlock('purchase/orders_edit'))
			->_addLeft($this->getLayout()->createBlock('purchase/orders_edit_tabs','order-tabs',array('tab'=>$tab)));

			$this->renderLayout();

		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('purchase')->__('Order does not exist'));
			$this->_redirect('*/*/');
		}
	}

	/*
	 *Add a new purchase order, when button on grid view is clicked. Creates and displays the order/supplierchooser block and template
	 *
	 */
	public function newAction()
	{
		$this->loadLayout();
		$this->_setActiveMenu('purchase/orders');
		$this->_addContent($this->getLayout()->createBlock('purchase/orders_supplierchooser'));
		$this->renderLayout();
	}

	public function createAction()
	{
		//This actually gets you to the edit screen, as you can see it creates and saves the model before letting
		// you get at it.
		$newOrder = Mage::getModel('purchase/order')
		->setsupplier_id($this->getRequest()->getParam('supplier_id'))
		->setdate_created(now());
		$newOrder->save();

		$this->_redirect('*/*/edit', array('id' => $newOrder->getId()));
	}

	/*
	 * Should contain logic to update any shipment, supplier, PO products, supplier products neccesary.
	 *
	 */
	public function saveAction()
	{
		if ($data = $this->getRequest()->getPost())
		{
			//Grab the model
			$model = Mage::getModel('purchase/order')->load($this->getRequest()->getParam('id'));

			/*
			 * There are 6 peices of information that need to be saved, everything else is programaticaly
			 * updated at special events
			 *
			 * Items - obsolete, now updated real time via ajax functions above.
			 * Is Paid *
			 * Payment Method *
			 * Ship From
			 * Ship To
			 * Comments
			 *
			 */

			//Check to see if the next status is called for.
			//Update Log

			try {
				//Ship from and to
				/*
				 * The following code checks to see if the database needs custom values or not.
				 */
				$defaultToAddress = Mage::getStoreConfig('orders/shipping_address');
				$defaultFromAddress = $model->getSupplier()->getAddress();
				$postToAddress = array('name' => $data['to-name'],'contact' => $data['to-contact'],'street1' => $data['to-address1'],'street2' => $data['to-address2'],'city' => $data['to-city'],'zip' => $data['to-zip'],'state' => (isset($data['to-state']))? $data['to-state']:$data['to-region'],'country' => $data['to-country']);
				$postFromAddress = array('name' => $data['from-name'],'contact' => $data['from-contact'],'street1' => $data['from-address1'],'street2' => $data['from-address2'],'city' => $data['from-city'],'zip' => $data['from-zip'],'state' => (isset($data['from-state']))? $data['from-state']:$data['from-region'],'country' => $data['from-country']);
				$currentToAddress = $model->getAddress('to');
				$currentFromAddress = $model->getAddress('from');

				if ($postToAddress != $defaultToAddress)
				{
					$model->setship_to_name($postToAddress['name']);
					$model->setship_to_contact($postToAddress['contact']);
					$model->setship_to_address1($postToAddress['street1']);
					$model->setship_to_address2($postToAddress['street2']);
					$model->setship_to_country($postToAddress['country']);
					$model->setship_to_state($postToAddress['state']);
					$model->setship_to_zip($postToAddress['zip']);
					$model->setship_to_city($postToAddress['city']);
				}
				else if ($defaultToAddress != $currentToAddress)
				{
					$model->setship_to_name(NULL);
					$model->setship_to_contact(NULL);
					$model->setship_to_address1(NULL);
					$model->setship_to_address2(NULL);
					$model->setship_to_country(NULL);
					$model->setship_to_state(NULL);
					$model->setship_to_zip(NULL);
					$model->setship_to_city(NULL);
				}
				if ($postFromAddress != $defaultFromAddress)
				{
					$model->setship_from_name($postFromAddress['name']);
					$model->setship_from_contact($postFromAddress['contact']);
					$model->setship_from_address1($postFromAddress['street1']);
					$model->setship_from_address2($postFromAddress['street2']);
					$model->setship_from_country($postFromAddress['country']);
					$model->setship_from_state($postFromAddress['state']);
					$model->setship_from_zip($postFromAddress['zip']);
					$model->setship_from_city($postFromAddress['city']);
				}
				else if ($defaultFromAddress != $currentFromAddress)
				{
					$model->setship_from_name(NULL);
					$model->setship_from_contact(NULL);
					$model->setship_from_address1(NULL);
					$model->setship_from_address2(NULL);
					$model->setship_from_country(NULL);
					$model->setship_from_state(NULL);
					$model->setship_from_zip(NULL);
					$model->setship_from_city(NULL);
				}
				//end Addresses

				//Comments
				$model->setcomments($data['comments']);
				$model->setpayment_method($data['payment_method']);
				if (isset($data['is_paid']))
				{
					$model->setis_paid(true);
				}

				$model->updateStatus();
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('purchase')->__('Purchase order was successfully saved'));
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

			//echo $this->getRequest()->getParam('id');
			//var_dump($data);
		}
		Mage::getSingleton('adminhtml/session')->addError(Mage::helper('purchase')->__('Unable to find purchase order to save'));
		$this->_redirect('*/*/');
	}


	//Don't forget childern.
	public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				$model = Mage::getModel('purchase/order');

				$model->setId($this->getRequest()->getParam('id'));
				foreach ($model->getItems() as $item)
				{
					if (Mage::getStoreConfig('orders/deletion/absolute') == true)
					{
						foreach ($item->getAllShipmentObjects() as $shipmentObject)
						{
							if($shipmentObject->IsReceived() == true)
							{
								$stockItem = $shipmentObject->getMagentoStockItem();
								$stockItem->setQty($stockItem->getQty()- $shipmentObject->getItemQty());
								Mage::getModel('purchase/stockmovement')->addStockMovement($shipmentObject->getItemQty()*-1,$shipmentObject->getProductId(),'Canceled PO',$model->getId());
								$stockItem->save();
							}
							$shipmentObject->delete();
						}
					}
					$item->delete();
				}
				$model->delete();

				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Purchase order was successfully deleted'));
				$this->_redirect('*/*/');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}

	public function printAction()
	{
		$model = Mage::getModel('purchase/order');
		$model->load($this->getRequest()->getParam('id'));

		if ($this->getRequest()->getParam('show_comments') == 'on')
		{
			$model->showComment = true;
		}
		else
		{
			$model->showComment = false;
		}
		$pdf = Mage::getModel('purchase/pdf_order')->getPdf($model);
		return $this->_prepareDownloadResponse('Purchase Order #'.$model->getId().' for '.$model->getSupplier()->getName().'.pdf', $pdf->render(), 'application/pdf');
	}

	public function modifyAction()
	{
		$model = Mage::getModel('purchase/order');
		$model->load($this->getRequest()->getParam('id'));
		$model->setOrderStatus(1);
		$model->save();
		$this->_redirect('*/*/edit', array('id' => $model->getId()));
	}

	public function sendAction()
	{
		$model = Mage::getModel('purchase/order');
		$model->setId($this->getRequest()->getParam('id'));
		$model->send();
		$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
	}

	/*
	 * Ajax functions, don't forget response must always be JSON parsable (except totals)
	 */
	public function AJAXtotalsAction()
	{
		$order = Mage::getModel('purchase/order')->load($this->getRequest()->getParam('id'));
		echo '<div>Subtotal: '.Mage::helper('core')->currency($order->getSubtotal()).'</div>';
		echo '<div>Estimated Duty: '.Mage::helper('core')->currency($order->getTotalDuty()).'</div>';
	}

	public function AJAXgetUnshippedQtyAction()
	{
		echo Zend_Json::encode(Mage::getModel('purchase/order_product')->load($this->getRequest()->getParam('id'))->getUnshippedQty());
	}

	public function AJAXupdateAction()
	{
		try {
			$field = $this->getRequest()->getParam('field');
			$data = $this->getRequest()->getParam('data');
			$dataIsAcceptable = true;
			$response = array();
			switch ($field) {
				case 'qty':
					$data = (int)$data;
					if ($data < 0)
					{
						$response['success'] = false;
						$response['error'] = 'Please enter a positive integer';
						$dataIsAcceptable = false;
					};
					break;

				default:
					;
					break;
			}
			if ($dataIsAcceptable)
			{
				$product = Mage::getModel('purchase/order_product')->load($this->getRequest()->getParam('id'));
				if ($field == 'first_cost')
				{
					$product->updateFirstCost($data);
				}
				else
				{
					$product->setData($field,$data);
					$product->save();
				}
				if ($product->getData($field) == $data)
				{
					//Update other things that must be updated:
					if ($product->getOrder()->getStatus() >= 5)
					{
						foreach($product->getReceivedShipmentObjects() as $received)
						{
							$received->calculateLandedCost();
							$received->getShipment()->loadAdditional($received->getProductId())->calculateAverageLandedCost();
							$received->save();
						}
					}
					$response['success'] = true;
					$response['data'] = $data;
					$response['total'] = Mage::helper('core')->currency($product->getSubtotal(),true,false);
				}
				else
				{
					$response['success'] = false;
					$response['error'] = 'That data would not save, it is probably of an incorrect type.';
					$response['data'] = $product->getData($field);
				}
			}
		} catch (Exception $e) {
			$response['success'] = false;
			$response['error'] = $e->getMessage();
		}
		echo Zend_Json::encode($response);
		return;
	}

	public function AJAXaddAction()
	{
		$po = Mage::getModel('purchase/order')->load($this->getRequest()->getParam('po_id'));
		$dataIsAcceptable = true;
		$response = array();
		try
		{
			$item = $po->addItem($this->getRequest()->getParam('supplier_product_id'),$this->getRequest()->getParam('qty'));
			if ($item)
			{
				$response['success'] = true;
				$response['id'] = $item->getId();
				if ($po->isDuty())
				{
					$response['rate'] = Mage::helper('purchase')->formatPercentage($item->getDutyRate());
				}
				$response['qty'] = $item->getItemQty();
				$response['total'] = $item->getSubtotal();
				$response['extended'] = array();
				foreach($item->getExtendedCosts() as $extended)
				{
					$response['extended'][] = array('id'=>$extended->getId(),'name'=>$extended->getName(),'description'=>$extended->getDescription(),'cost'=>$extended->getCost());
				}
			}
			else
			{
				$response['success'] = false;
				$response['error'] = 'There was a problem adding the item.';
			}
		}
		catch (Exception $e)
		{
			$response['success'] = false;
			$response['error'] = $e->getMessage();
		}
		echo Zend_Json::encode($response);
		return;
	}

	public function AJAXremoveAction()
	{
		$model = Mage::getModel('purchase/order_product')->load($this->getRequest()->getParam('id'));
		try
		{
			$model = Mage::getModel('purchase/order_product')->load($this->getRequest()->getParam('id'));
			if ($model->getId() != NULL)
			{
				$response['on_order'] = $model->getOnOrder() - $model->getItemQty();
				$response['stock'] = $model->getStock();
				$response['supplier_product_id'] = $model->getSupplierProduct()->getId();
				$response['case_qty'] = $model->getSupplierProduct()->getCaseQty();
				$model->delete();
			}
			else
			{
				$response['success'] = false;
				$response['error'] = 'Could not find item to delete.';
				return;
			}
			$response['success'] = true;
		}
		catch (Exception $e) {
			$response['success'] = false;
			$response['error'] = $e->getMessage();
		}
		echo Zend_Json::encode($response);
		return;
	}

	public function AJAXaddToShipmentAction()
	{

	}

	public function AJAXremoveFromShipmentAction()
	{

	}

	public function AJAXupdateInShipmentAction()
	{

	}

	public function AJAXnewShipmentAction()
	{

	}

	public function AJAXshipAction()
	{

	}

	public function AJAXreceiveAction()
	{

	}

	public function AJAXcancelAction()
	{

	}

	public function AJAXaddExtendedAction()
	{
		try
		{
			$model = Mage::getModel('purchase/order_product')->load($this->getRequest()->getParam('id'));
			if ($model->getId() != NULL)
			{
				//Validate
				$cost = floatval(ereg_replace("[^-0-9\.]","",$this->getRequest()->getParam('cost')));
				if ($cost <= 0)
				{
					$response['success'] = false;
					$response['error'] = 'Cost was either not positive or parsed wrong.';
					echo Zend_Json::encode($response);
					return;
				}
				$name = $this->getRequest()->getParam('name');
				$description = $this->getRequest()->getParam('description');
				if ($this->getRequest()->getParam('shown') == 'true')
				{
					$displayed = true;
				}
				else
				{
					$displayed = false;
				}

				$extended = $model->addExtendedCost($cost,$name, $description,$displayed);
				$response['id'] = $extended->getId();
			}
			else
			{
				$response['success'] = false;
				$response['error'] = 'Could not find item to add extended cost to.';
				echo Zend_Json::encode($response);
				return;
			}
			$response['success'] = true;
		}
		catch (Exception $e) {
			$response['success'] = false;
			$response['error'] = $e->getMessage();
		}
		echo Zend_Json::encode($response);
		return;
	}

	public function AJAXupdateExtendedAction()
	{
		try
		{
			$model = Mage::getModel('purchase/order_product_extended')->load($this->getRequest()->getParam('id'));
			if ($model->getId() != NULL)
			{
				$cost = floatval(ereg_replace("[^-0-9\.]","",$this->getRequest()->getParam('cost')));
				if ($cost > 0)
				{
					$model->setData('cost',$cost);
					$model->save();
					$response['value'] = $model->getCost();
				}
				else
				{
					$response['success'] = false;
					$response['error'] = 'That data was incorect.';
					$response['value'] = $model->getCost();
					echo Zend_Json::encode($response);
					return;
				}
			}
			else
			{
				$response['success'] = false;
				$response['error'] = 'Could not find extended cost to delete.';
				echo Zend_Json::encode($response);
				return;
			}
			$response['success'] = true;
		}
		catch (Exception $e) {
			$response['success'] = false;
			$response['error'] = $e->getMessage();
		}
		echo Zend_Json::encode($response);
		return;
	}

	public function AJAXremoveExtendedAction()
	{
		try
		{
			$model = Mage::getModel('purchase/order_product_extended')->load($this->getRequest()->getParam('id'));
			if ($model->getId() != NULL)
			{
				$model->delete();
			}
			else
			{
				$response['success'] = false;
				$response['error'] = 'Could not find extended cost to delete.';
				echo Zend_Json::encode($response);
				return;
			}
			$response['success'] = true;
		}
		catch (Exception $e) {
			$response['success'] = false;
			$response['error'] = $e->getMessage();
		}
		echo Zend_Json::encode($response);
		return;
	}
}