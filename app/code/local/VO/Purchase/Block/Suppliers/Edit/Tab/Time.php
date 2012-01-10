<?php

class VO_Purchase_Block_Suppliers_Edit_Tab_Time extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareForm()
	{
		$form = new Varien_Data_Form();
		$this->setForm($form);
		$fieldset = $form->addFieldset('time_form', array('legend'=>Mage::helper('purchase')->__('Delays')));

		$fieldset->addField('buffer_time', 'text', array(
          'label'     => Mage::helper('purchase')->term('Buffer Time'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'buffer_time',
          'note' => 'The '.Mage::helper('purchase')->term('buffer time').' is the time in days that the system will warn you in advance of the absolute date when a purchase order should be created'
          ));

          $fieldset->addField('lead_time', 'text', array(
          'label'     => Mage::helper('purchase')->term('Lead Time'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'lead_time',
          'note' => 'The '.Mage::helper('purchase')->term('lead time').' is the time in days it takes a supplier to ship items once the PO has been sent to them.'
          ));

          $fieldset->addField('default_carrier', 'select', array(
          'label'     => Mage::helper('purchase')->term('Default Carrier'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'default_carrier',
          'values'    => Mage::helper('purchase')->getCarrierOptions(true),
          'note' => 'The default carrier to be selected when creating a purchase order for this supplier'
          ));

          $fieldset->addField('default_method', 'select', array(
          'label'     => Mage::helper('purchase')->term('Default Freight Method'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'default_method',
          'values'    => Mage::helper('purchase')->getShippingMethodOptions(true),
          'note' => 'The default method of shipping for this supplier, used to assume shipping delay time after lead time.'
          ));


          $fieldset->addField('default_projection_time', 'text', array(
          'label'     => Mage::helper('purchase')->term('Default Projection Time'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'default_projection_time',
          'note' => 'The '.Mage::helper('purchase')->term('default projection time').' is the time in days an auto-generated purchase order should plan for; when setting item quantities given sales/time slope. '
          ));

          $fieldset->addField('shipping_delay', 'text', array(
          'label'     => 'Shipping Delay',
          'name'      => 'shipping_delay',
          'note' => 'This is the manual override of the default shipping delays calculated with air, sea, ground options. Make sure to leave
          blank or zero if you wish to use the values set in config for freight method'
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