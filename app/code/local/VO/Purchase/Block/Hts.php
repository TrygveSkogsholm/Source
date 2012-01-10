<?php
class VO_Purchase_Block_Hts extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'hts';
    $this->_blockGroup = 'purchase';
    $this->_headerText = Mage::helper('purchase')->__('Hts Manager');
    $this->_addButtonLabel = Mage::helper('purchase')->__('Add Rate');
    parent::__construct();
  }
}