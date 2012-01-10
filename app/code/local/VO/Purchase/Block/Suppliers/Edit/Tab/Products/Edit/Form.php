<?php

class VO_Purchase_Block_Suppliers_Edit_Tab_Products_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareForm()
	{
		$form = new Varien_Data_Form(array(
                                      'id' => 'edit_form',
                                      'action' => $this->getUrl('*/*/productsave', array('id' => $this->getRequest()->getParam('id'))).'?supId='.Mage::registry('supplier_product_data')->getData('supplier_id'),
                                      'method' => 'post',
        							  'enctype' => 'multipart/form-data'
        							  )
        							  );

       $fieldset = $form->addFieldset('product_form', array('legend'=>Mage::helper('purchase')->__('General Information')));

        $fieldset->addField('model', 'text', array(
          'label'     => Mage::helper('purchase')->__('Model #'),
          'name'      => 'model',
        ));
        							  
        $fieldset->addField('case_qty', 'text', array(
          'label'     => Mage::helper('purchase')->__('Case Qty'),
          'name'      => 'case_qty',
       	  'class' => 'validate-digits'
   		 ));

        $fieldset->addField('first_cost', 'text', array(
          'label'     => Mage::helper('purchase')->__('First Cost'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'first_cost'
          ));

          $form->setUseContainer(true);

          if ( Mage::getSingleton('adminhtml/session')->getSupplierData() )
          {
          	$form->setValues(Mage::getSingleton('adminhtml/session')->getSupplierData());
          	Mage::getSingleton('adminhtml/session')->setSupplierData(null);
          } elseif ( Mage::registry('supplier_product_data') ) {
          	$form->setValues(Mage::registry('supplier_product_data')->getData());
          }

          //echo $this->getUrl('*/*/productsave', array('id' => $this->getRequest()->getParam('id'))).'?supId='.Mage::registry('supplier_product_data')->getData('supplier_id');

          $this->setForm($form);
          return parent::_prepareForm();
	}
}