<?php
class VO_Purchase_Block_Orders_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
	{
		parent::__construct();
		$this->setId('poOrdersGrid');
		$this->setDefaultSort('order_id');
		$this->setDefaultDir('ASC');
		$this->setSaveParametersInSession(true);
	}

	protected function _prepareCollection()
	{
		$collection = Mage::getModel('purchase/order')->getCollection()
		->join('purchase/supplier',
			        'id=supplier_id',
		        	'company_name');
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

	protected function _prepareColumns()
	{
		$this->addColumn('order_id', array(
          'header'    => Mage::helper('purchase')->__('PO #'),
          'align'     =>'center',
          'width'     => '75px',
          'index'     => 'order_id',
		));

		$this->addColumn('company_name', array(
          'header'    => Mage::helper('purchase')->__('Supplier'),
          'align'     =>'left',
		  'type'	  =>'options',
		  'options'   => Mage::helper('purchase')->getSupplierListOptions(true),
          'index'     => 'company_name'
		));

		$this->addColumn('date_created', array(
            'header'=> Mage::helper('purchase')->__('Date Created'),
            'index' => 'date_created',
            'type'	=> 'date'
            ));

            $this->addColumn('total', array(
            'header'=> Mage::helper('purchase')->__('Order Total'),
            'renderer' => 'VO_Purchase_Block_Orders_Renderer_Total',
            'width'     => '150px',
            'type'  => 'currency',
            'currency' => 'order_currency_code'
            ));


            $this->addColumn('is_paid', array(
            'header'=> Mage::helper('purchase')->__('Paid'),
            'index' => 'is_paid',
            'type' => 'options',
            'options' => array(
                '1' => Mage::helper('catalog')->__('Yes'),
                '0' => Mage::helper('catalog')->__('No'),
            ),
            'width'     => '50px',
            'align' => 'center'
            ));

            $this->addColumn('status', array(
            'header'=> Mage::helper('purchase')->__('Status'),
            'index' => 'status',
            'type' => 'options',
            'options' => Mage::helper('purchase')->getStatusOptions(),
        	'width'     => '110px',
            'align'	=> 'center'
            ));

            //$this->addColumn('sub_total', array(
            //'header'=> Mage::helper('purchase')->__('Status'),
            //'index' => 'sub_total',
           // 'type' => 'price',
           // 'align'	=> 'center'
          //  ));



            //$this->addExportType('*/*/exportCsv', Mage::helper('dealers')->__('CSV'));
            //$this->addExportType('*/*/exportAllCsv', Mage::helper('dealers')->__('Complete CSV'));

            return parent::_prepareColumns();
	}


	public function getRowUrl($row)
	{
		return $this->getUrl('*/*/edit', array('id' => $row->getId()));
	}
}