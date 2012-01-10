<?php

class VO_Dealers_Model_Mysql4_Dealers extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the warehouse_id refers to the key field in your database table.
        $this->_init('dealers/dealers', 'dealer_id');
    }
}