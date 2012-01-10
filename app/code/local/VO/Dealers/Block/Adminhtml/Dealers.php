<?php
class VO_Dealers_Block_Adminhtml_Dealers extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_dealers';
    $this->_blockGroup = 'dealers';
    $this->_headerText = Mage::helper('dealers')->__('Dealer Manager');
    $this->_addButtonLabel = Mage::helper('dealers')->__('Add Dealer');
    parent::__construct();
  }
}