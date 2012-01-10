<?php

class VO_Purchase_Block_Orders_Edit_Tab_Send extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareForm()
	{
		$this->setTemplate('purchase/order/send.phtml');
		return parent::_prepareForm();
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
}