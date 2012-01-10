<?php

class VO_Purchase_Block_Suppliers_Edit_Tab_Renderer_Model
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
    	$productId = $row->getId();
    	$retour = '<input type="text" name="'.$productId.'_model" id="'.$productId.'_model" value=""   disabled>';
		return $retour;
    }

}