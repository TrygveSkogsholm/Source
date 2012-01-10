<?php
class VO_Warehouse_Block_Adminhtml_Stock extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_stock';
    $this->_blockGroup = 'warehouse';
    $this->_headerText = Mage::helper('warehouse')->__('Warehouse Labels');
    parent::__construct();
  }
}