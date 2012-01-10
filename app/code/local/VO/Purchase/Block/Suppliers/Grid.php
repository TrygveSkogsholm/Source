<?php
class VO_Purchase_Block_Suppliers_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
	{
      parent::__construct();
      $this->setId('poSupGrid');
      $this->setDefaultSort('id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
	}

	protected function _prepareCollection()
	{
		$collection = Mage::getModel('purchase/supplier')->getCollection();
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

	protected function _prepareColumns()
	{
		$this->addColumn('id', array(
          'header'    => Mage::helper('purchase')->__('ID'),
		'getter'    => 'getId',
          'width'     => '50px',
          'index'     => 'id',
		));

		$this->addColumn('company_name', array(
          'header'    => Mage::helper('purchase')->__('Name'),
		  'type'	=> 'text',
          'index'     => 'company_name',
		));

        $this->addColumn('contact_name', array(
            'header'=> Mage::helper('purchase')->__('Contact'),
            'index' => 'contact_name',
            'type'	=> 'text'
        ));

        $this->addColumn('email', array(
            'header'=> Mage::helper('purchase')->__('Email'),
            'index' => 'email',
            'type'	=> 'text'
        ));

        $this->addColumn('phone', array(
            'header'=> Mage::helper('purchase')->__('Phone'),
            'index' => 'phone',
            'type'	=> 'text'
        ));

        //$this->addExportType('*/*/exportCsv', Mage::helper('dealers')->__('CSV'));
        //$this->addExportType('*/*/exportAllCsv', Mage::helper('dealers')->__('Complete CSV'));

		return parent::_prepareColumns();
	}


	public function getRowUrl($row)
	{
		return $this->getUrl('*/*/edit', array('id' => $row->getId()));
	}
}