<?php

class VO_Purchase_Model_Pdf_Order extends VO_Purchase_Model_Pdf_Pdfhelper
{
	public function getPdf($order = array())
	{
		//Some kinda translation function
		$this->_beforeGetPdf();

		//No idea what this does, it's in pdf abstract
		$this->_initRenderer('shipment');

		//creating an instance of a pdf
		$pdf = new Zend_Pdf();

		//Connecting this with the new pdf object (I think)
		$this->_setPdf($pdf);

		//Creating a style, I think it works similar to css styles
		$style = new Zend_Pdf_Style();

		//This is important, creating a page which is not the same as a PDF
		$page = $pdf->newPage(Zend_Pdf_Page::SIZE_LETTER);

		//Add this page to the pages[] array in the pdf object!
		$pdf->pages[] = $page;

		$this->y = 792;
		$this->x = 0;

		// Rainbow!
		$white = new Zend_Pdf_Color_GrayScale(1);
		$black = new Zend_Pdf_Color_GrayScale(0);
		$lightGrey = new Zend_Pdf_Color_GrayScale(0.8);
		$grey = new Zend_Pdf_Color_GrayScale(0.5);
		$darkGrey = new Zend_Pdf_Color_GrayScale(0.2);
		$blue = new Zend_Pdf_Color_Rgb(0.5, 0.5, 1);
		$page->setFillColor($black);
		$this->_setFontRegular($page, 25);
		$page->setLineColor($lightGrey);

		//@todo fix formatting
		//@todo Company name to config
		$page->drawText('Velo Orange', $this->x+35, $this->y-50, 'UTF-8');

		$this->_setFontRegular($page, 12);
		$page->drawText(Mage::getStoreConfig('orders/shipping_address/street1').'                       '.Mage::getStoreConfig('orders/shipping_address/city').' MD, '.Mage::getStoreConfig('orders/shipping_address/zip'), $this->x+230, $this->y-39, 'UTF-8');
		$page->drawText(Mage::getStoreConfig('orders/printcontact/phone'), $this->x+430, $this->y-56, 'UTF-8');
		$page->drawText(Mage::getStoreConfig('orders/printcontact/email'), $this->x+230, $this->y-56, 'UTF-8');

		$page->drawLine($this->x+200, $this->y-43, $this->x+550, $this->y-43);

		$page->setFillColor($white);
		$this->_setFontRegular($page, 20);
		$this->y -= 35;

		$page->drawRectangle($this->x+25, $this->y-30, $this->x+306, $this->y-60);
		$page->drawRectangle($this->x+306, $this->y-30, $this->x+587, $this->y-60);
		$page->drawRectangle($this->x+25, $this->y-60, $this->x+306, $this->y-170);
		$page->drawRectangle($this->x+306, $this->y-60, $this->x+587, $this->y-170);

		$itemsTopHeight = $this->y-170;

		$page->setFillColor($black);
		$page->drawText('Purchase Order #'.$order->getId(), $this->x+35, $this->y-53, 'UTF-8');
		$page->drawText(date("F j, Y"), $this->x+360, $this->y-53, 'UTF-8');

		$this->_setFontRegular($page, 12);
		$this->y -= 75;
		$this->x += 35;

		//START ADDRESSES

		//TO LABEL
		$page->drawText('Ship To', $this->x, $this->y, 'UTF-8');
		$this->y -= 20;

		$toAddress = $order->getAddress('to');

		$this->scaleFontSizeToFit($page,$this->x,$this->y,$toAddress['name'].' ATTN: '.$toAddress['contact'],265);
		$this->y -= 16;

		$page->drawText($toAddress['street1'], $this->x, $this->y, 'UTF-8');
		$this->y -= 16;

		if (!empty($toAddress['street2']))
		{
			$page->drawText($toAddress['street2'], $this->x, $this->y, 'UTF-8');
		}
		$this->y -= 16;

		$page->drawText($toAddress['city'].' '.Mage::getModel('directory/region')->load($toAddress['state'])->getName().', '.$toAddress['zip'], $this->x, $this->y, 'UTF-8');
		$this->y -= 16;

		$page->drawText($toAddress['country'], $this->x, $this->y, 'UTF-8');
		$this->y +=80;

		//FROM LABEL
		$this->x += 281;
		$page->drawText('Vendor:', $this->x, $this->y, 'UTF-8');
		$this->y -= 16;

		$fromAddress = $order->getAddress('from');

		$this->scaleFontSizeToFit($page,$this->x,$this->y,$fromAddress['name'].' ATTN: '.$fromAddress['contact'],265);
		$this->y -= 16;

		$page->drawText($fromAddress['street1'], $this->x, $this->y, 'UTF-8');
		$this->y -= 16;

		if (!empty($fromAddress['street2']))
		{
			$page->drawText($fromAddress['street2'], $this->x, $this->y, 'UTF-8');
		}
		$this->y -= 16;

		$page->drawText($fromAddress['city'].' '.Mage::getModel('directory/region')->load($fromAddress['state'])->getName().', '.$fromAddress['zip'], $this->x, $this->y, 'UTF-8');
		$this->y -= 16;

		$page->drawText($fromAddress['country'], $this->x, $this->y, 'UTF-8');

		$this->y -= 28;

		$this->x -= 281;

		//END ADDRESSES

		//ITEMS
		//@todo vo# sku to config
		//COLUMN HEADERS
		$this->_setFontBold($page, 14);
		$page->drawText('VO#', $this->x, $this->y, 'UTF-8');
		$page->drawText('Model#', $this->x+90, $this->y, 'UTF-8');
		$page->drawText('Name', $this->x+200, $this->y, 'UTF-8');
		$page->drawText('Qty', $this->x+390, $this->y, 'UTF-8');
		$page->drawText('Price', $this->x+430, $this->y, 'UTF-8');
		$page->drawText('Ext.', $this->x+500, $this->y, 'UTF-8');
		$this->_setFontRegular($page, 12);
		$this->y -= 10;

		foreach ($order->getItems() as $item)
		{
			$page->setFillColor($white);
			$page->drawRectangle($this->x-10, $this->y, $this->x+552, $this->y-34);
			$page->setFillColor($black);
			$this->y -= 14;
			//Sku
			$this->scaleFontSizeToFit($page,$this->x,$this->y,$item->getSku(),75);

			//model
			$this->scaleFontSizeToFit($page,$this->x+90,$this->y,$item->getModelString(),100);

			//name
			$this->scaleFontSizeToFit($page,$this->x+200,$this->y,$item->getName(),175);


			$this->_setFontRegular($page, 8);
			//hts (small)
			$hts = $item->getHtsCode();
			if ($order->isDuty() == true && empty($hts) == false)
			{$page->drawText('HTS: '.$hts, $this->x+120, $this->y-16, 'UTF-8');}

			//upc (small)
			$upc = $item->getUpcCode();
			//@todo add config control over whether the upc if displayed or not.
			if (true == true && empty($upc) == false)
			{$page->drawText('UPC: '.$upc, $this->x+240, $this->y-16, 'UTF-8');}

			$this->_setFontRegular($page, 12);
			$this->y -= 9;
			//qty
			$this->drawTextInBlock($page,$item->getItemQty(), $this->x+386, $this->y, 30, 30);

			//price
			$this->drawTextInBlock($page, Mage::helper('core')->currency($item->getFirstCost()), $this->x+503, $this->y, 60, 30);

			//total
			$this->drawTextInBlock($page, Mage::helper('core')->currency($item->getSubtotal()), $this->x+569, $this->y, 60, 30);

			$this->y -= 11;
			if ($this->y < 60) {
				$page->drawLine($this->x+80, $itemsTopHeight, $this->x+80, $this->y);
				//$page->drawLine($this->x+190, $itemsTopHeight, $this->x+190, $this->y);
				$page->drawLine($this->x+380, $itemsTopHeight, $this->x+380, $this->y);
				$page->drawLine($this->x+420, $itemsTopHeight, $this->x+420, $this->y);
				$page->drawLine($this->x+490, $itemsTopHeight, $this->x+490, $this->y);
				$itemsTopHeight = 750;
				$page = $this->newPickingPage();
			}

		}

		$page->drawLine($this->x+80, $itemsTopHeight, $this->x+80, $this->y);
		//$page->drawLine($this->x+190, $itemsTopHeight, $this->x+190, $this->y);
		$page->drawLine($this->x+380, $itemsTopHeight, $this->x+380, $this->y);
		$page->drawLine($this->x+420, $itemsTopHeight, $this->x+420, $this->y);
		$page->drawLine($this->x+490, $itemsTopHeight, $this->x+490, $this->y);

		//END ITEMS

		$this->x = 0;
		$page->setFillColor($white);
		$page->drawRectangle($this->x+25, $this->y, $this->x+250, $this->y-30);
		$page->drawRectangle($this->x+250, $this->y, $this->x+450, $this->y-30);
		$page->drawRectangle($this->x+450, $this->y, $this->x+587, $this->y-30);

		$page->setFillColor($black);
		//payment method
		$this->drawTextInBlock($page,'Payment via:   '.$order->getPaymentMethod(), $this->x+25, $this->y-20, 220, 30,'c');

		//Duty
		if ($order->isDuty() == true){
			$this->drawTextInBlock($page,'Estimated Duty:   '.Mage::helper('core')->currency($order->getTotalDuty(),true, false), $this->x+245, $this->y-20, 200, 30,'r');
		}

		//Total
		$this->drawTextInBlock($page,'Total:   '.Mage::helper('core')->currency($order->getSubtotal(),true, false), $this->x+450, $this->y-20, 130, 30,'r');

		$this->y -= 40;

		//Comments
		if ($order->showComment == FALSE)
		{
		}
		else
		{
			$comment = $order->getOrderComments();
			$this->_setFontRegular($page, 10);
			//$comment = $this->WrapTextToWidth($page, $comment, 562);
			$this->DrawMultilineText($page, $comment, $this->x + 25 , $this->y, 8, 0.2, 9,true,$this);
		}

		return $pdf;
	}

