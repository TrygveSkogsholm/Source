<?php

class VO_Purchase_Block_Orders_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
	public $order;
	public function __construct()
	{
		parent::__construct();

		$this->_objectId = 'id';
		$this->_blockGroup = 'purchase';
		$this->_controller = 'orders';

		$this->_updateButton('save', 'label', Mage::helper('purchase')->__('Save Purchase Order'));
		$this->_updateButton('delete', 'label', Mage::helper('purchase')->__('Delete Purchase Order'));

		$this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
		), -100);

		if (Mage::registry('purchase_order_data')->getStatus() > 1)
		{
			$this->_addButton('modify', array(
            'label'     => Mage::helper('adminhtml')->__('Modify'),
            'onclick'   => 'deleteConfirm(\''. Mage::helper('adminhtml')->__('You will have to send this purchase order to the supplier again to inform them of any changes. Are you sure you wish to make changes?')
                    .'\', \'' . $this->getUrl('*/*/modify',array('id'=>Mage::registry('purchase_order_data')->getId())) . '\')',
            'class'     => 'open'
			), -110);
		}

		$this->_formScripts[] = "

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";

		/*$this->_formInitScripts[] = "
		initialize()
		";*/
		
		$this->order = $this->getOrder();
	}
	
	public function getOrder()
	{
		if ( Mage::getSingleton('adminhtml/session')->getPurchaseOrderData() )
		{
			return Mage::getSingleton('adminhtml/session')->getPurchaseOrderData();
			Mage::getSingleton('adminhtml/session')->setPurchaseOrderData(null);
		} elseif ( Mage::registry('purchase_order_data') ) {
			return Mage::registry('purchase_order_data');
		}
	}

	public function getHeaderText()
	{
		if( Mage::registry('purchase_order_data') && Mage::registry('purchase_order_data')->getId() ) {
			return Mage::helper('purchase')->__("Edit Purchase Order #%s", $this->htmlEscape(Mage::registry('purchase_order_data')->getorder_id()));
		} else {
			return Mage::helper('purchase')->__('New Purchase Order');
		}
	}
}