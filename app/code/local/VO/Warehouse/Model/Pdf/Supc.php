<?php

class VO_Warehouse_Model_Pdf_Supc extends VO_Warehouse_Model_Pdf_Barcodes
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
		 *Therefore 1 pdf unit = 2.8346456692913385826771653543307mm
		 *
		 *The distance between the three cols on the avery 8640 appear to be
		 *on average 3.5mm or 9.921259842519685039370078740155 PDF units. (updated it)
		 */
		$columnSpace = 11.49685039370078740157480314965;
		/*
		 * The distance between the top of the page and the start of the labels is
		 * on average 12.5 mm or 35.433070866141732283464566929125 PDF units
		 */
		$topBottomSpace = 35.433070866141732283464566929125;
		/*
		 * Each label appears to be 25.5mm high.
		 * That would be 72.11338582677165354330708661417 PDF units;
		 *
		 * To confirm note that 792 - 2(topBottomSpace) == 10*numrows x 72.113 if this is correct
		 */
		$labelHeight = 72.11338582677165354330708661417;
		/*
		 * The width appears to be 66.7 or 189.07086614173228346456692913381 PDF units
		 */
		$labelWidth = 189.07086614173228346456692913381;

		$this->y = 792;
		$this->x = -15;

		// some nice color cpu saving
		$white = new Zend_Pdf_Color_GrayScale(1);
		$black = new Zend_Pdf_Color_GrayScale(0);
		$grey = new Zend_Pdf_Color_GrayScale(0.5);
		$darkGrey = new Zend_Pdf_Color_GrayScale(0.2);
		$this->_setFontRegular($page, 15);
		$page->setLineColor($black);

		$columnNumber = 3;
		$rowNumber = 10;

		//Start off by going down to the first row.
		$this->y -= $topBottomSpace;
		for ($r = 0; $r < $rowNumber; $r++)
		{
			//We start every row one column space from the left.
			$this->x = $columnSpace + 0.8255;
			//Write every column then.
			for ($c = 0; $c < $columnNumber; $c++)
			{
				//DRAW THAT LABEL
				
				//Sku
				$page->setFillColor($black);
				$this->_setFontBold($page, 10);
				$Sku = $product->getSKU();
				$page->drawText($Sku, $this->x+10, $this->y-15, 'UTF-8');
				
				//Name
				$this->_setFontBold($page, 8);
				$name = $product->getName();
				
				$caption = $this->WrapTextToWidth($page, $name, 120);
				$offset = $this->DrawMultilineText($page, $caption, $this->x+75, $this->y-14, 8, 0.0, 8);
				
				$this->drawUPC($page, $this->x+15, $this->y-25, $product->getupc(),170,40);

				//Done? well move over to the start of the next column.
				$this->x += ($labelWidth + $columnSpace );
			}
			$this->y -= $labelHeight;
		}
		$this->y -= $topBottomSpace;

		return $pdf;
	}
}