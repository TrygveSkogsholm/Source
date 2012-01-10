<?php
class VO_Purchase_Block_Hts_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
	{
      parent::__construct();
      $this->setId('htsGrid');
      $this->setDefaultSort('code');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
	}

	protected function _prepareCollection()
	{
		$collection = Mage::getModel('purchase/hts')->getCollection();
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

	protected function _prepareColumns()
	{
		$this->addColumn('code', array(
          'header'    => Mage::helper('purchase')->__('Code'),
          'index'     => 'code'
		));
        
		$this->addColumn('rate', array(
          'header'    => Mage::helper('purchase')->__('Rate'),
          'align'     =>'left',
          'index'     => 'rate'
		));
        
        $this->addExportType('*/*/exportCsv', Mage::helper('purchase')->__('CSV'));
        //$this->addExportType('*/*/exportAllCsv', Mage::helper('dealers')->__('Complete CSV'));
        
		return parent::_prepareColumns();
	}


	public function getRowUrl($row)
	{
		return $this->getUrl('*/*/edit', array('id' => $row->getId()));
	}
}