<?php
class VO_Purchase_Block_Shipments_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
	{
		parent::__construct();
		$this->setId('poShipmentGrid');
		$this->setDefaultSort('id');
		$this->setDefaultDir('ASC');
		$this->setSaveParametersInSession(true);
	}

	protected function _prepareCollection()
	{
		$collection = Mage::getModel('purchase/shipment')->getCollection();
		$collection->join('purchase/supplier',
			        '`purchase/supplier`.`id`=`main_table`.`supplier_id`',
		        	'company_name');
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
	      'filter'  =>false
		));

		$this->addColumn('edoa', array(
            'header'=> Mage::helper('purchase')->__('Est. Date of Arrival'),
            'index' => 'edoa',
            'type'	=> 'date'
            ));

            $this->addColumn('company_name', array(
          'header'    => Mage::helper('purchase')->__('Supplier'),
          'align'     =>'left',
		  'type'	  =>'options',
		  'options'   => Mage::helper('purchase')->getSupplierListOptions(true),
          'index'     => 'company_name'
          ));

          	$this->addColumn('date_shipped', array(
            'header'=> Mage::helper('purchase')->__('Shipped'),
            'index' => 'date_shipped',
            'type'	=> 'date'
            ));


            $this->addColumn('ship_method', array(
            'header'=> Mage::helper('purchase')->__('Ship Method'),
            'index' => 'ship_method',
            'type' => 'options',
            'options' => Mage::helper('purchase')->getShippingMethodOptions(),
        	'width'     => '110px',
            'align'	=> 'center'
            ));

            $this->addColumn('orders', array(
            'header'=> Mage::helper('purchase')->__('POs'),
            'renderer' => 'VO_Purchase_Block_Shipments_Renderer_Orders',
            'width'     => '150px',
            'type'  => 'text',
            ));

          $this->addColumn('total', array(
            'header'=> Mage::helper('purchase')->__('Shipment Total'),
            'renderer' => 'VO_Purchase_Block_Shipments_Renderer_Total',
            'width'     => '150px',
            'type'  => 'currency',
            'currency' => 'order_currency_code'
            ));

            $this->addColumn('status', array(
            'header'=> Mage::helper('purchase')->__('Status'),
            'index' => 'status',
            'type' => 'options',
            'options' => Mage::helper('purchase')->getShipmentStatusOptions(),
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