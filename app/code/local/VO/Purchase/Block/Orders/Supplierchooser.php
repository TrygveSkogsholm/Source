<?php
class VO_Purchase_Block_Orders_Supplierchooser extends Mage_Adminhtml_Block_Template
{
  public function __construct()
  {

    parent::__construct();

    $this->setTemplate('purchase/order/supplierchooser.phtml');
  }

  public function getSuppliers()
  {
	return Mage::getModel('purchase/supplier')->getCollection()
	->setOrder('company_name','ASC');
  }
}