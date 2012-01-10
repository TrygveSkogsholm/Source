<?php

class VO_Purchase_ShipmentController extends Mage_Adminhtml_Controller_action
{
	protected function _initAction() {
		$this->loadLayout()
		->_setActiveMenu('purchase/shipments')
		->_addBreadcrumb(Mage::helper('adminhtml')->__('Purchase'), Mage::helper('adminhtml')->__('Shipments'));
		return $this;
	}

	//opening page, xml specifies block/shipments/grid as block.
	public function indexAction() {
		$this->_initAction()
		->renderLayout();
	}

	//Render the item select block (the block that allows you to select which items will be in a shipment) *block/shipments/itemselect.php*
	//It's a popup so normal block layout uneccesary, just toHtml.
	public function itemSelectAction()
	{
		$id = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('purchase/order')->load($id);
		Mage::register('order', $model);
		Mage::register('shipmentId', $this->getRequest()->getParam('shipment'));
		echo $this->getLayout()->createBlock('purchase/shipments_itemselect')->toHtml();
	}

	//Renders a similar popup to itemselect for selecting which order you want to ship.
	public function orderSelectAction()
	{
		Mage::register('shipmentId', $this->getRequest()->getParam('shipment'));
		echo $this->getLayout()->createBlock('purchase/shipments_orderselect')->toHtml();
	}

