<?php
/**
 Hey anyone who sees this stuff, Trygve.... Trygve of Velo Orange here.

 I am creating this picking list from scratch because the module we bought doesn't seem to be
 easily modified to make what we need.

 In OrderController.php I created a picking list action that should call this with an order array
 object as the argument.

 in Grid.php over in adminhtml I added the mass action for this.

 I intend to also create a special page to do this easily.

 This is going to be heavy on the comments because I am a newbe with this stuff.
 */
class VO_Warehouse_Model_Pdf_CombinedPickingList extends VO_Warehouse_Model_Pdf_Pdfhelper
{

	public function getPdf($orders = array())
	{
		$defaultOrder = current($orders);
		//Some kinda translation function
		$this->_beforeGetPdf();

		//No idea what this does, it's in pdf abstract
		$this->_initRenderer('shipment');

		//creating an instance of a pdf
		$pdf = new Zend_Pdf();

		//Connecting this with the new pdf object (I think)
		$this->_setPdf($pdf);

		//Creatomg a style, I think it works similar to css styles
		$style = new Zend_Pdf_Style();

		//This is important, creating a page which is not the same as a PDF
		$page = $pdf->newPage(Zend_Pdf_Page::SIZE_A4);

		//Add this page to the pages[] array in the pdf object!
		$pdf->pages[] = $page;

		//Don't forget this, $y is not defined in abstract so it must be defined
		//before you use it. It 0 is bottom of the page (yea what the hec)
		// 800 is the top (including page margins ect 840 is absolute top). oh and 600 wide
		$this->y = 790;

		// some nice color cpu saving
		$white = new Zend_Pdf_Color_GrayScale(1);
		$black = new Zend_Pdf_Color_GrayScale(0);
		$grey = new Zend_Pdf_Color_GrayScale(0.5);
		$darkGrey = new Zend_Pdf_Color_GrayScale(0.2);
		/*
		 * Here starts the drawing of the header info section
		 */


		//DATE
		$this->_setFontRegular($page, 10);

		//line setup don't forget it!
		$page->setLineColor($black);
		$page->setLineWidth(1);

		//Going down to headers of first row
		//$this->y -=11;

		//headers are white in dark background this effects rectangle
		$page->setFillColor($grey);

		//header box
		//$page->drawRectangle(25, $this->y-4, 570, $this->y +11);
		$page->drawRectangle(380, $this->y-140, 570, $this->y -155);
		$page->drawLine(25, $this->y-4, 570, $this->y-4);

		//NUMBER easy read boxes
		$page->setFillColor(new Zend_Pdf_Color_GrayScale(0.85));
		$page->drawLine(390, $this->y-4, 390, $this->y-60);

		$page->drawLine(25, $this->y-60, 570, $this->y-60);
		$page->setFillColor($black);
		$page->setLineColor($black);


		$this->_setFontRegular($page, 50/count($orders));
		foreach ($orders as $order)
		{
			$page->drawText('#'.$order->getRealOrderId().Mage::helper('sales')->__('  ') . Mage::helper('core')->formatDate($order->getCreatedAtStoreDate(), 'medium', true), 30, $this->y-(60/count($orders)), 'UTF-8');
			$this->y -= 50/count($orders);
		}

		$this->_setFontRegular($page, 30);

		if($Address = $defaultOrder->getShippingAddress())
		{
		}
		else
		{
			$Address = $defaultOrder->getBillingAddress();
		}
		//International orders require special attention
		if($Address->getCountry() != 'US')
		{
			$this->_setFontBold($page, 45);
			$page->drawText('INT '.$Address->getCountry(), 395, $this->y, 'UTF-8');
			$this->_setFontRegular($page, 30);
		}
		else
		{
			$this->_setFontRegular($page, 32);
			$actualWidth = 175;
			$initialWidth = $this->widthForStringUsingFontSize($Address->getRegion(), $page->getFont(), $page->getFontSize());

			if ($initialWidth >= $actualWidth)
			{
				$FontSize = $page->getFontSize();
				$fontType = $page->getFont();
				$changingString = $Address->getRegion();
				while ($this->widthForStringUsingFontSize($changingString, $fontType, $FontSize) >= $actualWidth)
				{
					$FontSize -=1;
				}
				$this->_setFontRegular($page, $FontSize);
			}

			$page->drawText($Address->getRegion(), 395, $this->y+8, 'UTF-8');
		}
		$this->_setFontItalic($page, 8);
		//second row end first
		$this->y -=40;

		//customer ID
		$page->drawText('Account #', 306, $this->y+20, 'UTF-8');
		$this->_setFontRegular($page, 30);
		$page->drawText($defaultOrder->getcustomer_id(), 306, $this->y-4, 'UTF-8');
		$page->drawLine(380, $this->y+30, 380, $this->y-40);

		// Customer full last name

		$actualWidth = 275;
		$initialWidth = $this->widthForStringUsingFontSize($Address->getName(), $page->getFont(), $page->getFontSize());

		if ($initialWidth >= $actualWidth)
		{
			$FontSize = $page->getFontSize();
			$fontType = $page->getFont();
			$changingString = $Address->getName();
			while ($this->widthForStringUsingFontSize($changingString, $fontType, $FontSize) >= $actualWidth)
			{
				$FontSize -=1;
			}
			$this->_setFontRegular($page, $FontSize);
		}

		$page->drawText($Address->getName(), 25, $this->y, 'UTF-8');
		$page->drawLine(304, $this->y+30, 304, $this->y-10);

		// Shipping Speed
		// Setup bold logic here
		$this->_setFontRegular($page, 25);
		$shippingType = $defaultOrder->getShippingDescription();
		if ($shippingType == 'Best Method: USPS or Fedex Ground - Table Rate')
		{$page->drawText('Standard', 385, $this->y, 'UTF-8');}
		else
		{
			$this->_setFontRegular($page, 10);
			$caption = $this->WrapTextToWidth($page, $shippingType, 195);
			$offset = $this->DrawMultilineText($page, $caption, 385, $this->y+15, 10, 0.2, 10);
			//$page->drawText($shippingType, 385, $this->y, 'UTF-8');
		}

		$page->drawLine(25, $this->y-10, 570, $this->y-10);
		//end second row

		//starting from the top on this one (because of comments box)
		$this->y -=10;
		$page->drawRectangle(25, $this->y, 380, $this->y -95,Zend_Pdf_Page::SHAPE_DRAW_STROKE);
		$page->drawLine(380, $this->y-20, 570, $this->y-20);
		$page->drawLine(380, $this->y-40, 570, $this->y-40);

		//Comments Box
		$orders = array_reverse($orders);
		if ($defaultOrder->getState() == 'holded')
		{
			$this->_setFontBold($page, 25);
			$caption = $this->WrapTextToWidth($page, '< HELD >', 345);
			$offset = $this->DrawMultilineText($page, $caption, 30, $this->y-35, 40, 0.2, 40);
			$this->_setFontRegular($page, 25);
			$comment = '';
			foreach ($orders as $order)
			{
				$comment .= ' '.$order->getBiebersdorfCustomerordercomment();
			}
			$caption = $this->WrapTextToWidth($page, $comment, 345);
			$offset = $this->DrawMultilineText($page, $caption, 30, $this->y-50, 10, 0.2, 10);
		}
		else
		{
			$comment = '';
			foreach ($orders as $order)
			{
				$comment .= ' '.$order->getBiebersdorfCustomerordercomment();
			}
			$caption = $this->WrapTextToWidth($page, $comment, 345);
			$offset = $this->DrawMultilineText($page, $caption, 30, $this->y-15, 10, 0.2, 10);
		}


		//Shipping & Cost
		$this->_setFontRegular($page, 18);
		$amt= 0;
		foreach ($orders as $order)
		{
			$amt += $order->getShippingAmount();
		}
		$page->drawText('Shipping: '.$defaultOrder->formatPriceTxt($amt), 383, $this->y-16.5, 'UTF-8');

		$amt= 0;
		foreach ($orders as $order)
		{
			$amt += $order->getSubtotal();
		}
		$page->drawText('SubTotal: '.$defaultOrder->formatPriceTxt($amt), 383, $this->y-36.5, 'UTF-8');

		//pick check row
		$this->y -=55;

		//headers are white in dark background this effects text
		$page->setFillColor($white);
		$this->_setFontRegular($page, 10);

		//pick check header
		$page->drawText('Picked', 385, $this->y+4, 'UTF-8');
		$page->drawText('Checked', 431, $this->y+4, 'UTF-8');
		$page->drawText('Packed', 482, $this->y+4, 'UTF-8');
		$page->drawText('Labeled', 526, $this->y+4, 'UTF-8');

		$page->drawRectangle(380, $this->y+15, 427.5, $this->y -40,Zend_Pdf_Page::SHAPE_DRAW_STROKE);
		$page->drawRectangle(427.5, $this->y+15, 475, $this->y -40,Zend_Pdf_Page::SHAPE_DRAW_STROKE);
		$page->drawRectangle(475, $this->y+15, 522.5, $this->y -40,Zend_Pdf_Page::SHAPE_DRAW_STROKE);
		$page->drawRectangle(522.5, $this->y+15, 570, $this->y -40,Zend_Pdf_Page::SHAPE_DRAW_STROKE);

		$this->y -=60;

		//Item Table Headers
		$page->setFillColor($grey);
		$page->drawRectangle(25, $this->y+20, 570, $this->y);
		$page->setFillColor($white);
		$this->_setFontRegular($page, 16);
		$page->drawText('Shelf', 70, $this->y+4, 'UTF-8');
		$page->drawText('Qty', 161, $this->y+4, 'UTF-8');
		$page->drawText('SKU', 220, $this->y+4, 'UTF-8');
		$page->drawText('Product', 310, $this->y+4, 'UTF-8');
		$page->drawLine(25, $this->y, 570, $this->y);

		$this->y -=22;

		// Here come the ITEMS!



		// this creates a array which contains all products and their fields
		// most of it is taken up by the bin location logic
		$n = 0;
		//count the number of bundles
		$bundleID = 0;
		$Products = array();
		$totalItems = array();
		foreach ($orders as $order)
		{
			$totalItems = array_merge($totalItems,$order->getAllItems());
		}

		foreach ($totalItems as $item)
		{
			try{
				$productId = Mage::getModel('catalog/product')->getIdBySku($item->getSku());
				$product = Mage::getModel('catalog/product')->load($productId);

				// Don't print parents
				if ($product->isSuper() == false)
				{
					// If it's a bundle item add it to the bundled item array
					if ($product->isComposite() == true) {
						$children = $item->getChildrenItems();
						foreach ($children as $child)
						{
							$childSku = $child->getSku();
							if($childSku[3] != '9')
							{
								$bundleItems[$bundleID][] = $childSku;
							}
						}
						if(sizeof($bundleItems[$bundleID]) <= 1)
						{
							unset($bundleItems[$bundleID]);
						}
						else
						{
							$bundleID += 1;
						}
						//$page->drawText($options['attributes_info'], 3500, $this->y, 'UTF-8');
							
					}
					elseif($product->getTypeId() == 'simple')
					{

						//Bin location Code - VELO ORANGE ADDITION :p
						//retrieve string
						$rawBinLocationData = $product->getData('binlocation');
						if ($rawBinLocationData != '')
						{
							//split string into individual bin locations
							$BinLocationData = explode(',', $rawBinLocationData);

							//determine the type of each location
							// Assign values for the 6 normal bin locations,
							// create an array for the rest called genericLocation.
							foreach ($BinLocationData as $location)
							{
								//Find the tag
								$trimmedLocation = trim($location);
								$location = $trimmedLocation;
								if(isset($location[1]) == true)
								{
									if ($location[1] == '!' || $location[1] == '$' || $location[1] == '%')
									{$tag = ($location[0].$location[1]);}
									else if ($location[0] == '!' || $location[0] == '$' || $location[0] == '%')
									{$tag = $location[0];}
									else
									{$tag = '';}
								}
								else
								{$tag = '';}
								//Done finding the tag

								//remove tag
								$trimmed = trim($location, $tag);
								$location = $trimmed;

								switch ($tag)
								{
									case '!':
										$Primary = $location;
										break;
									case '$':
										$PrimarySoverstock = $location;
										break;
									case '%':
										$Primaryoverstock = $location;
										break;
									case '!!':
										$Secondary = $location;
										break;
									case '$$':
										$SecondarySoverstock = $location;
										break;
									case '%%':
										$SecondaryOverstock = $location;
										break;
										// if there is no tag add it to generic
									default:
										$genericLocation = $location;
								}
							}

							// assign the final variable
							if (isset($Primary) == true)
							{$displayBinLocation = $Primary;}
							else if (isset($PrimarySoverstock) == true)
							{$displayBinLocation = $PrimarySoverstock;}
							else if (isset($Primaryoverstock) == true)
							{$displayBinLocation = $Primaryoverstock;}
							else if (isset($Secondary) == true)
							{$displayBinLocation = $Secondary;}
							else if (isset($SecondarySoverstock) == true)
							{$displayBinLocation = $SecondarySoverstock;}
							else if (isset($SecondaryOverstock) == true)
							{$displayBinLocation = $SecondaryOverstock;}
							else
							{$displayBinLocation = $genericLocation;}
						}
						else
						{
							$displayBinLocation = '?';
						}
						// end bin location code.
						
					if ($item->getData('parent_item_id') != NULL)
						{
							$price = $item->getParentItem()->getOriginalPrice();
						}
						else
						{
							$price = $item->getOriginalPrice();
						}

						$Products[$n] = array("BinLocation"=>$displayBinLocation,"Qty"=>number_format($item->getQtyOrdered()),"SKU"=>$product->getSku(),"Name"=>$product->getName(),"Price"=>$price);
						$n=$n+1;
					}
				}
			}
			catch (Exception $e)
			{
				$Products[$n] = array("BinLocation"=>'Error',"Qty"=>'SKU',"SKU"=>'Not',"Name"=>'Found');
				$n=$n+1;
			}
		}

		//Sort it right!
		$defaultOrder1='asc';
		$natsort=FALSE;
		$case_sensitive=FALSE;
		if(is_array($Products) && count($Products)>0)
		{
			foreach(array_keys($Products) as $key)
			$temp[$key]=$Products[$key]["BinLocation"];
			if(!$natsort)
			($defaultOrder1=='asc')? asort($temp) : arsort($temp);
			else
			{
				($case_sensitive)? natsort($temp) : natcasesort($temp);
				if($defaultOrder1!='asc')
				$temp=array_reverse($temp,TRUE);
			}
			foreach(array_keys($temp) as $key)
			(is_numeric($key))? $sorted[]=$Products[$key] : $sorted[$key]=$Products[$key];
			$sorted;

			$Products = $sorted;
		}

		//how long is it?
		$numofproducts = count($Products);
		//write it out then, this is merely drawing the data in the products array

		for ($i = 0; $i < $numofproducts; ++$i)
		{
			if ($this->y < 40) {
				$page = $this->newPageCustom(array('table_header' => true),$defaultOrder->getRealOrderId());
			}

			if(($i%2) == 0)
			{
				$page->setFillColor(new Zend_Pdf_Color_GrayScale(0.9));
				$page->drawRectangle(25, $this->y+21, 570, $this->y+1,Zend_Pdf_Page::SHAPE_DRAW_FILL);
			}


			$this->_setFontRegular($page, 18);

			//check box
			$page->setFillColor($white);
			$page->setLineColor($darkGrey);
			$page->drawRectangle(26, $this->y+20, 44, $this->y+2);

			$page->setFillColor($darkGrey);

			//color for columns
			$page->setLineColor(new Zend_Pdf_Color_GrayScale(0.8));

			//large bin location logic
			if ((strlen($Products[$i]["BinLocation"])) <= 4)
			{
				$page->drawText($Products[$i]["BinLocation"], 70, $this->y+4, 'UTF-8');
			}
			else if ((strlen($Products[$i]["BinLocation"])) <= 46)
			{
				$this->_setFontRegular($page, 8);

				$caption = $this->WrapTextToWidth($page, $Products[$i]["BinLocation"], 80);
				$offset = $this->DrawMultilineText($page, $caption, 50, $this->y+13, 10, 0.2, 10);
			}
			else
			{
				$this->_setFontRegular($page, 8);
				$caption = $this->WrapTextToWidth($page, 'Bin location string too long(somehow)', 80);
				$offset = $this->DrawMultilineText($page, $caption, 50, $this->y+13, 10, 0.2, 10);
			}

			$page->drawLine(150, $this->y+21, 150, $this->y);

			//greater than one logic
			if($Products[$i]["Qty"]==1)
			{
				$this->_setFontRegular($page, 20);
				$page->setFillColor(new Zend_Pdf_Color_GrayScale(0.4));
				$this->drawTextInBlock($page, $Products[$i]["Qty"], 150, $this->y+3, 60, 22);
			}
			else
			{
				$this->_setFontBold($page, 20);
				$page->setFillColor($black);
				$this->drawTextInBlock($page, $Products[$i]["Qty"], 150, $this->y+3, 60, 22);
			}
			$page->drawLine(210, $this->y+21, 210, $this->y);

			//small sku logic - Outdated thanks to Annette
			$page->setFillColor(new Zend_Pdf_Color_GrayScale(0.1));
			if(strlen($Products[$i]["SKU"]) <= 7)
			{
				$this->_setFontBold($page, 13);
				$page->drawText(substr($Products[$i]["SKU"], 0, 11), 216, $this->y+5, 'UTF-8');
			}
			else
			{
				$this->_setFontBold($page, 13);
				$page->drawText(substr($Products[$i]["SKU"], 0, 11), 216, $this->y+5, 'UTF-8');
			}
			$page->drawLine(305, $this->y+21, 305, $this->y);

			//name changing size
		$page->setFillColor(new Zend_Pdf_Color_GrayScale(0.3));
			$this->_setFontRegular($page, 12);
			$actualWidth = 223;
			$initialWidth = $this->widthForStringUsingFontSize($Products[$i]["Name"], $page->getFont(), $page->getFontSize());

			if ($initialWidth >= $actualWidth)
			{
				$FontSize = $page->getFontSize();
				$fontType = $page->getFont();
				$changingString = $Products[$i]["Name"];
				while ($this->widthForStringUsingFontSize($changingString, $fontType, $FontSize) >= $actualWidth)
				{
					$FontSize -=1;
				}
				$this->_setFontRegular($page, $FontSize);
			}

			$page->drawText($Products[$i]["Name"], 310, $this->y+6, 'UTF-8');

			$this->_setFontRegular($page, 12);
			try
			{
				$page->drawText($order->formatPriceTxt($Products[$i]["Price"]), 533, $this->y+6, 'UTF-8');
			}
			catch (Exception $e)
			{}

			$page->setFillColor(new Zend_Pdf_Color_GrayScale(0.6));

			$page->setLineColor($grey);
			$page->drawLine(25, $this->y, 570, $this->y);

			$this->y -=22;
		}

		//Bundle Message
		if ($bundleID != 0)
		{
			$this->_setFontRegular($page, 9);
			$page->setFillColor(new Zend_Pdf_Color_GrayScale(0.4));
			$page->drawText('Bundle these products.', 30, $this->y, 'UTF-8');
			$this->y -=14;
			//For each bundle group (items that are bundled under one item)
			$currentX = 30;
			foreach ($bundleItems as $BundleGroup)
			{
				$lasty1 = $this->y+10;
				//For each item in the bundle
				foreach ($BundleGroup as $bundleItem)
				{
					$page->drawText($bundleItem, $currentX, $this->y, 'UTF-8');
					$this->y -=10;
				}
				$page->drawRectangle($currentX-3, $lasty1, $currentX+67, $this->y+7,Zend_Pdf_Page::SHAPE_DRAW_STROKE);
				$currentX +=70;
			}
		}
		//some more translation stuff
		if($Address->getCountry() != 'US')
		{
			$page->drawText('Dimensions', 300, $this->y, 'UTF-8');
			$page->drawRectangle(300, $this->y-5 , 570, $this->y - 62,Zend_Pdf_Page::SHAPE_DRAW_STROKE);
			$page->drawText('Weight', 30, $this->y, 'UTF-8');
			$page->drawLine(80, $this->y-2, 280, $this->y-2);
			$page->drawText('Emailed', 30, $this->y-30, 'UTF-8');
			$page->drawLine(100, $this->y-32, 280, $this->y-32);
			$page->drawText('Confirmed', 30, $this->y-60, 'UTF-8');
			$page->drawLine(120, $this->y-62, 280, $this->y-62);
		}

		return $pdf;
	}

