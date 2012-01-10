<?php
class VO_Purchase_Block_Suppliers_Edit_Tab_Products_Extended extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
  	//Folder which has the files this container... contains
    $this->_controller = 'suppliers_edit_tab_products_extended';
    //block group = module of blocks
    $this->_blockGroup = 'purchase';
    $this->_headerText = Mage::helper('purchase')->__('Extended Costs');
    $this->_addButtonLabel = Mage::helper('purchase')->__('New Extended Cost');
    
    parent::__construct();
  }
  
  public function getCreateUrl()
  {
  	return $this->getUrl('*/*/addExtended',array('supplier_product_id' => Mage::registry('supplier_product_data')->getId()));
  }
}