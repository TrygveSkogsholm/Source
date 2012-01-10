<?php
class VO_Purchase_Block_Prices_Plan_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
	{
		parent::__construct();
		$this->setId('pricePlanGrid');
		$this->setDefaultSort('date_created');
		$this->setDefaultDir('ASC');
		$this->setSaveParametersInSession(true);
	}

	protected function _prepareCollection()
	{
		$collection = Mage::getModel('purchase/price_plan')->getCollection();
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

	protected function _prepareColumns()
	{
		$this->addColumn('id', array(
          'header'    => Mage::helper('purchase')->__('ID'),
          'align'     =>'center',
          'width'     => '75px',
          'index'     => 'id',
		));

		$this->addColumn('category', array(
          'header'    => Mage::helper('purchase')->__('Scope'),
          'align'     =>'left',
          'index'     => 'category'
          ));
          
         $this->addColumn('updater', array(
          'header'    => Mage::helper('purchase')->__('User'),
          'align'     =>'left',
          'index'     => 'updater'
          ));

        $this->addColumn('date_planned', array(
            'header'=> Mage::helper('purchase')->__('Date Changed'),
            'index' => 'date_planned',
            'type'	=> 'date'
         ));
         
       $this->addColumn('date_activated', array(
            'header'=> Mage::helper('purchase')->__('Date Applied'),
            'index' => 'date_activated',
            'type'	=> 'date'
         ));
         
       $this->addColumn('date_created', array(
            'header'=> Mage::helper('purchase')->__('Date Created'),
            'index' => 'date_created',
            'type'	=> 'date'
         ));


            //$this->addExportType('*/*/exportCsv', Mage::helper('dealers')->__('CSV'));
            //$this->addExportType('*/*/exportAllCsv', Mage::helper('dealers')->__('Complete CSV'));

            return parent::_prepareColumns();
	}


	public function getRowUrl($row)
	{
		return $this->getUrl('*/*/new', array('plan_id' => $row->getId()));
	}
}