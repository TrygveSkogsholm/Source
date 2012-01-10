<?php
class VO_Purchase_Block_Suppliers extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'Suppliers';
    $this->_blockGroup = 'purchase';
    $this->_headerText = Mage::helper('purchase')->__('Suppliers');
    $this->_addButtonLabel = Mage::helper('purchase')->__('New Supplier');
    parent::__construct();
  }
}