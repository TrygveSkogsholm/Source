<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2009 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @author : Olivier ZIMMERMANN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/**
 *
 */
abstract class VO_Purchase_Model_Pdf_Pdfhelper extends Mage_Sales_Model_Order_Pdf_Abstract
{
	//use this function to input inches instead of points
	public function inch($points)
	{
		return $points * 72;
	}

	/**
	 * takes some text and a width, draws it and returns the required font size to make it fit. Note will also scale up if you need it to
	 */
	public function scaleFontSizeToFit(&$page,$x,$y,$text,$width,$scaleUp = FALSE)
	{
		if (!empty($text))
		{
			$coefficient = $width/$this->widthForStringUsingFontSize($text,$page->getFont(),$page->getFontSize());
			if ($scaleUp == FALSE && $coefficient > 1)
			{
				$coefficient = 1;
			}
			$this->_setFontRegular($page,$page->getFontSize() * $coefficient);
			$page->drawText($text,$x,$y,'UTF-8');
			$this->_setFontRegular($page,$page->getFontSize() / $coefficient);
			return $page->getFontSize() * $coefficient;
		}
		return;
	}

	/**
	 * @author http://devzone.zend.com/member/3754-mediaplatforms
	 */
	public function widthForStringUsingFontSize($string, $font, $fontSize)
	{
		try
		{
			$drawingString = iconv('UTF-8', 'UTF-16BE//IGNORE', $string);
			$characters = array();
			for ($i = 0; $i < strlen($drawingString); $i++) {
				$characters[] = (ord($drawingString[$i++]) << 8 ) | ord($drawingString[$i]);
			}
			$glyphs = $font->glyphNumbersForCharacters($characters);
			$widths = $font->widthsForGlyphs($glyphs);
			$stringWidth = (array_sum($widths) / $font->getUnitsPerEm()) * $fontSize;
			return $stringWidth;
		}
		catch (Exception $ex)
		{
			die("Doesn't work darn it! = ".$string);
		}
	}

	public function WrapTextToWidth($page, $text, $width)
	{
		$t_words = explode(' ', $text);
		$retour = "";
		$current_line = "";
		for($i = 0;$i<count($t_words);$i++)
		{
			if ($this->widthForStringUsingFontSize($current_line.' '.$t_words[$i], $page->getFont(), $page->getFontSize()) < $width)
			$current_line .= ' '.$t_words[$i];
			else
			{
				if (($current_line != '') && (strlen($current_line) > 2))
				$retour .= $current_line."\n";
				$current_line = $t_words[$i];
			}

			if (strpos($t_words[$i], "\n") === false)
			{

			}
			else
			{
				if (($current_line != '') && (strlen($current_line) > 2))
				$retour .= $current_line."\n";
				$current_line = '';
			}
		}
		$retour .= $current_line;

		return $retour;
	}

	protected function DrawMultilineText(&$page, $Text, $x, $y, $Size, $GrayScale, $LineHeight,$newPage = false,$instance = NULL)
	{
		$retour=-$LineHeight;
		$page->setFillColor(new Zend_Pdf_Color_GrayScale($GrayScale));
		$page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA), $Size);
		foreach (explode("\n", $Text) as $value){
			if ($value!=='') {
				$page->drawText(trim(strip_tags($value)), $x, $y, 'UTF-8');
				$y -=$LineHeight;

				if ($newPage == true && $y < 60)
				{
					$page = $instance->newPageNoHeader();
					$y = 742;
				}
				$retour += $LineHeight;
			}
		}
		return $retour;
	}

	protected function _setFontRegular($object, $size = 7)
	{
		$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
		$object->setFont($font, $size);
		return $font;
	}

	protected function _setFontBold($object, $size = 7)
	{
		$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
		$object->setFont($font, $size);
		return $font;
	}

	protected function _setFontItalic($object, $size = 7)
	{
		$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_OBLIQUE);
		$object->setFont($font, $size);
		return $font;
	}

	 public function drawTextInBlock(&$page, $text, $x, $y, $width, $height, $alignment = 'c', $encoding = 'UTF-8')
	 {
	 	//$page->drawRectangle($x, $y, $x + $width, $y + $height, Zend_Pdf_Page::LINE_DASHING_SOLID);
	 	//recupere la largeur du texte
	 	$text_width = $this->widthForStringUsingFontSize($text, $page->getFont(), $page->getFontSize());
	 	switch ($alignment)
	 	{
	 		case 'c':	//on centre le texte dans le bloc
	 			$x = $x + ($width / 2) - $text_width / 2;
	 			break;
	 		case 'r':	//on aligne à droite
	 			$x = $x + $width - $text_width;
	 	}

	 	$page->drawText(trim(strip_tags($text)), $x, $y, $encoding);
	 }

	//public function NewPage($title, $StoreId = null)
	public function newPage(array $settings = array())
	{
		$page = $this->pdf->newPage(Zend_Pdf_Page::SIZE_A4);
		$this->pdf->pages[] = $page;

		//on place Y tout en haut
		$this->y = 830;

		//dessine l'entete
		$title = $settings['title'];
		$StoreId = $settings['store_id'];
		$this->drawHeader($page, $title, $StoreId);

		//retourne la page
		return $page;
	}

	/**
	 * Raccourci un texte jusqu'a ce qu'il ait une taille inférieure à celle passée en parametre
	 *
	 * @param unknown_type $text
	 * @param unknown_type $width
	 */
	public function TruncateTextToWidth($page, $text, $width)
	{
		$current_width = $this->widthForStringUsingFontSize($text, $page->getFont(), $page->getFontSize());
		while ($current_width > $width)
		{
			$text = substr($text, 0, strlen($text)-1);
			$current_width = $this->widthForStringUsingFontSize($text, $page->getFont(), $page->getFontSize());
		}
		return $text;
	}
}