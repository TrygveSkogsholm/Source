<?php

/*
 * The plan is to have all the tabs mentioned but the item adding removing and such is going to be pure JS creating a JSON object.
 */
class VO_Purchase_Block_Suppliers_Edit_Tab_Products_Extended_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form(array(
                                      'id' => 'edit_form',
                                      'action' => $this->getUrl('*/*/saveExtended', array('id' => $this->getRequest()->getParam('id'),'supplier_product_id'=>Mage::registry('supplier_product_extended_data')->getData('sup_item_id'))),
                                      'method' => 'post',
        							  'enctype' => 'multipart/form-data'
                                   )
      );
      
	$fieldset = $form->addFieldset('extended_cost_form',array());
	
	$fieldset->addField('display_to_supplier', 'select', array(
          'label'     => Mage::helper('purchase')->__('Display to Supplier'),
          'name'      => 'display_to_supplier',
          'values'    => array(
			array(
	                  'value'     => 1,
	                  'label'     => Mage::helper('purchase')->__('Yes'),
			),

			array(
	                  'value'     => 0,
	                  'label'     => Mage::helper('purchase')->__('No'),
			),
			),
		'required'  => true
	));
	
	$fieldset->addField('name', 'text', array(
	'label'     => Mage::helper('purchase')->__('Name'),
	'class'     => 'required-entry',
	'required'  => true,
	'name'      => 'name'
	));
	
	$fieldset->addField('cost', 'text', array(
	'label'     => Mage::helper('purchase')->__('Cost'),
	'class'     => 'required-entry',
	'required'  => true,
	'name'      => 'cost'
	));
	
	$fieldset->addField('description', 'textarea', array(
	'label'     => Mage::helper('purchase')->__('Description'),
	'name'      => 'description',
	));
	
  	if (Mage::registry('supplier_product_extended_data')) 
  	{
		$form->setValues(Mage::registry('supplier_product_extended_data')->getData());
	}

      $form->setUseContainer(true);
      $this->setForm($form);
      return parent::_prepareForm();
  }
}