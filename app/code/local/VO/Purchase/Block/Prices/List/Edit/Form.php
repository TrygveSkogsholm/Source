<?php

/*
 * The plan is to have all the tabs mentioned but the item adding removing and such is going to be pure JS creating a JSON object.
 */
class VO_Purchase_Block_Prices_List_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareForm()
	{
		$form = new Varien_Data_Form(array
		(
			'id' => 'edit_form',
			'action' => $this->getUrl('*/*/saveList', array('id' => $this->getRequest()->getParam('id'))),
			'method' => 'post',
			'enctype' => 'multipart/form-data'
		));
		
		$form->setUseContainer(true);
		$this->setForm($form);
		
		$fieldset = $form->addFieldset('price_list_form', array('legend'=>Mage::helper('purchase')->__('List Info')));
		$skuFieldset = $form->addFieldset('add_product_prices_form', array('legend'=>Mage::helper('purchase')->__('Add SKUs to List')));
		
		$fieldset->addField('name', 'text', array(
          'label'     => Mage::helper('purchase')->__('Name'),
          'name'      => 'name',
		  'required'  => true
		));
		
		$fieldset->addField('comment', 'textarea', array(
          'label'     => Mage::helper('purchase')->__('Comment'),
          'name'      => 'comment',
          'style'	  => 'height:50px;'
		));
		
		$skuFieldset->addField('dump', 'textarea', array(
          'label'     => Mage::helper('purchase')->__('Sku Dump'),
          'name'      => 'dump',
		  'style'	  => 'height:50px;'
		));
		
		$skuFieldset->addField('file', 'file', array(
          'label'     => Mage::helper('purchase')->__('CSV File Upload'),
          'name'      => 'file'
		));
		
		if ( Mage::registry('price_list_data') ) {
			$form->setValues(Mage::registry('price_list_data')->getData());
		}
		
		return parent::_prepareForm();
	}
}