	/**
	 * Create new page and assign to PDF object
	 *
	 * @param array $settings
	 * @return Zend_Pdf_Page
	 */
	public function newPageCustom(array $settings = array(),$defaultOrdernumber)
	{
		/* Add new table head */
		$page = $this->_getPdf()->newPage(Zend_Pdf_Page::SIZE_A4);
		$this->_getPdf()->pages[] = $page;
		$this->y = 800;

		$this->y -=25;
		if (!empty($settings['table_header'])) {



			$this->_setFontRegular($page, 8);
			$page->setFillColor(new Zend_Pdf_Color_GrayScale(0.1));
			$page->drawText('#'.$defaultOrdernumber, 30, $this->y+20, 'UTF-8');
			$this->y -=8;
			//Item Table Headers
			$page->setFillColor(new Zend_Pdf_Color_GrayScale(0.5));
			$page->drawRectangle(25, $this->y+20, 570, $this->y);
			$page->setFillColor(new Zend_Pdf_Color_GrayScale(1));
			$this->_setFontRegular($page, 16);
			$page->drawText('Shelf', 70, $this->y+4, 'UTF-8');
			$page->drawText('Qty', 161, $this->y+4, 'UTF-8');
			$page->drawText('SKU', 220, $this->y+4, 'UTF-8');
			$page->drawText('Product', 310, $this->y+4, 'UTF-8');
			$page->drawLine(25, $this->y, 570, $this->y);
			$this->y -=22;
		}
		return $page;
	}

}