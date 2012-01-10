<?php

class VO_Warehouse_Block_Widget_Column_Renderer_Inventory_Bin
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract 
{
    public function render(Varien_Object $row)
    {
    	$bin = $row->getbinlocation();
    	$productId = $row->getId();
    	$onChangeCode = 'new Ajax.Request(\'' . $this->getUrl('*/*/BinSave',array('id'=>$productId)).'?value=\' + this.value)';
    	$html = '<input onChange="'.$onChangeCode.'" value="'.$bin.'" />';
		return $html;
    }
}