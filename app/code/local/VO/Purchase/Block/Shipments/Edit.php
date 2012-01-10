<?php

class VO_Purchase_Block_Shipments_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
	public function __construct()
	{
		parent::__construct();

		$this->_objectId = 'id';
		$this->_blockGroup = 'purchase';
		$this->_controller = 'shipments';

		$this->_updateButton('save', 'label', Mage::helper('purchase')->__('Save Shipment'));
		$this->_updateButton('delete', 'label', Mage::helper('purchase')->__('Delete Shipment'));

		$this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
		), -100);

		if (Mage::registry('shipment')->Status() < 2)
		{
			$this->_addButton('ship', array(
            'label'     => Mage::helper('adminhtml')->__('Ship'),
            'onclick'   => '$(\'ship\').show()',
            'class'     => 'scalable go',
			), -10);
		}
		else if (Mage::registry('shipment')->Status() < 3)
		{
			$this->_addButton('receive', array(
            'label'     => Mage::helper('adminhtml')->__('Receive'),
            'onclick'   => '$(\'receive\').show()',
            'class'     => 'scalable go',
			), -10);
		}

		$this->_formScripts[] = "

			//prompt('sometext','defaultvalue');



			$('edit_form').insert('<input type=\"text\" class=\"input-text\" style=\"display:none;\" value=\"".Mage::registry('shipment')->getSupplier()->getId()."\" name=\"supplier_id\" id=\"supplier_id\">');

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";

	}

	public function getHeaderText()
	{
		if( Mage::registry('shipment') && Mage::registry('shipment')->getId() ) {
			return Mage::helper('purchase')->__("Edit Shipment #%s - ".Mage::registry('shipment')->Status('label'), $this->htmlEscape(Mage::registry('shipment')->getId()));
		} else {
			return Mage::helper('purchase')->__('New Shipment');
		}
	}
}