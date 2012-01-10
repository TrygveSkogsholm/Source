<?php
class VO_Purchase_Block_Prices extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
  	//Folder which has the files this container... contains
    $this->_controller = 'prices';
    //block group = module of blocks
    $this->_blockGroup = 'purchase';
    $this->_headerText = Mage::helper('purchase')->__('Price Changes');
    $this->_addButtonLabel = Mage::helper('purchase')->__('Change Prices');
    parent::__construct();
  }
}