<?php
class VO_Warehouse_Block_Adminhtml_Ranges extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
	{
		parent::__construct();
		$this->setId('rangesGrid');
		$this->setDefaultSort('id');
		$this->setDefaultDir('DESC');
		$this->setSaveParametersInSession(true);
	}

	protected function _prepareCollection()
	{
		$collection = Mage::getModel('warehouse/range')->getCollection();
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

	protected function _prepareColumns()
	{
		$this->addColumn('id', array(
          'header'    => Mage::helper('warehouse')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'id',
		));

		$this->addColumn('store_id', array(
          'header'    => Mage::helper('dealers')->__('Store View'),
          'align'     =>'left',
		  'type'      =>'store',
          'index'     => 'store_id',
		));

		$this->addColumn('start_increment', array(
          'header'    => Mage::helper('warehouse')->__('Start'),
          'align'     =>'right',
          'index'     => 'start_increment',
		));

		$this->addColumn('end_increment', array(
          'header'    => Mage::helper('warehouse')->__('End'),
          'align'     =>'right',
          'index'     => 'end_increment',
		));

		$this->addColumn('start_date', array(
          'header'    => Mage::helper('dealers')->__('Start Date'),
          'align'     =>'left',
		  'type'      =>'datetime',
          'index'     => 'start_date',
		));

		$this->addColumn('end_date', array(
          'header'    => Mage::helper('dealers')->__('End Date'),
          'align'     =>'left',
		  'type'      =>'datetime',
          'index'     => 'end_date',
		));
		
		$this->addColumn('latest', array(
            'header'=> Mage::helper('warehouse')->__('Current'),
 		    'width'     => '80',
            'index' => 'latest',
            'type' => 'options',
            'options' => array(
                '1' => Mage::helper('catalog')->__('Yes'),
                '0' => Mage::helper('catalog')->__('No'),
            ),
            'align' => 'center'
        ));

		$this->addExportType('*/*/exportCsv', Mage::helper('dealers')->__('CSV'));
		$this->addExportType('*/*/exportAllCsv', Mage::helper('dealers')->__('Complete CSV'));

		return parent::_prepareColumns();
	}

	public function getRowUrl($row)
	{
		return $this->getUrl('*/*/edit', array('id' => $row->getId()));
	}
}