<?php

class VO_Purchase_Model_Mysql4_Supplier_Product_Extended_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('purchase/supplier_product_extended');
    }
}