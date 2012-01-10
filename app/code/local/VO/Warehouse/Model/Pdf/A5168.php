<?php

class VO_Warehouse_Model_Pdf_A5168 extends VO_Warehouse_Model_Pdf_Pdfhelper
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
		$page = $pdf->newPage(Zend_Pdf_Page::SIZE_LETTER_LANDSCAPE);

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
		$Space = 36;
		$labelHeight = 251.5181102362204724409448818897;
		$labelWidth = 360.73700787401574803149606299204;

		$this->y = 612;
		$this->x = 0;

		// some nice color cpu saving
		$white = new Zend_Pdf_Color_GrayScale(1);
		$black = new Zend_Pdf_Color_GrayScale(0);
		$grey = new Zend_Pdf_Color_GrayScale(0.5);
		$darkGrey = new Zend_Pdf_Color_GrayScale(0.2);
		$this->_setFontRegular($page, 15);
		$page->setLineColor($black);

		$columnNumber = 2;
		$rowNumber = 2;

		//Start off by going down to the first row.
		$this->y -= $Space;
		for ($r = 0; $r < $rowNumber; $r++)
		{
			//We start every row one column space from the left.
			$this->x = $Space;
			//Write every column then.
			for ($c = 0; $c < $columnNumber; $c++)
			{
				//DRAW THAT LABEL
				//Sku
				$page->setFillColor($black);
				$this->_setFontBold($page, 75);
				
				$Sku = $product->getSKU();
				if ($this->widthForStringUsingFontSize($Sku, $page->getFont(), $page->getFontSize()) < 330)
				{
					$page->drawText($Sku, $this->x+30, $this->y-80, 'UTF-8');
				}
				else
				{
					$this->_setFontBold($page, 50);
					$page->drawText($Sku, $this->x+25, $this->y-70, 'UTF-8');
				}
				//Name
				$this->_setFontBold($page, 25);
				$name = $product->getName();
				$maxChars = 23;
				$length = strlen($name);
				if ($length > $maxChars)
				{
					$name = substr_replace($name, '', $maxChars, $length - $maxChars);
				}
				$caption = $this->WrapTextToWidth($page, $name, 330);
				$offset = $this->DrawMultilineText($page, $caption, $this->x+20, $this->y-110, 25, 0.1, 25, 'c', 330, Zend_Pdf_Font::FONT_HELVETICA_BOLD);
				
				//Description
				$page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_OBLIQUE), 12);
				$description = $product->getShortDescription();
				$maxChars = 76;
				if ($length = strlen($description) > $maxChars)
				{
					$description = substr_replace($description, '', $maxChars, $length - $maxChars);
				}
				$caption = $this->WrapTextToWidth($page, $description, 320);
				$offset = $this->DrawMultilineText($page, $caption, $this->x+23, $this->y-130, 12, 0.2, 12, 'c',320 , Zend_Pdf_Font::FONT_HELVETICA_OBLIQUE);
				
				$this->_setFontRegular($page, 8);
				$page->drawText('This box contains the following quantity:', $this->x+25, $this->y-175, 'UTF-8');
				
				$subx = $this->x+27;
				while ($subx < ($this->x + 300))
				{
				$page->drawRectangle($subx, $this->y-180, $subx + 50, $this->y - 200,Zend_Pdf_Page::SHAPE_DRAW_STROKE);
				$subx += 50;
				}
				$subx = $this->x+27;
				while ($subx < ($this->x + 300))
				{
				$page->drawRectangle($subx, $this->y-200, $subx + 50, $this->y - 220,Zend_Pdf_Page::SHAPE_DRAW_STROKE);
				$subx += 50;
				}
				//Done? well move over to the start of the next column.
				$this->x += $labelWidth;
			}
			$this->y -= ($labelHeight + $Space);
		}

		//$page->drawText('Hello World', $this->x, 792, 'UTF-8');
		//$this->y -= 20;
		//$page->drawText($product->getSku(), 0, 0, 'UTF-8');
		return $pdf;
	}
}