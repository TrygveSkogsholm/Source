<?php

class VO_Warehouse_Block_Adminhtml_Stock_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('stock_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('warehouse')->__('Warehouse Tasks'));
  }

  protected function _beforeToHtml()
  {
  	  $this->addTab('info', array(
          'label'     => Mage::helper('warehouse')->__('Print Labels'),
          'title'     => Mage::helper('warehouse')->__('Labels for internal peace and order.'),
          'content'   => $this->getLayout()->createBlock('warehouse/adminhtml_stock_edit_tab_labels')->toHtml(),
      ));
      return parent::_beforeToHtml();
  }
}