<?php

class VO_Purchase_Block_Orders_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('order_tabs');
      $this->setDestElementId('edit_form');
      //$this->setTitle(Mage::helper('purchase')->__('Order Aspects'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('summary', array(
          'label'     => Mage::helper('purchase')->__('Summary'),
          'title'     => Mage::helper('purchase')->__('Summary'),
          'content'   => $this->getLayout()->createBlock('purchase/orders_edit_tab_summary')->toHtml(),
      ));

            $this->addTab('items', array(
          'label'     => Mage::helper('purchase')->__('Items'),
          'title'     => Mage::helper('purchase')->__('Items'),
          'content'   => $this->getLayout()->createBlock('purchase/orders_edit_tab_items')->toHtml(),
      ));

            $this->addTab('send', array(
          'label'     => Mage::helper('purchase')->__('Send'),
          'title'     => Mage::helper('purchase')->__('Send'),
          'content'   => $this->getLayout()->createBlock('purchase/orders_edit_tab_send')->toHtml(),
      ));

            $this->addTab('ship', array(
          'label'     => Mage::helper('purchase')->__('Ship'),
          'title'     => Mage::helper('purchase')->__('Ship'),
          'content'   => $this->getLayout()->createBlock('purchase/orders_edit_tab_ship')->toHtml(),
      ));

            $this->addTab('receive', array(
          'label'     => Mage::helper('purchase')->__('Receive'),
          'title'     => Mage::helper('purchase')->__('Receive'),
          'content'   => $this->getLayout()->createBlock('purchase/orders_edit_tab_receive')->toHtml(),
      ));

      $this->setActiveTab($this->getData('tab'));

      return parent::_beforeToHtml();
  }
}