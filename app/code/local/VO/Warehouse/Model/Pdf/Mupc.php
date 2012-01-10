<?php

class VO_Warehouse_Model_Pdf_Mupc extends VO_Warehouse_Model_Pdf_Barcodes
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
		$page = $pdf->newPage(Zend_Pdf_Page::SIZE_LETTER);

		//Add this page to the pages[] array in the pdf object!
		$pdf->pages[] = $page;

		$outerSpace = 11.33858267716535433070866141732;
		$columnSpace = 13.322834645669291338582677165351;
		$topBottomSpace = 35.716535433070866141732283464558;
		$labelHeight = 143.4330708661417322834645669291;
		$labelWidth = 287.2629921259842519685039370078;

		$this->y = 792;
		$this->x = 0;

		// some nice color cpu saving
		$white = new Zend_Pdf_Color_GrayScale(1);
		$black = new Zend_Pdf_Color_GrayScale(0);
		$grey = new Zend_Pdf_Color_GrayScale(0.5);
		$darkGrey = new Zend_Pdf_Color_GrayScale(0.2);
		$this->_setFontRegular($page, 15);
		$page->setLineColor($black);

		$columnNumber = 2;
		$rowNumber = 5;

		//Start off by going down to the first row.
		$this->y -= $topBottomSpace;
		for ($r = 0; $r < $rowNumber; $r++)
		{
			//We start every row one column space from the left.
			$this->x = $outerSpace;
			//Write every column then.
			for ($c = 0; $c < $columnNumber; $c++)
			{
				//DRAW THAT LABEL
				//Sku
				$page->setFillColor($black);
				$this->_setFontBold($page, 16);

				$Sku = $product->getSKU();
				if ($this->widthForStringUsingFontSize($Sku, $page->getFont(), $page->getFontSize()) < 260)
				{
					$page->drawText($Sku, $this->x+140, $this->y-40, 'UTF-8');
				}
				else
				{
					$this->_setFontBold($page, 40);
					$page->drawText($Sku, $this->x+140, $this->y-40, 'UTF-8');
				}
				//Name
				$this->_setFontBold($page, 12);
				$name = $product->getName();
				$maxChars = 46;
				$length = strlen($name);
				if ($length > $maxChars)
				{
					$name = substr_replace($name, '', $maxChars, $length - $maxChars);
				}
				$caption = $this->WrapTextToWidth($page, $name, 137);
				$offset = $this->DrawMultilineText($page, $caption, $this->x+130, $this->y-60, 16, 0.1, 16, 'c',137, Zend_Pdf_Font::FONT_HELVETICA_BOLD);

				$this->drawUPC($page, $this->x+15, $this->y-30, $product->getupc());
				
				$page->drawRectangle($this->x, $this->y, $this->x+$labelWidth, $this->y - $labelHeight ,Zend_Pdf_Page::SHAPE_DRAW_STROKE);
				//Done? well move over to the start of the next column.
				$this->x += ($labelWidth + $columnSpace );
			}
			$this->y -= $labelHeight;
		}
		$this->y -= $topBottomSpace;
		return $pdf;
	}
}