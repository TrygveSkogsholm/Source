<?php
class VO_Purchase_Block_Orders extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
  	//Folder which has the files this container... contains
    $this->_controller = 'orders';
    //block group = module of blocks
    $this->_blockGroup = 'purchase';
    $this->_headerText = Mage::helper('purchase')->__('Purchase Orders');
    $this->_addButtonLabel = Mage::helper('purchase')->__('New Purchase Order');
    parent::__construct();
  }
}