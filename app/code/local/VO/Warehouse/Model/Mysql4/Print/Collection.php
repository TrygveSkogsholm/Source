<?php

class VO_Warehouse_Model_Mysql4_Print_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('warehouse/print');
    }
}