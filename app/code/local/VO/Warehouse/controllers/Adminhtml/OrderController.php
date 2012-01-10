<?php

class VO_Warehouse_Adminhtml_OrderController extends Mage_Adminhtml_Controller_action
{
	protected function _initAction() {
		$this->loadLayout()
		->_setActiveMenu('warehouse/orders')
		->_addBreadcrumb(Mage::helper('adminhtml')->__('Warehouse Order Manager'), Mage::helper('adminhtml')->__('Item Manager'));
		return $this;
	}

	public function indexAction() {
		$params = $this->getRequest()->getParams();
		if (isset($params['range']))
		{
			Mage::register('loaded_range', Mage::getModel('warehouse/range')->load($params['range']));
		}
		$this->_initAction()
		->renderLayout();
	}

	private $groupsToInvoice;

	private function getInvoiceCustomerGroups()
	{
		if (!isset($this->groupsToInvoice))
		{
			$this->groupsToInvoice = explode (',', Mage::getStoreConfig('warehouse_orders/print_options/invoices'));
		}
		return $this->groupsToInvoice;
	}

	private function getPickingslipPDF($orderGroup,$range,$combined = false)
	{
		if ($combined == false)
		{
			$id = key($orderGroup);
			$orderGroup = reset($orderGroup);

			//Load Print Object
			$print = Mage::getModel('warehouse/print')->load($id);

			//Assign print object to range
			$print->assignToRange($range);

			//Note date printed and mark as printed
			$print->markAsPrinted();
			$print->save();

			//get order
			$order = $print->getOrder();

			//Create PDF
			$pages = Mage::getModel('warehouse/pdf_PickingList')->getPdf($order);

			//This refers to customer group
			if (in_array($orderGroup['group'], $this->getInvoiceCustomerGroups()))
			{
				try
				{
					$invoiceCollection = $order->getInvoiceCollection();
					if (!empty($invoiceCollection) && $invoiceCollection->getSize() > 0)
					{
						$invoicePage = Mage::getModel('sales/order_pdf_invoice')->getPdf($invoiceCollection);
						$pages->pages = array_merge ($pages->pages, $invoicePage->pages);
					}
				}
				catch (Exception $e)
				{
					Mage::getSingleton('adminhtml/session')->addError($orderGroup['increment'].' could not load invoices.');
				}
			}
		}
		else
		{
			$invoicePages = array();
			foreach ($orderGroup as $id => $individualOrder)
			{
				//Load Print Object
				$print = Mage::getModel('warehouse/print')->load($id);
				//Assign print object to range
				$print->assignToRange($range);
				//Note date printed and mark as printed
				$print->markAsPrinted();
				$print->save();
					
				//get order
				$order = $print->getOrder();
				$orders[] = $order;
				//This refers to customer group
				if (in_array($individualOrder['group'], $this->getInvoiceCustomerGroups()))
				{
					try
					{
						$invoiceCollection = $order->getInvoiceCollection();
						if (!empty($invoiceCollection) && $invoiceCollection->getSize() > 0)
						{
							$invoicePage = Mage::getModel('sales/order_pdf_invoice')->getPdf($invoiceCollection);
							$invoicePages[] = $invoicePage;
						}
					}
					catch (Exception $e)
					{
						Mage::getSingleton('adminhtml/session')->addError($individualOrder['increment'].' could not load invoices.');
					}
				}
			}
			$pages = Mage::getModel('warehouse/pdf_CombinedPickingList')->getPdf($orders);
			if (!empty($invoicePages))
			{
				foreach ($invoicePages as $invoicePage)
				{
					$pages->pages = array_merge($pages->pages, $invoicePage->pages);
				}
			}
		}
		return $pages;
	}

