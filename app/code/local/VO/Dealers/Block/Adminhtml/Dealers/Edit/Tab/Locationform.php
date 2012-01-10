<?php

class VO_Dealers_Block_Adminhtml_Dealers_Edit_Tab_Locationform extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('dealers__locale_form', array('legend'=>Mage::helper('dealers')->__('Dealer Location')));
		
		$fieldset->addField('dealer_id', 'text', array(
          'label'     => Mage::helper('dealers')->__('Store Location ID'),
		  'disabled'  => true,
          'name'      => 'dealer_id',
		));
		
		$fieldset->addField('country', 'text', array(
          'label'     => Mage::helper('dealers')->__('Country'),
          'name'      => 'country',
		));
		
 		$fieldset->addField('state', 'text', array(
          'label'     => Mage::helper('dealers')->__('State/Province'),
          'name'      => 'state',
		));    
		
		$fieldset->addField('zip', 'text', array(
          'label'     => Mage::helper('dealers')->__('Postal Code'),
          'name'      => 'zip',
		));
		
		$fieldset->addField('city', 'text', array(
          'label'     => Mage::helper('dealers')->__('City'),
          'name'      => 'city',
		));
		
		$fieldset->addField('address', 'textarea', array(
          'label'     => Mage::helper('dealers')->__('Address'),
          'name'      => 'address',
		  'style'     => 'height:35px;'
		));
		
		$fieldset->addField('longitude', 'text', array(
          'label'     => Mage::helper('dealers')->__('Longitude'),
          'name'      => 'longitude',
		));
		$fieldset->addField('latitude', 'text', array(
          'label'     => Mage::helper('dealers')->__('Latitude'),
          'name'      => 'latitude',
		));
      if ( Mage::getSingleton('adminhtml/session')->getdealersData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getdealersData());
          Mage::getSingleton('adminhtml/session')->setdealersData(null);
      } elseif ( Mage::registry('dealers_data') ) {
          $form->setValues(Mage::registry('dealers_data')->getData());
      }
      return parent::_prepareForm();
  }
}