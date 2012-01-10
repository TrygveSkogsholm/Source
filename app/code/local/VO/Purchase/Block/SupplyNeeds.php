<?php
class VO_Purchase_Block_SupplyNeeds extends Mage_Adminhtml_Block_Template
{
  public function __construct()
  {
  	echo 'hello';
    $this->_headerText = Mage::helper('purchase')->__('Supply Needs');
    parent::__construct();
  }
}