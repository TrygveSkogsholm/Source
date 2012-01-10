<?php

class VO_Purchase_Block_Hts_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form(array(
                                      'id' => 'edit_form',
                                      'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
                                      'method' => 'post',
        							  'enctype' => 'multipart/form-data'
                                   )
      );

      $form->setUseContainer(true);
      $this->setForm($form);
      
      $fieldset = $form->addFieldset('hts_form', array('legend'=>Mage::helper('purchase')->__('Code & Rate')));

		$fieldset->addField('code', 'text', array(
          'label'     => Mage::helper('purchase')->__('Code'),
          'name'      => 'code',
		  'required'  => true
		));
		
		$fieldset->addField('rate', 'text', array(
          'label'     => Mage::helper('purchase')->__('Rate'),
          'name'      => 'rate',
		  'required'  => true
		));
		
  		if (Mage::registry('hts_data'))
  		{
			$form->setValues(Mage::registry('hts_data')->getData());
		}
      
      return parent::_prepareForm();
  }
}