	//Edit a shipment
	public function editAction()
	{
		//Load Model
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('purchase/shipment')->load($id);
		if ($model->getId() || $id == 0)
		{
			//Register for internal block reference.
			Mage::register('shipment', $model);

			$this->loadLayout();
			$this->_setActiveMenu('purchase/shipments');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Purchase'), Mage::helper('adminhtml')->__('Shipment'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			//Add the form block with General Information fieldset
			$this->_addContent($this->getLayout()->createBlock('purchase/shipments_edit'));
			$this->_addContent($this->getLayout()->createBlock('purchase/shipments_popups'));

			//Add the items grid and PO list to the left
			if ($model->Status() == 1 || $model->getId() == NULL)
			{
				$this->_addContent($this->getLayout()->createBlock('purchase/shipments_items'))
				->_addLeft($this->getLayout()->createBlock('purchase/shipments_orders'));
			}
			else
			{$this->_addContent($this->getLayout()->createBlock('purchase/shipments_items'));}

			$this->renderLayout();
		}
		else
		{
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('purchase')->__('Shipment does not exist'));
			$this->_redirect('*/*/');
		}
	}

	/*
	 * Note even though this new action will forward to edit, there is code in the shipments/orders block
	 * that will force a user to select a supplier if one is not already specified before saving.
	 */
	public function newAction()
	{
		$this->_forward('edit');
	}

	//The item select popup calls this function when save is hit, it has a json param in GET mode.
	//It may be possible that sufficient information cannot be transmitted in which case someone needs to get
	//POST method to work for some reason magento controllers don't like it (it seems to me).
	public function AJAXUpdateItemsAction()
	{
		$updatedOrder =  Zend_Json::decode($this->getRequest()->getParam('json'));
		$order = Mage::getModel('purchase/order')->load($updatedOrder['id']);
		$shipmentId = $this->getRequest()->getParam('shipment');

		//Compare the JSON object to the saved data, update, create, or delete where needed.
		foreach ($updatedOrder['items'] as $item)
		{
			$model = Mage::getModel('purchase/order_product')->load($item['id']);
			$shipmentObject = $model->getSpecificShipmentObject($shipmentId);
			if ($item['localShipped'] > 0)
			{
				if ($shipmentObject->getId() == NULL)
				{
					//echo $model->getSku().' was not found and is created</br>';
					$order->shipItem($shipmentId,$item['id'],$item['localShipped']);
				}
				else
				{
					//echo $model->getSku().' was found and is now '.$item['localShipped'].'</br>';
					$shipmentObject->setItemQty($item['localShipped']);
					$shipmentObject->save();
				}
			}
			else
			{
				if ($shipmentObject->getId() != NULL)
				{
					$shipmentObject->delete();
				}
			}
			$model = NULL;
			$shipmentObject = NULL;
		}
		//close the window and refresh the parrent.
		echo '<script type="text/javascript">self.close(); window.opener.location.href = window.opener.location.href;</script>';
		return;
	}

	/*
	 * Should contain logic to update any shipment, supplier, PO products, supplier products neccesary.
	 *
	 */
	public function saveAction() {
		if ($data = $this->getRequest()->getPost()) {
			//if edoa is blank make sure it doesn't try to save anything.
			if (empty($data['edoa']))
			{
				$data['edoa'] = NULL;
			}

			//Grab the model
			$model = Mage::getModel('purchase/shipment')->load($this->getRequest()->getParam('id'));
			$model->setData($data)
			->setId($this->getRequest()->getParam('id'));

			try {
				//Created time
				if ($model->getdate_created() == NULL) {
					$model->setdate_created(now());
				}

				if ($model->dataHasChangedFor('freight_cost'))
				{
					$freight = $model->getFreight();
					$total = $model->getExtendedGrandtotal();
					$status = $model->getOrigData('status');
					foreach ($model->getItems() as $item)
					{
						$item->calculateLandedCost($freight,$total);
						$item->save();
						if ($status == 3)
						{
							$productAdditional = $model->loadAdditional($item->getProductId());
							$productAdditional->calculateAverageLandedCost();
						}
					}
				}

				$model->save();

				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('purchase')->__('Shipment was successfully saved'));
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
		Mage::getSingleton('adminhtml/session')->addError(Mage::helper('purchase')->__('Unable to find shipment order to save'));
		$this->_redirect('*/*/');
	}


	//Delete, don't forget childern!
	public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				$model = Mage::getModel('purchase/shipment');

				$model->setId($this->getRequest()->getParam('id'));


				$purchaseOrders = $model->getPurchaseOrders();
				foreach ($model->getItems() as $shipmentObject)
				{
					if (Mage::getStoreConfig('orders/deletion/absolute') == true && $shipmentObject->IsReceived() == true)
					{
						$stockItem = $shipmentObject->getMagentoStockItem();
						$stockItem->setQty($stockItem->getQty()- $shipmentObject->getItemQty());
						Mage::getModel('purchase/stockmovement')->addStockMovement($shipmentObject->getItemQty()*-1,$shipmentObject->getProductId(),'Canceled Shipment',$model->getId());
						$stockItem->save();
					}
					$shipmentObject->delete();
				}
				foreach ($purchaseOrders as $purchaseOrder)
				{
					$purchaseOrder->updateStatus();
				}
				$model->delete();

				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Shipment was successfully deleted'));
				$this->_redirect('*/*/');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}

	public function shipAction()
	{
		$shipment = Mage::getModel('purchase/shipment')->load($this->getRequest()->getParam('id'));
		$shipment->ship($this->getRequest()->getParam('date-shipped'),$this->getRequest()->getParam('estimated'));
		$this->_redirect('*/*/edit',array('id'=>$shipment->getId()));
	}

	public function receiveAction()
	{
		$shipment = Mage::getModel('purchase/shipment')->load($this->getRequest()->getParam('id'));
		$shipment->receive($this->getRequest()->getParam('date-received'));
		$this->_redirect('*/*/edit',array('id'=>$shipment->getId()));
	}

	public function exportCsvAction()
	{
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('purchase/shipment')->load($id);

		$fileName   = 'shipment_'.$model->getId().'.csv';
		$headers = array('Order','Model','Sku','Name','HTS','First Cost','Rate','Qty','Total');
		$data = array();

		/*
		 * In this condensed form every hts code equals a row.
		 * Iterate through items and place them based on HTS in the proper row.
		 */
		foreach ($model->getItems() as $item)
		{
				$data[] = array
				(
					$item->getOrder()->getId(),
					$item->getOrderProduct()->getModelString(),
					$item->getSku(),
					$item->getName(),
					$item->getOrderProduct()->getHtsCode(),
					$item->getOrderProduct()->getFirstCost(),
					Mage::helper('purchase')->formatPercentage($item->getOrderProduct()->getDutyRate()),
					$item->getItemQty(),
					$item->getSubtotal()
				);
		}
		$CSV = Mage::getModel('utility/csv');
		$CSV->initialize($data, $headers);
		$this->_prepareDownloadResponse($fileName, $CSV->getContent(), $CSV->getContentType());
	}

	public function exporthtsCsvAction()
	{
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('purchase/shipment')->load($id);

		$fileName   = 'shipment_'.$model->getId().'_condensed_hts.csv';
		$headers = array('HTS','Rate','Total');
		$data = array();

		/*
		 * In this condensed form every hts code equals a row.
		 * Iterate through items and place them based on HTS in the proper row.
		 */
		foreach ($model->getItems() as $item)
		{
			if (isset($totals[$item->getOrderProduct()->getHtsCode()]))
			{
				$totals[$item->getOrderProduct()->getHtsCode()] += $item->getSubtotal();
			}
			else
			{
				$totals[$item->getOrderProduct()->getHtsCode()] = $item->getSubtotal();
			}
		}

		foreach ($totals as $hts => $total)
		{
			$htsModel = Mage::getModel('purchase/hts')->load($hts);
			$data[] = array($hts,Mage::helper('purchase')->formatPercentage($htsModel->getDutyRate()),$total);
		}

		$CSV = Mage::getModel('utility/csv');
		$CSV->initialize($data, $headers);
		$this->_prepareDownloadResponse($fileName, $CSV->getContent(), $CSV->getContentType());
	}
}