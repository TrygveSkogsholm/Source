<?php

class VO_Purchase_Model_Mysql4_Price_List_Price extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the order_id refers to the key field in your database table.
        $this->_init('purchase/price_list_price', 'id');
    }
}