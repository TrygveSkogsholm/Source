<?php

class VO_Warehouse_Model_Pdf_Therm extends VO_Warehouse_Model_Pdf_Barcodes
{

	public function getPdf($product = array())
	{
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
		$page = $pdf->newPage(288,468);

		//Add this page to the pages[] array in the pdf object!
		$pdf->pages[] = $page;

		/*Don't forget this, $y is not defined in abstract so it must be defined
		 *before you use it. It 0 is bottom of the page (yea what the hec)
		 *612:792 is size
		 *which is exactly 215.9 mm × 279.4 mm
		 *
		 *Therefore 1 pdf unit = 2.8346456692913385826771653543307mm
		 *
		 *This thermal page is 4in x 6.5 in
		 *This thermal page would then be 101.6 mm x 165.1mm
		 *Or  288 x 468 PDF units.
		 */

		$this->y = 468;
		$this->y -= 121.82677165354330708661417322822;
		$this->x = 0;

		// some nice color cpu saving
		$white = new Zend_Pdf_Color_GrayScale(1);
		$black = new Zend_Pdf_Color_GrayScale(0);
		$grey = new Zend_Pdf_Color_GrayScale(0.5);
		$darkGrey = new Zend_Pdf_Color_GrayScale(0.2);
		$this->_setFontRegular($page, 15);
		$page->setLineColor($black);

		$Sku = $product->getSKU();
		$description = $product->getShortDescription();
		$name = $product->getName();
		$page->setFillColor($black);
		
		//Smaller part (58.393700787401574803149606299198 high) plateu
		//125.09291338582677165354330708658 plateu length
		//49.889763779527559055118110236208 lower height
		//72.283464566929133858267716535415 from end to rise
		//80.220472440944881889763779527539 to plateu
		$page->drawLine(0,49.89,72.28,49.89);
		$page->drawLine(72.28,49.89,80.22,58.4);
		$page->drawLine(80.22,58.4,205.313,58.4);
		$page->drawLine(205.313,58.4,213.25,49.89);
		$page->drawLine(213.25,49.89,288,49.89);
		
		$this->_setFontBold($page, 32);
		$page->drawText($Sku, $this->x+80, $this->y-343, 'UTF-8');
		
		$this->_setFontBold($page, 14);
		$caption = $this->WrapTextToWidth($page, $name, 288);
		$offset = $this->DrawMultilineText($page, $caption, $this->x, $this->y-357, 14, 0.1, 14, 'c',288, Zend_Pdf_Font::FONT_HELVETICA_BOLD_OBLIQUE);
		
		//The large part
		$page->rotate(144, 334, M_PI/2);

		$this->_setFontBold($page, 100);

		//Large SKU
		if ($this->widthForStringUsingFontSize($Sku, $page->getFont(), $page->getFontSize()) < 400)
		{
			$page->drawText($Sku, $this->x-125, $this->y+20, 'UTF-8');
		}
		else
		{
			$this->_setFontBold($page, 65);
			$page->drawText($Sku, $this->x-125, $this->y+20, 'UTF-8');
		}

		//Large Name
		$this->_setFontBold($page, 30);
		$maxChars = 43;
		$length = strlen($name);
		if ($length > $maxChars)
		{
			$name = substr_replace($name, '', $maxChars, $length - $maxChars);
		}
		$caption = $this->WrapTextToWidth($page, $name, 380);
		$offset = $this->DrawMultilineText($page, $caption, $this->x-110, $this->y-30, 30, 0.1, 30, 'c',380, Zend_Pdf_Font::FONT_HELVETICA_BOLD);
		
		$page->drawLine($this->x+300,$this->y-70,$this->x-132,$this->y-70);
		
		//$supRef = Mage::getModel('Purchase/ProductSupplier')->load($product->getId(),'pps_product_id');
		//$page->drawText($supRef->getpps_reference(), $this->x-110, $this->y-100, 'UTF-8');
		
		$this->drawUPC($page, $this->x+180, $this->y-75, $product->getupc());
		return $pdf;
	}
}