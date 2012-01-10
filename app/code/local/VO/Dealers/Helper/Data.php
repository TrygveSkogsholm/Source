<?php

class VO_Dealers_Helper_Data extends Mage_Core_Helper_Abstract
{

    public function getNewDealerUrl()
    {
        return $this->_getUrl('dealers/index/new');
    }
	
    public function displayDealerUrl()
    {
    	return $this->_getUrl('dealers/index/display');
    }
}