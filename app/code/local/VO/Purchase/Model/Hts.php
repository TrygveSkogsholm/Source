<?php

class VO_Purchase_Model_Hts extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('purchase/hts');
    }

    public function getDutyRate()
    {
    	if ($this->getrate())
    	{
    	return ($this->getrate()/100) + 1;
    	}
    	else
    	{return 1;}
    }
}