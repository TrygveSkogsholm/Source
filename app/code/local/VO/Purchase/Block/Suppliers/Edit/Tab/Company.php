<?php

class VO_Purchase_Block_Suppliers_Edit_Tab_Company extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareForm()
	{
		$form = new Varien_Data_Form();
		$this->setForm($form);
		$fieldset = $form->addFieldset('company_form', array('legend'=>Mage::helper('purchase')->__('General Information')));

		$fieldset->addField('company_name', 'text', array(
          'label'     => Mage::helper('purchase')->__('Company Name'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'company_name',
		));

		$fieldset->addField('is_manufacturer', 'select', array(
          'label'     => Mage::helper('purchase')->__('Manufacturer?'),
          'name'      => 'is_manufacturer',
          'values'    => array(
		array(
                  'value'     => 0,
                  'label'     => Mage::helper('purchase')->__('No'),
		),

		array(
                  'value'     => 1,
                  'label'     => Mage::helper('purchase')->__('Yes'),
		),
		),
		));

	    $fieldset->addField('address_country', 'text', array(
          'label'     => Mage::helper('purchase')->__('Country'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'address_country',
		));
				$fieldset->addField('address_state', 'text', array(
          'label'     => Mage::helper('purchase')->__('State'),
          'name'      => 'address_state',
		));
		$fieldset->addField('address_city', 'text', array(
          'label'     => Mage::helper('purchase')->__('City'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'address_city',
		));
		$fieldset->addField('address_zip', 'text', array(
          'label'     => Mage::helper('purchase')->__('Zip'),
          'required'  => false,
		'style'     => 'width:75px;',
          'name'      => 'address_zip'
		));

		$fieldset->addField('address_street1', 'text', array(
          'label'     => Mage::helper('purchase')->__('Street Line 1'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'address_street1',
		));
		$fieldset->addField('address_street2', 'text', array(
          'label'     => Mage::helper('purchase')->__('Street Line 2'),
          'required'  => false,
          'name'      => 'address_street2',
		));

		$fieldset->addField('address_additional', 'editor', array(
          'name'      => 'address_additional',
          'label'     => Mage::helper('purchase')->__('Aditional Address Info'),
          'title'     => Mage::helper('purchase')->__('Address'),
          'style'     => 'width:300px; height:75px;',
          'wysiwyg'   => false,
          'required'  => false,
		));

		$contactFieldset = $form->addFieldset('contact_form', array('legend'=>Mage::helper('purchase')->__('Contact Information')));
		$contactFieldset->addField('contact_name', 'text', array(
          'label'     => Mage::helper('purchase')->__('Name'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'contact_name',
		));

		$contactFieldset->addField('phone', 'text', array(
          'label'     => Mage::helper('purchase')->__('Phone'),
          'name'      => 'phone',
		));

		$contactFieldset->addField('email', 'text', array(
          'label'     => Mage::helper('purchase')->__('Email'),
          'name'      => 'email',
		));

		$contactFieldset->addField('fax', 'text', array(
          'label'     => Mage::helper('purchase')->__('Fax'),
          'name'      => 'fax',
		));

		if ( Mage::getSingleton('adminhtml/session')->getSupplierData() )
		{
			$form->setValues(Mage::getSingleton('adminhtml/session')->getSupplierData());
			Mage::getSingleton('adminhtml/session')->setSupplierData(null);
		} elseif ( Mage::registry('supplier_data') ) {
			$form->setValues(Mage::registry('supplier_data')->getData());
		}
		return parent::_prepareForm();
	}
}