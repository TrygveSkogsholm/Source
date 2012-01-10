<?php

class VO_Warehouse_Block_Adminhtml_Ranges_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareForm()
	{
		$form = new Varien_Data_Form(array
								(
							         'id' => 'edit_form',
                                      'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
                                      'method' => 'post',
        							  'enctype' => 'multipart/form-data'
                                )                            
        );
		$this->setForm($form);
		$fieldset = $form->addFieldset('range_form', array('legend'=>Mage::helper('warehouse')->__('General')));

		$fieldset->addField('comment', 'text', array(
          'label'     => Mage::helper('warehouse')->__('Range Comment'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'comment',
		));
		
		if ( Mage::registry('range_data') ) {
			$form->setValues(Mage::registry('range_data')->getData());
		}
		return parent::_prepareForm();
	}
}