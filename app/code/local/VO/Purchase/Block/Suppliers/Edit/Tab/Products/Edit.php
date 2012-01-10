<?php

class VO_Purchase_Block_Suppliers_Edit_Tab_Products_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
	public function __construct()
	{
		parent::__construct();

		$this->_objectId = 'id';
		$this->_blockGroup = 'purchase';
		$this->_controller = 'suppliers_edit_tab_products';

		$this->_updateButton('back', 'onclick', 'setLocation(\''.$this->getUrl('*/*/edit',array('id' => Mage::registry('supplier_product_data')->getData('supplier_id'))).'\')');
	}

	public function getHeaderText()
	{
		if( Mage::registry('supplier_product_data') && Mage::registry('supplier_product_data')->getId() ) {
			return Mage::helper('dealers')->__("Edit %s", $this->htmlEscape(Mage::registry('supplier_product_data')->getId()));
		} else {
			return Mage::helper('dealers')->__('Add Supplier');
		}
	}
}