<?php

/*
 * The plan is to have all the tabs mentioned but the item adding removing and such is going to be pure JS creating a JSON object.
 */
class VO_Purchase_Block_Shipments_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
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

      $fieldset = $form->addFieldset('shipment_form', array('legend'=>Mage::helper('purchase')->__('General Information')));

      $fieldset->addField('edoa', 'date', array(
          'label'     => Mage::helper('purchase')->__('Estimated Date of Arrival'),
      	  'format'    => 'y-M-d',
      	  'image'  => $this->getSkinUrl('images/grid-cal.gif'),
          'input_format' => Varien_Date::DATETIME_INTERNAL_FORMAT,
          'name'      => 'edoa'
		));

		$fieldset->addField('freight_cost', 'text', array(
          'label'     => Mage::helper('purchase')->__('Freight'),
		  'style'     => ' width:50px;',
          'name'      => 'freight_cost'
		));

		$fieldset->addField('ship_method', 'select', array(
          'label'     => Mage::helper('purchase')->__('Method'),
		  'options'   => Mage::helper('purchase')->getShippingMethodOptions(false),
          'name'      => 'ship_method'
		));

		$fieldset->addField('carrier', 'select', array(
          'label'     => Mage::helper('purchase')->__('Carrier'),
		  'options'   => Mage::helper('purchase')->getCarrierOptions(false),
          'name'      => 'carrier'
		));

		$fieldset->addField('comments', 'textarea', array(
          'label'     => Mage::helper('purchase')->__('Comments'),
		  'style'     => 'height:75px; width:500px;',
          'name'      => 'comments'
		));

  		if ( Mage::registry('shipment') ) {
			$form->setValues(Mage::registry('shipment')->getData());
		}


      return parent::_prepareForm();
  }
}