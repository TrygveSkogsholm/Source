<?php

class VO_Dealers_Model_Mysql4_Dealers_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('dealers/dealers');
    }
}