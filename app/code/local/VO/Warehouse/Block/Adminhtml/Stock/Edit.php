<?php

class VO_Warehouse_Block_Adminhtml_Stock_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
	public function __construct()
	{
		parent::__construct();
		 
		$this->_objectId = 'id';
		$this->_blockGroup = 'warehouse';
		$this->_controller = 'adminhtml_stock';

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
			return Mage::helper('warehouse')->__("Warehouse Stock Item Tools");
	}
}