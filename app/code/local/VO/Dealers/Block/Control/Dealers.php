<?php
class VO_Dealers_Block_Control_Dealers extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
     public function getDealers()     
     { 
        if (!$this->hasData('dealers')) {
            $this->setData('dealers', Mage::registry('dealers'));
        }
        return $this->getData('dealers');
        
    }
}