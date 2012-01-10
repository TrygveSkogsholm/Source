<?php

class VO_Purchase_Block_Suppliers_Edit_Tab_Products_Extended_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
	public function __construct()
	{
		parent::__construct();

		$this->_objectId = 'id';
		$this->_blockGroup = 'purchase';
		$this->_controller = 'suppliers_edit_tab_products_extended';

		$this->_updateButton('save', 'label', Mage::helper('purchase')->__('Save Extended Cost'));
		$this->_updateButton('delete', 'label', Mage::helper('purchase')->__('Delete Extended Cost'));

		$this->_formScripts[] = "";

		/*$this->_formInitScripts[] = "
		initialize()
		";*/
	}
	
	public function getOrder()
	{
		if ( Mage::getSingleton('adminhtml/session')->getPurchaseOrderData() )
		{
			return Mage::getSingleton('adminhtml/session')->getPurchaseOrderData();
			Mage::getSingleton('adminhtml/session')->setPurchaseOrderData(null);
		} elseif ( Mage::registry('purchase_order_data') ) {
			return Mage::registry('purchase_order_data');
		}
	}

	public function getHeaderText()
	{
		if( Mage::registry('purchase_order_data') && Mage::registry('supplier_product_extended_data')->getId() ) {
			return Mage::helper('purchase')->__("Edit #%s Extended Cost", $this->htmlEscape(Mage::registry('supplier_product_extended_data')->getName()));
		} else {
			return Mage::helper('purchase')->__('New Extended Cost');
		}
	}
}