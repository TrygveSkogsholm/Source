<?php

class VO_Purchase_Block_Suppliers_Edit_Tab_Renderer_Firstcost
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
    	$productId = $row->getId();
    	$retour = '<input type="text" name="'.$productId.'_firstcost" id="'.$productId.'_firstcost" value="" class="validate-number required-entry"  disabled>';
		return $retour;
    }

}