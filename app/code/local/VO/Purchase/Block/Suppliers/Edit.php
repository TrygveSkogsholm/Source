<?php

class VO_Purchase_Block_Suppliers_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
	public function __construct()
	{
		parent::__construct();

		$this->_objectId = 'id';
		$this->_blockGroup = 'purchase';
		$this->_controller = 'suppliers';

		$this->_updateButton('save', 'label', Mage::helper('purchase')->__('Save Supplier & Products'));
		$this->_updateButton('delete', 'label', Mage::helper('purchase')->__('Delete Supplier'));

		$this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
		), -100);

				$this->_formScripts[] = "

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
	}

	public function getHeaderText()
	{
		if( Mage::registry('supplier_data') && Mage::registry('supplier_data')->getId() ) {
			return Mage::helper('dealers')->__("Edit %s", $this->htmlEscape(Mage::registry('supplier_data')->getcompany_name()));
		} else {
			return Mage::helper('dealers')->__('Add Supplier');
		}
	}
}