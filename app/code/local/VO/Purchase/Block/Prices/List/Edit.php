<?php

class VO_Purchase_Block_Prices_List_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
	public function __construct()
	{
		parent::__construct();
			
		$this->_objectId = 'listEdit';
		$this->_blockGroup = 'purchase';
		$this->_controller = 'prices_list';
			
		$this->_updateButton('save', 'label', Mage::helper('purchase')->__('Update List'));
		$this->_updateButton('delete', 'label', Mage::helper('purchase')->__('Delete List'));
	}

	public function getHeaderText()
	{
		if( Mage::registry('price_list_data') && Mage::registry('price_list_data')->getId() ) {
			return Mage::helper('dealers')->__("Edit %s Price List", $this->htmlEscape(Mage::registry('price_list_data')->getName()));
		} else {
			return Mage::helper('dealers')->__('Add Price List');
		}
	}

	public function getBackUrl()
	{
		return $this->getUrl('*/*/list');
	}

	public function getDeleteUrl()
	{
		return $this->getUrl('*/*/deleteList', array($this->_objectId => $this->getRequest()->getParam($this->_objectId)));
	}
}