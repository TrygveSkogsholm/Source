<?php
class VO_Purchase_Block_Prices_List extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'prices_list';
    $this->_blockGroup = 'purchase';
    $this->_headerText = Mage::helper('dealers')->__('Price Lists');
    $this->_addButtonLabel = Mage::helper('dealers')->__('Add List');
    parent::__construct();
  }
  
    public function getCreateUrl()
    {
        return $this->getUrl('*/*/newList');
    }
}