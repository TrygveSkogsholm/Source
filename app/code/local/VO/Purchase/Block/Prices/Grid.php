<?php
class VO_Purchase_Block_Prices_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
	{
		parent::__construct();
		$this->setId('priceChangesGrid');
		$this->setDefaultSort('date');
		$this->setDefaultDir('ASC');
		$this->setSaveParametersInSession(true);
	}

	protected function _prepareCollection()
	{
		$collection = Mage::getModel('purchase/price')->getCollection();
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

		$this->addColumn('sku', array(
          'header'    => Mage::helper('purchase')->__('SKU'),
          'align'     =>'left',
          'index'     => 'sku'
          ));

        $this->addColumn('date', array(
            'header'=> Mage::helper('purchase')->__('Date Changed'),
            'index' => 'date',
            'type'	=> 'date'
         ));

         $this->addColumn('change_text', array(
          'header'=> Mage::helper('purchase')->__('Summary'),
          'align'     =>'left',
          'index'     => 'change_text',
          'type'	  => 'text'
          ));


            //$this->addExportType('*/*/exportCsv', Mage::helper('dealers')->__('CSV'));
            //$this->addExportType('*/*/exportAllCsv', Mage::helper('dealers')->__('Complete CSV'));

            return parent::_prepareColumns();
	}


	public function getRowUrl($row)
	{
		return $this->getUrl('*/*/view', array('id' => $row->getId()));
	}
}