	public function printOrdersAction()
	{
		$rangeParameter = $this->getRequest()->getParam('range');
		$ranges = Mage::getSingleton('core/session')->getData('ranges_to_print');
		ini_set('memory_limit', '1024M');
		//Add new notes and exclusions

		foreach ($ranges as $range)
		{
			if ($rangeParameter == 'all' || $rangeParameter == $range->getId())
			{
				foreach ($range->getOrders() as $addressString => $orderGroup)
				{
					if (count($orderGroup) > 1)
					{
						$pages = $this->getPickingslipPDF($orderGroup,$range->getId(),true);
					}
					else
					{
						$pages = $this->getPickingslipPDF($orderGroup,$range->getId());
					}
					if (!isset($pdf)){
						$pdf = $pages;
					} else {
						$newPages = $pages;
						$pdf->pages = array_merge($pdf->pages, $newPages->pages);
					}
				}
				//save notes
				foreach ($range->notes as $note)
				{
					$note->save();
				}
				//Close range and start new one
				$range->closeRange();
			}
		}
		if (isset($pdf))
		{
			return $this->_prepareDownloadResponse('Picking Slip '.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf', $pdf->render(), 'application/pdf');
		}
		else
		{
			Mage::getSingleton('adminhtml/session')->addError('No orders to print.');
			$this->_redirect('*/*/');
		}
	}

	public function printInvoicesAction()
	{
		$rangeParameter = $this->getRequest()->getParam('range');
		$ranges = Mage::getSingleton('core/session')->getData('ranges_to_print');
		foreach ($ranges as $range)
		{
			if ($rangeParameter == $range->getId())
			{
				foreach ($range->getOrders() as $orderGroup)
				{
					foreach ($orderGroup as $id => $orderData)
					{
						$order = Mage::getModel('sales/order')->load($id);
						try
						{
							$invoiceCollection = $order->getInvoiceCollection();
							if (!empty($invoiceCollection) && $invoiceCollection->getSize() > 0)
							{
								if (!isset($pdf)){
									$pdf = Mage::getModel('sales/order_pdf_invoice')->getPdf($invoiceCollection);
								} else {
									$pages = Mage::getModel('sales/order_pdf_invoice')->getPdf($invoiceCollection);
									$pdf->pages = array_merge ($pdf->pages, $pages->pages);
								}
							}
						}
						catch (Exception $e)
						{
							Mage::getSingleton('adminhtml/session')->addError($orderData['increment'].' could not load invoices.');
						}
					}
				}

			}
		}
		if (isset($pdf))
		{
			return $this->_prepareDownloadResponse('Picking Slip '.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf', $pdf->render(), 'application/pdf');
		}
		else
		{
			Mage::getSingleton('adminhtml/session')->addError('No orders to print.');
			$this->_redirect('*/*/');
		}
	}

	//Pretty much everything after this point will have to be removed in time
	public function AJAXgeneratePdfAction()
	{
		//get the orders, the id's are seperated by comma
		$orderIds = split(',', $this->getRequest()->getParam('orders'));
		try {
			//Grab the pdf from the session
			if (Mage::getSingleton('core/session')->getPickingSlips())
			{$pdf = unserialize(Mage::getSingleton('core/session')->getPickingSlips());}

			if (count($orderIds) > 1)
			{
				//This block is called if there are multiple orders in the chunk
				$orders = array();
				foreach ($orderIds as $id)
				{
					$orders[] = Mage::getModel('sales/order')->load($id);
				}

				if (!isset($pdf)){
					$pdf = Mage::getModel('warehouse/pdf_CombinedPickingList')->getPdf($orders);
				} else {
					$pages = Mage::getModel('warehouse/pdf_CombinedPickingList')->getPdf($orders);
					$pdf->pages = array_merge ($pdf->pages, $pages->pages);
				}

				//Grap the group ID (for invoices)
				$GroupID = Mage::getModel('customer/customer')->load($orders[0]->getCustomerId())->getGroupId();

				if ($GroupID == 2 || $GroupID == 4)
				{
					foreach ($orders as $order)
					{
						//echo 'THIS PRINTED -> '.$order->getRealOrderId().'</br>';
						$invoiceCollection = $order->getInvoiceCollection();
						if (isset($invoiceCollection))
						{$invoicePage = Mage::getModel('sales/order_pdf_invoice')->getPdf();}
						$pdf->pages = array_merge ($pdf->pages, $invoicePage->pages);
					}
				}


			}
			else
			{
				//This block is called if the order is singular, so use the singular pdf.
				$order = Mage::getModel('sales/order')->load(reset($orderIds));
				//Grap the group ID (for invoices)
				$GroupID = Mage::getModel('customer/customer')->load($order->getCustomerId())->getGroupId();

				//ZEE INVOICE!
				if ($GroupID == 2 || $GroupID == 4)
				{
					$invoicePage = Mage::getModel('sales/order_pdf_invoice')->getPdf($order->getInvoiceCollection());
					if (!isset($pdf)){
						$pdf = Mage::getModel('warehouse/pdf_PickingList')->getPdf($order);
						$pdf->pages = array_merge ($pdf->pages, $invoicePage->pages);
					} else {
						$pages = Mage::getModel('warehouse/pdf_pickingList')->getPdf($order);
						$pdf->pages = array_merge ($pdf->pages, $pages->pages);
						$pdf->pages = array_merge ($pdf->pages, $invoicePage->pages);
					}
				}
				else
				{
					if (!isset($pdf)){
						$pdf = Mage::getModel('warehouse/pdf_PickingList')->getPdf($order);
					} else {
						$pages = Mage::getModel('warehouse/pdf_pickingList')->getPdf($order);
						$pdf->pages = array_merge ($pdf->pages, $pages->pages);
					}
				}
			}
			Mage::getSingleton('core/session')->setPickingSlips(serialize($pdf));
			echo 'success';
		} catch (Exception $e) {
			echo $e;
		}
	}

	public function downloadPickingPdfAction()
	{
		echo 'hello';
		$pdf = unserialize(Mage::getSingleton('core/session')->getPickingSlips());
		Mage::getSingleton('core/session')->setPickingSlips(NULL);
		return $this->_prepareDownloadResponse('Picking Slip '.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf', $pdf->render(), 'application/pdf');
	}


	//Obsolete functions:
	//======================================================================
	public function pdfPickingListAction()
	{
		//See if we can avoid timeout.
		set_time_limit(300);

		try{
			//Retrieve orders to print from the orders object.
			try {
				$Model = Mage::getModel('warehouse/orders');
				$orders = $Model->GetOrdersToPrint();
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError('Cannot Retrieve Orders to Print!');
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}

			/*
			 *The following code should be capable of catching double or triple orders and combining them.
			 *
			 *It will do it in this way (although a bit resource intensive it should be nothing compared to me first attempt)
			 *
			 * 1. Iterate through the whole list creating an array : [(string)'increment_id'=>addressId,]
			 *
			 * 2. Identify with a second foreach which ones are duplicate and assign them to combined or single order arrays
			 *
			 * 3. print them out.
			 */

			try {
				$duplicateArrayCheck = array();
				foreach ($orders as $order)
				{
					try {
						if ($address = $order->getShippingAddress())
						{
							$duplicateArrayCheck[$order->getRealOrderId()] = $address->getregion().$address->getStreetFull().$address->getcity();
						}
					} catch (Exception $e) {
						Mage::getSingleton('adminhtml/session')->addError($order->getRealOrderId().' does not have a shipping address');
						Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
					}

					$address = NULL;
				}

				$singleOrders = array();
				$combinedOrders = array();

				//This variable represents each address that exists in this list only once.
				$uniqueAddresses = array_unique($duplicateArrayCheck);

				foreach ($uniqueAddresses as $firstKey => $uniqueAddress)
				{
					//$v is an array of key value pairs for each unique address
					$v = array_keys($duplicateArrayCheck, $uniqueAddress);

					//Therefore if it's 1 that means the address is only used once, if it's two or more it's used that many times.
					//So if it is used multiple times throw those all into an array of individual orders that must be combined, then
					//add that array to our list of all combinations ($combinedOrders)
					if (count($v) > 1)
					{
						foreach ($v as $orderNum)
						{
							$ordersWhichShouldBeCombined[] = Mage::getModel('sales/order')->loadByIncrementId($orderNum);
						}
						$combinedOrders[] = $ordersWhichShouldBeCombined;
						$ordersWhichShouldBeCombined = NULL;
					}
					else
					{
						//There is only one so we know we can just use the unique increment ID in the unique array in the first
						//foreach
						$singleOrders[] = Mage::getModel('sales/order')->loadByIncrementId($firstKey);
					}
				}

			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError('Cannot combine orders');
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}


			//To make sure print outs don't stack
			$pdf = NULL;

			//Print Singles
			foreach ($singleOrders as $order)
			{
				try {
					//Grap the group ID (for invoices)
					$GroupID = Mage::getModel('customer/customer')->load($order->getCustomerId())->getGroupId();

					//Try to get the invoice
					if ($GroupID == 2 || $GroupID == 4)
					{
						$invoicePage = Mage::getModel('sales/order_pdf_invoice')->getPdf($order->getInvoiceCollection());
						if (!isset($pdf)){
							$pdf = Mage::getModel('warehouse/pdf_PickingList')->getPdf($order);
							$pdf->pages = array_merge ($pdf->pages, $invoicePage->pages);
						} else {
							$pages = Mage::getModel('warehouse/pdf_pickingList')->getPdf($order);
							$pdf->pages = array_merge ($pdf->pages, $pages->pages);
							$pdf->pages = array_merge ($pdf->pages, $invoicePage->pages);
						}
					}
					else
					{
						if (!isset($pdf)){
							$pdf = Mage::getModel('warehouse/pdf_PickingList')->getPdf($order);
						} else {
							$pages = Mage::getModel('warehouse/pdf_pickingList')->getPdf($order);
							$pdf->pages = array_merge ($pdf->pages, $pages->pages);
						}
					}
				} catch (Exception $e) {
					Mage::getSingleton('adminhtml/session')->addError('Cannot Print Order #'.$order->getRealOrderId());
					Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				}

			}

			//Print Combined
			foreach ($combinedOrders as $orders)
			{
				try {
					//Grap the group ID (for invoices)
					$GroupID = Mage::getModel('customer/customer')->load($orders[0]->getCustomerId())->getGroupId();
					//Try to get the invoice
					if ($GroupID == 2 || $GroupID == 4)
					{
						$invoicePages = array();
						foreach ($orders as $order)
						{
							try {
								$invoicePages[] = Mage::getModel('sales/order_pdf_invoice')->getPdf($order->getInvoiceCollection());
							} catch (Exception $e) {
								Mage::getSingleton('adminhtml/session')->addError('Cannot Print Invoice for Order #'.$order->getRealOrderId());
								Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
								$order->setBiebersdorfCustomerordercomment('This order should have had an invoice but would not print '.$order->getBiebersdorfCustomerordercomment());
							}
						}

						if (count($invoicePages) <= 1)
						{
							Mage::getSingleton('adminhtml/session')->addError('Cannot Print Invoices for Combined Orders');
						}

						if (!isset($pdf)){
							$pdf = Mage::getModel('warehouse/pdf_CombinedPickingList')->getPdf($orders);
							foreach ($invoicePages as $invoicePage)
							{$pdf->pages = array_merge ($pdf->pages, $invoicePage->pages);}
						} else {
							$pages = Mage::getModel('warehouse/pdf_CombinedPickingList')->getPdf($orders);
							$pdf->pages = array_merge ($pdf->pages, $pages->pages);
							foreach ($invoicePages as $invoicePage)
							{$pdf->pages = array_merge ($pdf->pages, $invoicePage->pages);}
						}
					}
					else
					{
						if (!isset($pdf)){
							$pdf = Mage::getModel('warehouse/pdf_CombinedPickingList')->getPdf($orders);
						} else {
							$pages = Mage::getModel('warehouse/pdf_CombinedPickingList')->getPdf($orders);
							$pdf->pages = array_merge ($pdf->pages, $pages->pages);
						}
					}

				} catch (Exception $e) {
					$string = '';
					foreach ($orders as $order)
					{
						$string .= $order->getRealOrderId().' ';
					}
					Mage::getSingleton('adminhtml/session')->addError('Cannot Print Combined Orders '.$string);
					Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				}
			}

			try {
				//$Model->ConfirmPrintStatus($orders);
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError('Cannot mark orders as printed.');
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
			try {
				$Model->CreateRange($orders);
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError('Cannot create order range.');
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}

			return $this->_prepareDownloadResponse('Picking Slip '.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf', $pdf->render(), 'application/pdf');
		}
		catch (Exception $e)
		{
			$this->_redirect('*/*/');
		}
	}

	//======================================================================
	public function UndoPrintAction(){
		Mage::getModel('warehouse/orders')->UndoPrint($this->getRequest()->getParam('voStart')
		,$this->getRequest()->getParam('voStop'),$this->getRequest()->getParam('voiStart')
		,$this->getRequest()->getParam('voiStop'));
		$this->_redirect('*/*/');

	}
	//======================================================================


	///======== XML FUNCTIONS, eventualy move to own module
	///======== XML FUNCTIONS, eventualy move to own module
	public function writeTag($tagName,$attributes = array(),$value,$large)
	{
		$XML = '';

		//Attributes string
		$string = '';
		if ($attributes != null)
		{
			foreach ($attributes as $type => $attribute)
			{
				$string .= ' '.$type.'="'.$attribute.'"';
			}
		}

		//write that tag
		if (!empty($value))
		{
			if ($large == true)
			{
				$XML .= '<'.$tagName.$string.'>';
				$XML .= $value;
				$XML .= '</'.$tagName.'>';
			}
			else
			{
				$XML .= '<'.$tagName.$string.'>'.$value.'</'.$tagName.'>';
			}
		}
		else
		{
			$XML .= '<'.$tagName.'/>';
		}
		return $XML;
	}

	//$this->writeTag($tagName,$attributes = array(),$value,$large)

	public function downloadXMLAction()
	{
		$orders = '';

		foreach (Mage::getModel('warehouse/orders')->GetOrdersToPrint() as $order)
		{
			$orderContent = '';
			$orderContent .= $this->writeTag('Time',null,Mage::helper('core')->formatDate($order->getCreatedAtStoreDate(), 'medium', false),false);
			//$orderContent .= $this->writeTag('NumericTime',null,$order->getCreatedAtStoreDate(),false);

			//ship
			$addressInfo = '';

			$Address = $order->getShippingAddress();
			$nameInfo = '';
			$nameInfo .= $this->writeTag('First',null,$Address->getFirstname(),false);
			$nameInfo .= $this->writeTag('Last',null,$Address->getLastname(),false);
			$nameInfo .= $this->writeTag('Full',null,$Address->getName(),false);

			$addressInfo .= $this->writeTag('Name',null,$nameInfo,true);
			$addressInfo .= $this->writeTag('Company',null,$Address->getCompany(),false);
			$addressInfo .= $this->writeTag('Address1',null,$Address->getStreet1(),false);
			$addressInfo .= $this->writeTag('Address2',null,$Address->getStreet2(),false);
			$addressInfo .= $this->writeTag('City',null,$Address->getCity(),false);
			$addressInfo .= $this->writeTag('State',null,$Address->getRegion(),false);
			$addressInfo .= $this->writeTag('Country',null,$Address->getCountry(),false);
			$addressInfo .= $this->writeTag('Zip',null,$Address->getPostcode(),false);
			$addressInfo .= $this->writeTag('Phone',null,$Address->getTelephone(),false);
			$addressInfo .= $this->writeTag('Email',null,$order->getCustomerEmail(),false);

			$orderContent .= $this->writeTag('AddressInfo',array('type'=>'ship'),$addressInfo,true);

			//Bill
			$addressInfo = '';

			$Address = $order->getBillingAddress();
			$nameInfo = '';
			$nameInfo .= $this->writeTag('First',null,$Address->getFirstname(),false);
			$nameInfo .= $this->writeTag('Last',null,$Address->getLastname(),false);
			$nameInfo .= $this->writeTag('Full',null,$Address->getName(),false);

			$addressInfo .= $this->writeTag('Name',null,$nameInfo,true);
			$addressInfo .= $this->writeTag('Company',null,$Address->getCompany(),false);
			$addressInfo .= $this->writeTag('Address1',null,$Address->getStreet1(),false);
			$addressInfo .= $this->writeTag('Address2',null,$Address->getStreet2(),false);
			$addressInfo .= $this->writeTag('City',null,$Address->getCity(),false);
			$addressInfo .= $this->writeTag('State',null,$Address->getRegion(),false);
			$addressInfo .= $this->writeTag('Country',null,$Address->getCountry(),false);
			$addressInfo .= $this->writeTag('Zip',null,$Address->getPostcode(),false);
			$addressInfo .= $this->writeTag('Phone',null,$Address->getTelephone(),false);
			$addressInfo .= $this->writeTag('Email',null,$order->getCustomerEmail(),false);

			$orderContent .= $this->writeTag('AddressInfo',array('type'=>'bill'),$addressInfo,true);

			$orderContent .= $this->writeTag('Comments',null,$order->getBiebersdorfCustomerordercomment(),true);

			$items = $order->getAllItems();

			foreach ($items as $key => $item)
			{
				$productId = Mage::getModel('catalog/product')->getIdBySku($item->getSku());
				$product = Mage::getModel('catalog/product')->load($productId);

				$itemContent = '';
				if ($product->isSuper() == false)
				{
					$itemContent .= $this->writeTag('Code',null,$item->getSku(),false);
					$itemContent .= $this->writeTag('Quantity',null,$item->getQtyOrdered(),false);
					$itemContent .= $this->writeTag('Unit-Price',null,$item->getPrice(),false);
					$itemContent .= $this->writeTag('Description',null,$item->getName(),false);

					$orderContent .= $this->writeTag('Item',array('num'=>$key),$itemContent,true);
				}
			}

			$totals = '';
			$totals .= $this->writeTag('Line',array('type'=>'Subtotal','name'=>'Subtotal'),$order->getSubtotal(),false);
			$totals .= $this->writeTag('Line',array('type'=>'Shipping','name'=>'Shipping'),$order->getShippingAmount(),false);
			$totals .= $this->writeTag('Line',array('type'=>'Total','name'=>'Total'),$order->getShippingAmount()+$order->getSubtotal(),false);

			$orderContent .= $this->writeTag('Total',null,$totals,true);

			$order = $this->writeTag('Order',array('currency'=>'USD','id'=>$order->getRealOrderId()),$orderContent,true);
			$orders .= $order;
		}

		$XML = $this->writeTag('OrderList',null,$orders,true);

		$this->_sendUploadResponse('orders.xml', $XML);
	}

	public function downloadCSVAction()
	{
		$CSV = '"contact","company","address1","address2","city","state","postalcode","itemtotal","orderid","trackingnumber"'."\r\n";

		foreach (Mage::getModel('warehouse/orders')->GetOrdersToPrint() as $order)
		{
			try
			{
				$Address = $order->getShippingAddress();

				$CSV .= '"'.$Address->getName().'","'.$Address->getCompany().'","'.$Address->getStreet1().'","'.$Address->getStreet2().'","'.$Address->getCity().'","'.$Address->getRegion().'","'.$Address->getPostcode().'",'.($order->getShippingAmount()+$order->getSubtotal()).','.$order->getRealOrderId().','."\r\n";
			}
			catch (Exception $e)
			{

			}
		}

		$this->_sendUploadResponse('ShipRush.csv', $CSV);
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