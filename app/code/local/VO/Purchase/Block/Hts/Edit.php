<?php

class VO_Purchase_Block_Hts_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
	public function __construct()
	{
		parent::__construct();
		 
		$this->_objectId = 'editHts';
		$this->_blockGroup = 'purchase';
		$this->_controller = 'Hts';
		 
		$this->_updateButton('save', 'label', Mage::helper('purchase')->__('Save Code'));
		$this->_updateButton('delete', 'label', Mage::helper('purchase')->__('Delete Code'));

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
		if( Mage::registry('hts_data') && Mage::registry('hts_data')->getId() )
		{
			return Mage::helper('purchase')->__("Edit '%s'", $this->htmlEscape(Mage::registry('hts_data')->getId()));
		}
		else
		{
			return Mage::helper('purchase')->__('Add Code');
		}
	}
}