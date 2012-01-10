<?php

class VO_Purchase_Block_Suppliers_Edit_Tab_Renderer_Select
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
    	$productId = $row->getId();
    	$retour = '<input type="checkbox"  name="add" id="'.$productId.'_add" value="'.$productId.'" size="4" onchange="check('.$productId.')" >';
		return $retour;
    }

}