	public function newPickingPage()
	{
		/* Add new table head */
		$page = $this->_getPdf()->newPage(Zend_Pdf_Page::SIZE_LETTER);
		$this->_getPdf()->pages[] = $page;
		$this->y = 792;
		$this->x = 0;
		$this->y -= 55;
		$this->x += 35;
		$page->setLineColor(new Zend_Pdf_Color_GrayScale(0.8));

		$this->_setFontBold($page, 14);
		$page->drawText('VO#', $this->x, $this->y, 'UTF-8');
		$page->drawText('Model#', $this->x+90, $this->y, 'UTF-8');
		$page->drawText('Name', $this->x+200, $this->y, 'UTF-8');
		$page->drawText('Qty', $this->x+390, $this->y, 'UTF-8');
		$page->drawText('Price', $this->x+430, $this->y, 'UTF-8');
		$page->drawText('Ext.', $this->x+500, $this->y, 'UTF-8');
		$this->_setFontRegular($page, 12);
		$this->y -= 10;
		return $page;
	}

	public function newPageNoHeader()
	{
		/* Add new table head */
		$page = $this->_getPdf()->newPage(Zend_Pdf_Page::SIZE_LETTER);
		$this->_getPdf()->pages[] = $page;
		$this->y = 792;
		$this->x = 0;
		$this->y -= 55;
		$this->x += 35;
		$page->setLineColor(new Zend_Pdf_Color_GrayScale(0.8));
		$this->_setFontRegular($page, 8);
		return $page;
	}
}