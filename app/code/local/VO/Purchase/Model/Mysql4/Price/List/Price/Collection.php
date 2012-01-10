<?php

class VO_Purchase_Model_Mysql4_Price_List_Price_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('purchase/price_list_price');
    }
}