<?php
class VO_Purchase_Block_Shipments_Items extends Mage_Adminhtml_Block_Widget_Grid
{
	public $shipID;

	public function __construct()
	{
		parent::__construct();
		$this->setId('shipmentItemsGrid');
		$this->setDefaultSort('sku');
		$this->setDefaultDir('ASC');
		$this->setSaveParametersInSession(true);
	}

	protected function _prepareCollection()
	{
		if ( Mage::registry('shipment') ) {
			$shipID = Mage::registry('shipment')->getData('id');
			$supId = Mage::registry('shipment')->getSupplier()->getId();
		}


		$collection = Mage::getResourceModel('purchase/shipment_product_collection')
		->addFieldToFilter('shipment_id', $shipID)
		->join('catalog/product','product_id=entity_id','sku')
		->join('purchase/supplier_product','`purchase/supplier_product`.`product_id`=`main_table`.`product_id`',array('first_cost','model'))
		->addFieldToFilter('supplier_id', $supId)
		->join('purchase/order_product','order_product_id=`purchase/order_product`.id','po_id');
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

	protected function _prepareColumns()
	{

		$this->addColumn('po_id', array(
          'header'    => Mage::helper('purchase')->__('PO'),
          'width'     => '50px',
          'index'     => 'po_id',
		));

		$this->addColumn('qty', array(
          'header'    => Mage::helper('purchase')->__('Qty'),
		  'type'	=> 'qty',
		'width'     => '60px',
          'index'     => 'qty',
		));

		$this->addColumn('sku', array(
          'header'    => Mage::helper('purchase')->__('SKU'),
		  'type'	=> 'text',
		'width'     => '120px',
          'index'     => 'sku',
		));

		$this->addColumn('name', array(
          'header'    => Mage::helper('purchase')->__('Name'),
		  'type'	=> 'text',
		  'renderer' => 'VO_Purchase_Block_Shipments_Renderer_Name',
          'index'     => 'name',
		'filter' => false
		));

		$this->addColumn('model', array(
          'header'    => Mage::helper('purchase')->__('Model #'),
		  'type'	=> 'text',
          'index'     => 'model',
			'filter' => false
		));

		$this->addColumn('landed_cost', array(
          'header'    => Mage::helper('purchase')->__('Landed Cost'),
		  'type'	=> 'price',
		'width'     => '120px',
          'index'     => 'landed_cost',
		'filter' => false
		));

		$this->addExportType('*/*/exportCsv', Mage::helper('purchase')->__('CSV'));
		$this->addExportType('*/*/exporthtsCsv', Mage::helper('purchase')->__('HTS CSV'));
		//$this->addExportType('*/*/exportAllCsv', Mage::helper('dealers')->__('Complete CSV'));

		return parent::_prepareColumns();
	}

	protected function _prepareMassaction()
	{
		if ( Mage::registry('shipment') )
		{
			$supID = Mage::registry('shipment')->getData('id');
		}

		$this->setMassactionIdField('id');
		$this->getMassactionBlock()->setFormFieldName('product');

		$this->getMassactionBlock()->addItem('remove', array(
             'label'=> Mage::helper('purchase')->__('Remove'),
             'url'  => $this->getUrl('*/*/massRemove').'?Sup='.$supID,
             'confirm' => Mage::helper('purchase')->__('Are you sure? Model numbers and first costs will be lost!')
		));
		return $this;
	}



	public function getRowUrl($row)
	{
		return $this->getUrl('*/*/productedit', array('id' => $row->getId()));
	}
}