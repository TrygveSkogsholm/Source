<?php

class VO_Dealers_Block_Adminhtml_Dealers_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('dealer_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('dealers')->__('Dealer Information'));
  }

  protected function _beforeToHtml()
  {
  	  $this->addTab('info', array(
          'label'     => Mage::helper('dealers')->__('Company'),
          'title'     => Mage::helper('dealers')->__('Company name, description, type, ect...'),
          'content'   => $this->getLayout()->createBlock('dealers/adminhtml_dealers_edit_tab_form')->toHtml(),
      ));
      
     $this->addTab('files', array(
          'label'     => Mage::helper('dealers')->__('Documents'),
          'title'     => Mage::helper('dealers')->__('Uploaded files and such'),
          'content'   => $this->getLayout()->createBlock('dealers/adminhtml_dealers_edit_tab_files')->toHtml(),
      ));
      
      $this->addTab('locale', array(
          'label'     => Mage::helper('dealers')->__('Location'),
          'title'     => Mage::helper('dealers')->__('Any data associated with the location of the dealer'),
          'content'   => $this->getLayout()->createBlock('dealers/adminhtml_dealers_edit_tab_locationform')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}