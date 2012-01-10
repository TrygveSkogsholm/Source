<?php
class VO_Purchase_Block_Prices_List_Prices extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'prices_list_prices';
    $this->_blockGroup = 'purchase';
    $this->_headerText = Mage::helper('dealers')->__('List Products');
    $this->_addButtonLabel = Mage::helper('dealers')->__('Add Products');
    parent::__construct();
  }
  
    public function getCreateUrl()
    {
        return $this->getUrl('*/*/newListProduct',array('list_id'=>Mage::registry('price_list_data')->getId()));
    }
}