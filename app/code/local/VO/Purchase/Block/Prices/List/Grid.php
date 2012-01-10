<?php
class VO_Purchase_Block_Prices_List_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
	{
      parent::__construct();
      $this->setId('priceListGrid');
      $this->setDefaultSort('id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
	}

	protected function _prepareCollection()
	{
		$collection = Mage::getModel('purchase/price_list')->getCollection();
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

	protected function _prepareColumns()
	{
		$this->addColumn('id', array(
          'header'    => Mage::helper('dealers')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'id',
		));
        
		$this->addColumn('name', array(
          'header'    => Mage::helper('dealers')->__('List Name'),
          'align'     =>'left',
          'index'     => 'name',
		));
		
		$this->addColumn('date_modified', array(
            'header'=> Mage::helper('purchase')->__('Date Changed'),
            'index' => 'date_modified',
            'type'	=> 'date'
         ));
        
		return parent::_prepareColumns();
	}


	public function getRowUrl($row)
	{
		return $this->getUrl('*/*/editList', array('id' => $row->getId()));
	}
}