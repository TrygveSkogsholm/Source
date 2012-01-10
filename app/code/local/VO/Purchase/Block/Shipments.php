<?php
class VO_Purchase_Block_Shipments extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'shipments';
    $this->_blockGroup = 'purchase';
    $this->_headerText = Mage::helper('purchase')->__('Shipments');
    $this->_addButtonLabel = Mage::helper('purchase')->__('New Shipment');
    parent::__construct();
  }
}