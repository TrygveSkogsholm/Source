<?php
class VO_Warehouse_Block_Adminhtml_Inventory extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_inventory';
    $this->_blockGroup = 'warehouse';
    $this->_headerText = Mage::helper('warehouse')->__('Warehouse Inventory');
    parent::__construct();
    $this->removeButton('add');
  }
}