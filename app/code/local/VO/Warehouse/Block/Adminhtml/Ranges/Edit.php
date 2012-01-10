<?php

class VO_Warehouse_Block_Adminhtml_Ranges_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
	public function __construct()
	{
		parent::__construct();
		 
		$this->_objectId = 'id';
		$this->_blockGroup = 'warehouse';
		$this->_controller = 'Adminhtml_Ranges';
		 
		$this->_updateButton('save', 'label', Mage::helper('warehouse')->__('Save Range'));
		$this->_updateButton('delete', 'label', Mage::helper('warehouse')->__('Delete Range'));

		$this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
		), -100);

		$this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('warehouse_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'warehouse_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'warehouse_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
	}

	public function getHeaderText()
	{
		if( Mage::registry('range_data') && Mage::registry('range_data')->getId() ) {
			return Mage::helper('warehouse')->__("Edit Range '%s'", $this->htmlEscape(Mage::registry('range_data')->getId()));
		}
	}
}