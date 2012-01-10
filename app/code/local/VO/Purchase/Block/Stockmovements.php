<?php
class VO_Purchase_Block_Stockmovements extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
	{
		parent::__construct();
		$this->setId('stockmovementGrid');
		$this->setDefaultSort('date');
		$this->setDefaultDir('DESC');
		$this->setSaveParametersInSession(true);
	}

	protected function _prepareCollection()
	{
		$collection = Mage::getModel('purchase/stockmovement')->getCollection()
		->join('catalog/product',
			        'entity_id=product_id',
		array('sku'));

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


		$this->addColumn('date', array(
            'header'=> Mage::helper('dealers')->__('When'),
            'index' => 'date',
            'type' => 'date',
            'align' => 'center'
            ));

            $this->addColumn('sku', array(
          'header'    => Mage::helper('dealers')->__('SKU'),
          'align'     =>'left',
          'index'     => 'sku',
            ));

            $this->addColumn('type', array(
          'header'    => Mage::helper('dealers')->__('Type'),
          'align'     =>'left',
		  'type' => 'options',
            'options' => array(
                'Ordered' => 'Ordered',
				'Returned' => 'Returned',
				'Manual' => 'Manual',
				'Restocked' => 'Restocked',
            	'Canceled Shipment' => 'Canceled Shipment'
				),
          'index'     => 'type'
          ));

          $this->addColumn('magnitude', array(
          'header'    => Mage::helper('dealers')->__('Change'),
          'align'     =>'left',
          'index'     => 'magnitude',
          ));

           $this->addColumn('stockafter', array(
          'header'    => Mage::helper('dealers')->__('Stock after Change'),
          'align'     =>'left',
           'filter'=>	false,
          'index'     => 'stockafter'
          ));

          $this->addColumn('order_num', array(
          'header'    => Mage::helper('dealers')->__('Order or PO #'),
          'align'     =>'left',
          'index'     => 'order_num',
          ));
          
          $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('purchase')->__('Action'),
                //'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array
            	(
                    array
                    (
                        'caption'   => Mage::helper('purchase')->__('View Source'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => true,
                'index'     => 'id',
                'is_system' => true,
			));

          $this->addExportType('*/*/exportCsv', Mage::helper('dealers')->__('CSV'));
          $this->addExportType('*/*/exportAllCsv', Mage::helper('dealers')->__('Complete CSV'));

          return parent::_prepareColumns();
	}
}