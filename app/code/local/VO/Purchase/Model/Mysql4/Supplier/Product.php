<?php

class VO_Purchase_Model_Mysql4_Supplier_Product extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the order_id refers to the key field in your database table.
        $this->_init('purchase/supplier_product', 'id');
    }
}