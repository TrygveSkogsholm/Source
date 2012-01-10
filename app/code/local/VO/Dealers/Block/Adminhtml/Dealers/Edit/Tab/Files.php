<?php

class VO_Dealers_Block_Adminhtml_Dealers_Edit_Tab_Files extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareForm()
	{
		$this->setTemplate('dealers/files.phtml');
		return parent::_prepareForm();
	}
}
