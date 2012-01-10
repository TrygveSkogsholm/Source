<?php

class VO_Purchase_Model_Mysql4_Supplyneed_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('purchase/supplyneed');
    }
}