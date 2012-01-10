<?php

class VO_Warehouse_Model_Pdf_A5163 extends VO_Warehouse_Model_Pdf_Pdfhelper
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

		/*Don't forget this, $y is not defined in abstract so it must be defined
		 *before you use it. It 0 is bottom of the page (yea what the hec)
		 *612:792 is size
		 *which is exactly 215.9 mm × 279.4 mm
		 *
		 *Therefore 1 mm = 2.8346456692913385826771653543307 pdf unit
		 *
		 *or 0.35277777777777777777777777777778mm = 1 pdf unit
		 */
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
				$this->_setFontBold($page, 60);
				
				$Sku = $product->getSKU();
				if ($this->widthForStringUsingFontSize($Sku, $page->getFont(), $page->getFontSize()) < 260)
				{
					$page->drawText($Sku, $this->x+23, $this->y-60, 'UTF-8');
				}
				else
				{
					$this->_setFontBold($page, 40);
					$page->drawText($Sku, $this->x+23, $this->y-50, 'UTF-8');
				}
				//Name
				$this->_setFontBold($page, 20);
				$name = $product->getName();
				$maxChars = 16;
				$length = strlen($name);
				if ($length > $maxChars)
				{
					$name = substr_replace($name, '', $maxChars, $length - $maxChars);
				}
				$caption = $this->WrapTextToWidth($page, $name, 250);
				$offset = $this->DrawMultilineText($page, $caption, $this->x+10, $this->y-82, 20, 0.1, 20, 'c',250, Zend_Pdf_Font::FONT_HELVETICA_BOLD);
				
				//Description
				$page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_OBLIQUE), 12);
				$description = $product->getShortDescription();
				$maxChars = 80;
				if ($length = strlen($description) > $maxChars)
				{
					$description = substr_replace($description, '', $maxChars, $length - $maxChars);
				}
				$caption = $this->WrapTextToWidth($page, $description, 250);
				$offset = $this->DrawMultilineText($page, $caption, $this->x+18, $this->y-98, 12, 0.3, 12, 'c',250 , Zend_Pdf_Font::FONT_HELVETICA_OBLIQUE);
				
				//Done? well move over to the start of the next column.
				$this->x += ($labelWidth + $columnSpace );
			}
			$this->y -= $labelHeight;
		}
		$this->y -= $topBottomSpace;

		return $pdf;
	}
}