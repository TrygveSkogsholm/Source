<?php

class VO_Dealers_Block_Adminhtml_Dealers_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareForm()
	{
		$form = new Varien_Data_Form();
		$this->setForm($form);
		$fieldset = $form->addFieldset('dealers_form', array('legend'=>Mage::helper('dealers')->__('Company Information')));

		if(Mage::registry('dealers_data')->getData('is_approved') != 1)
		{

			$fieldset->addField('is_approved', 'select', array(
          'label'     => Mage::helper('warehouse')->__('Approved'),
          'name'      => 'is_approved',
          'values'    => array(
			array(
	                  'value'     => 1,
	                  'label'     => Mage::helper('dealers')->__('Yes'),
			),

			array(
	                  'value'     => 0,
	                  'label'     => Mage::helper('dealers')->__('No'),
			)
			),
		'required'  => true
			));
		}

		$fieldset->addField('name', 'text', array(
          'label'     => Mage::helper('dealers')->__('Store Name'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'name',
		));

		$fieldset->addField('account_id', 'text', array(
          'label'     => Mage::helper('dealers')->__('Account ID'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'account_id',
		));

		$fieldset->addField('is_primary', 'select', array(
          'label'     => Mage::helper('warehouse')->__('Main'),
          'name'      => 'is_primary',
          'values'    => array(
		array(
	                  'value'     => 1,
	                  'label'     => Mage::helper('warehouse')->__('Yes'),
		),

		array(
	                  'value'     => 0,
	                  'label'     => Mage::helper('warehouse')->__('No'),
		),
		),
		'required'  => true
		));

		$fieldset->addField('is_displayed', 'select', array(
          'label'     => Mage::helper('warehouse')->__('Display'),
          'name'      => 'is_displayed',
          'values'    => array(
		array(
	                  'value'     => 1,
	                  'label'     => Mage::helper('dealers')->__('Yes'),
		),

		array(
	                  'value'     => 0,
	                  'label'     => Mage::helper('dealers')->__('No'),
		)
		),
		'required'  => true
		));

		$fieldset->addField('type', 'select', array(
          'label'     => Mage::helper('warehouse')->__('Type'),
          'name'      => 'type',
          'values'    => Mage::getSingleton('dealers/dealers')->getOptionArray(),
		'required'  => true
		));

		$fieldset->addField('description', 'editor', array(
          'name'      => 'description',
          'label'     => Mage::helper('dealers')->__('Description'),
          'title'     => Mage::helper('dealers')->__('Description'),
          'style'     => 'width:500px; height:100px;',
          'wysiwyg'   => false,
          'required'  => false,
		));

		/*
		 * The format for the hours will be
		 *
		 * Day, from,to;Day,from,to;Day ect. all the way up to each day
		 *
		 * Day can = Sun, Mon, Tue, Wed, Thu, Fri,Sat
		 *
		 */
		$fieldset->addField('hours', 'text', array(
          'label'     => Mage::helper('dealers')->__('Store Hours'),
          'name'      => 'hours',
		  'style'     => 'width:500px;'
		  ));

		  $fieldset->addField('phone', 'text', array(
          'label'     => Mage::helper('dealers')->__('Phone'),
          'name'      => 'phone',
		  ));

		  $fieldset->addField('email', 'text', array(
          'label'     => Mage::helper('dealers')->__('Email'),
          'name'      => 'email',
		  ));

		  $fieldset->addField('website', 'text', array(
          'label'     => Mage::helper('dealers')->__('Website'),
          'name'      => 'website',
		  ));
		  $fieldset->addField('websiteNote', 'note', array(
          'text'     => Mage::helper('dealers')->__('Do not include http://, it is added
          automaticaly to the display'),
		  ));
		  if ( Mage::registry('dealers_data') ) {
		  	$form->setValues(Mage::registry('dealers_data')->getData());
		  }
		  return parent::_prepareForm();
	}
}