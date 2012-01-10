<?php
class VO_Purchase_Block_Suppliers_Edit_Tab_Products extends Mage_Adminhtml_Block_Widget_Grid
{
	public $supID;

	public function __construct()
	{
		parent::__construct();
		$this->setId('SupProductGrid');
		$this->setDefaultSort('sku');
		$this->setDefaultDir('ASC');
		$this->setSaveParametersInSession(true);
	}

	protected function _prepareCollection()
	{
		if ( Mage::getSingleton('adminhtml/session')->getSupplierData() )
		{
			$supID = Mage::getSingleton('adminhtml/session')->getSupplierData();
			Mage::getSingleton('adminhtml/session')->setSupplierData(null);
		} elseif ( Mage::registry('supplier_data') ) {
			$supID = Mage::registry('supplier_data')->getData('id');
		}


		$collection = Mage::getResourceModel('purchase/supplier_product_collection')
		->addFilter('supplier_id', $supID)
		->join('catalog/product',
			        'product_id=entity_id','sku');
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

		$this->addColumn('sku', array(
          'header'    => Mage::helper('purchase')->__('SKU'),
		  'type'	=> 'text',
          'index'     => 'sku',
		));

		$this->addColumn('model', array(
          'header'    => Mage::helper('purchase')->__('Model #'),
		  'type'	=> 'text',
          'index'     => 'model',
		));

		$this->addColumn('first_cost', array(
          'header'    => Mage::helper('purchase')->__('First Cost'),
		  'type'	=> 'price',
		'width'     => '120px',
		'filter' => false,
          'index'     => 'first_cost'
		));

		//$this->addExportType('*/*/exportCsv', Mage::helper('purchase')->__('CSV'));
		//$this->addExportType('*/*/exportAllCsv', Mage::helper('dealers')->__('Complete CSV'));

		return parent::_prepareColumns();
	}

	protected function _prepareMassaction()
	{
			if ( Mage::getSingleton('adminhtml/session')->getSupplierData() )
		{
			$supID = Mage::getSingleton('adminhtml/session')->getSupplierData();
			Mage::getSingleton('adminhtml/session')->setSupplierData(null);
		} elseif ( Mage::registry('supplier_data') ) {
			$supID = Mage::registry('supplier_data')->getData('id');
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