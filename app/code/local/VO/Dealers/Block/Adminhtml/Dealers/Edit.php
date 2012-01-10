<?php

class VO_Dealers_Block_Adminhtml_Dealers_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
	public function __construct()
	{
		parent::__construct();
		 
		$this->_objectId = 'id';
		$this->_blockGroup = 'dealers';
		$this->_controller = 'Adminhtml_Dealers';
		 
		$this->_updateButton('save', 'label', Mage::helper('dealers')->__('Save Dealer'));
		$this->_updateButton('delete', 'label', Mage::helper('dealers')->__('Delete Dealer'));

		$this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
		), -100);
		
		if (Mage::registry('dealers_data') && Mage::registry('dealers_data')->getId() != null && Mage::registry('dealers_data')->isApproved() == false)
		{
			$this->_addButton('approve', array(
	            'label'     => Mage::helper('adminhtml')->__('Approve'),
	            'onclick'   => 'window.location = \''.$this->getUrl('*/*/approve',array('id'=>Mage::registry('dealers_data')->getId())).'\'',
	            'class'     => 'check'
			), -150);
		}

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
		if( Mage::registry('dealers_data') && Mage::registry('dealers_data')->getId() ) {
			return Mage::helper('dealers')->__("Edit Dealer '%s'", $this->htmlEscape(Mage::registry('dealers_data')->getName()));
		} else {
			return Mage::helper('dealers')->__('Add Dealer');
		}
	}
}