<?php

class VO_Warehouse_Model_Pdf_Cs extends VO_Warehouse_Model_Pdf_Pdfhelper
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
		$page = $pdf->newPage('216:72:');

		//Add this page to the pages[] array in the pdf object!
		$pdf->pages[] = $page;

		/*Don't forget this, $y is not defined in abstract so it must be defined
		 *before you use it. It 0 is bottom of the page (yea what the hec)
		 *612:792 is size
		 *which is exactly 215.9 mm × 279.4 mm
		 *
		 *Therefore 1 pdf unit = 2.8346456692913385826771653543307mm
		 *
		 *This card is 3in x 1" or 216 x 72
		 *
		 */

		$this->y = 72;
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
		
		
		$this->_setFontBold($page, 36);
				if ($this->widthForStringUsingFontSize($Sku, $page->getFont(), $page->getFontSize()) < 180)
				{
					$page->drawText($Sku, $this->x+31, $this->y-33, 'UTF-8');
				}
				else
				{
					$this->_setFontBold($page, 25);
					$page->drawText($Sku, $this->x+31, $this->y-33, 'UTF-8');
				}
				
		$this->_setFontBold($page, 14);
		$caption = $this->WrapTextToWidth($page, $name, 190);
		$this->DrawMultilineText($page, $caption, $this->x+13, $this->y-50, 14, 0.1, 14, 'c',190, Zend_Pdf_Font::FONT_HELVETICA_BOLD_OBLIQUE);
		
		$page->drawRectangle(1, 1, 215, 71 ,Zend_Pdf_Page::SHAPE_DRAW_STROKE);
		return $pdf;
	}
}