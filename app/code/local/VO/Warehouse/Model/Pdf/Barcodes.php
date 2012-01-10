<?php
/*
 * This abstract class is for the labels we will print with bar codes, it's primary purpose is to provide
 * barcode functionality for the PDF's who inherit it.
 *
 */
abstract class VO_Warehouse_Model_Pdf_Barcodes extends VO_Warehouse_Model_Pdf_Pdfhelper
{
	public function drawUPC(&$page, $x, $y, $code,$width = null,$height = null,$type = 'UPC-A')
	{
		if (strtoupper($type) == 'UPC-A' && strlen($code) == 12)
		{
			//====================================================
			//BARCODE... CODE
			//Create an index for the binary conversion.
			//Note this is the left hand side. The left hand 0 means black
			//1 means white, simply reverse for right side.
			$index[0] = '0001101';
			$index[1] = '0011001';
			$index[2] = '0010011';
			$index[3] = '0111101';
			$index[4] = '0100011';
			$index[5] = '0110001';
			$index[6] = '0101111';
			$index[7] = '0111011';
			$index[8] = '0110111';
			$index[9] = '0001011';

			//These are the dimensions for the bar code itself.
			//If width and height are set we need to scale these
			$Xdimension = 0.9354;
			$symbolHeight = 73.417322834645669291338582677147;

			if ($width != null)
			{
				//there are 95 bits and 18 x-dimensions in the barcode width
				//Meaning the width = Xdimenion times 113
				$Xdimension = $width/113;
			}
			else
			{
				$width = $Xdimension * 113;
			}

			if ($height != null)
			{
				//kk, we have our width, the height should be symbol height + 5x xdimension
				//+ 3 xdimensions(text)
				$symbolHeight = ($height - ($Xdimension * 8));
			}
			else
			{
				$height = $symbolHeight + ($Xdimension * 8);
			}
			
			//Intrinsic to UPC-A
			$start = '101';
			$middle = '01010';
			$end = '101';

			//Split the code up into our right and left hand sides
			$left = substr($code,0,6);
			$right = substr($code,6,6);
			$left = str_split($left);
			$right = str_split($right);
			$start = str_split($start);
			$middle = str_split($middle);
			$end = str_split($end);

			//Ok now we are all set up, lets start writing :)
			$white = new Zend_Pdf_Color_GrayScale(1);
			$black = new Zend_Pdf_Color_GrayScale(0);
			
			$page->saveGS();
			
			$page->setFillColor($white);
			$page->drawRectangle($x, $y, $x+$width, $y - $height ,Zend_Pdf_Page::SHAPE_DRAW_FILL);

			$this->_setFontBold($page, (7*$Xdimension));
			
			//Ssshhh! Quiet space...
			$x += (3*$Xdimension);
			$page->setFillColor($black);
			$page->drawText($left[0], $x, $y-$symbolHeight-(7.5*$Xdimension), 'UTF-8');
			$x += (6*$Xdimension);
				
			//guard the bars!
			foreach ($start as $bar)
			{
				if ($bar == 1)
				{
					$page->setFillColor($black);
					$page->drawRectangle($x, $y, $x+$Xdimension, $y - ($symbolHeight+($Xdimension*5)) ,Zend_Pdf_Page::SHAPE_DRAW_FILL);
				}
				else if ($bar == 0)
				{
					$page->setFillColor($white);
					$page->drawRectangle($x, $y, $x+$Xdimension, $y - ($symbolHeight+($Xdimension*5)) ,Zend_Pdf_Page::SHAPE_DRAW_FILL);
				}
				$x += ($Xdimension);
			}
			
			//Left
			foreach ($left as $key => $digit)
			{
				
				$module = str_split($index[$digit]);
				foreach ($module as $bar)
				{
				if ($bar == 1)
				{
					$page->setFillColor($black);
					$page->drawRectangle($x, $y, $x+$Xdimension, $y - ($symbolHeight) ,Zend_Pdf_Page::SHAPE_DRAW_FILL);
				}
				else if ($bar == 0)
				{
					$page->setFillColor($white);
					$page->drawRectangle($x, $y, $x+$Xdimension, $y - ($symbolHeight) ,Zend_Pdf_Page::SHAPE_DRAW_FILL);
				}
				$x += ($Xdimension);
				}
				$page->setFillColor($black);
				if($key != 0)
				{$page->drawText($digit, $x-(5*$Xdimension), $y-$symbolHeight-(6.5*$Xdimension), 'UTF-8');}
				else {}
			}
			
			//guard the bars!
			foreach ($middle as $bar)
			{
				if ($bar == 1)
				{
					$page->setFillColor($black);
					$page->drawRectangle($x, $y, $x+$Xdimension, $y - ($symbolHeight+($Xdimension*5)) ,Zend_Pdf_Page::SHAPE_DRAW_FILL);
				}
				else if ($bar == 0)
				{
					$page->setFillColor($white);
					$page->drawRectangle($x, $y, $x+$Xdimension, $y - ($symbolHeight+($Xdimension*5)) ,Zend_Pdf_Page::SHAPE_DRAW_FILL);
				}
				$x += ($Xdimension);
			}
			
			//Right
			foreach ($right as $key => $digit)
			{
				$module = str_split($index[$digit]);
				foreach ($module as $bar)
				{
				if ($bar == 0)
				{
					$page->setFillColor($black);
					$page->drawRectangle($x, $y, $x+$Xdimension, $y - ($symbolHeight) ,Zend_Pdf_Page::SHAPE_DRAW_FILL);
				}
				else if ($bar == 1)
				{
					$page->setFillColor($white);
					$page->drawRectangle($x, $y, $x+$Xdimension, $y - ($symbolHeight) ,Zend_Pdf_Page::SHAPE_DRAW_FILL);
				}
				$x += ($Xdimension);
				}
				$page->setFillColor($black);
				if($key != 5)
				{$page->drawText($digit, $x-(5*$Xdimension), $y-$symbolHeight-(6.5*$Xdimension), 'UTF-8');}
				else {$flag = true;}
			}
			
			//guard the bars!
			foreach ($end as $bar)
			{
				if ($bar == 1)
				{
					$page->setFillColor($black);
					$page->drawRectangle($x, $y, $x+$Xdimension, $y - ($symbolHeight+($Xdimension*5)) ,Zend_Pdf_Page::SHAPE_DRAW_FILL);
				}
				else if ($bar == 0)
				{
					$page->setFillColor($white);
					$page->drawRectangle($x, $y, $x+$Xdimension, $y - ($symbolHeight+($Xdimension*5)) ,Zend_Pdf_Page::SHAPE_DRAW_FILL);
				}
				$x += ($Xdimension);
			}
			
			//Ssshhh! Quiet space...
			$x += (3*$Xdimension);
			$page->setFillColor($black);
			$page->drawText($right[5], $x, $y-$symbolHeight-(7.5*$Xdimension), 'UTF-8');
			$x += (6*$Xdimension);
			
			$page->restoreGS();
			// End drawing
		}
		//=======================End Bar Code=================
		//====================================================
	}
}