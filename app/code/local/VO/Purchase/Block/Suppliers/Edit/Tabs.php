<?php

class VO_Purchase_Block_Suppliers_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

	public function __construct()
	{
		parent::__construct();
		$this->setId('supplier_tabs');
		$this->setDestElementId('edit_form');
		$this->setTitle(Mage::helper('purchase')->__('Supplier Info'));
	}

	protected function _beforeToHtml()
	{

		$this->addTab('add_products', array(
          'label'     => Mage::helper('purchase')->__('Add Products'),
          'title'     => Mage::helper('purchase')->__('add products'),
          'content'   => $this->getLayout()->createBlock('purchase/suppliers_edit_tab_addproducts')->toHtml().$this->getLayout()->createBlock('purchase/suppliers_edit_tab_js')->toHtml(),
		));

		$this->addTab('products_section', array(
          'label'     => Mage::helper('purchase')->__('Supplier Products'),
          'title'     => Mage::helper('purchase')->__('products'),
          'content'   => $this->getLayout()->createBlock('purchase/suppliers_edit_tab_products')->toHtml(),
		));

		$this->addTab('company_section', array(
          'label'     => Mage::helper('purchase')->__('Company'),
          'title'     => Mage::helper('purchase')->__('company tab'),
          'content'   => $this->getLayout()->createBlock('purchase/suppliers_edit_tab_company')->toHtml(),
		));

		$this->addTab('times_section', array(
          'label'     => Mage::helper('purchase')->__('Supply Chain Times'),
          'title'     => Mage::helper('purchase')->__('logistics'),
          'content'   => $this->getLayout()->createBlock('purchase/suppliers_edit_tab_time')->toHtml(),
		));

		$this->setActiveTab($this->getData('tab'));

		return parent::_beforeToHtml();
	}

}