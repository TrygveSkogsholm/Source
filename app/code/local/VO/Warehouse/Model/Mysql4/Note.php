<?php

class VO_Warehouse_Model_Mysql4_Note extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('warehouse/note', 'id');
    }
}