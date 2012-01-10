<?php
class VO_Purchase_Block_Prices_List_Prices_Add extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
	{
		parent::__construct();
		$this->setId('productGrid');
		$this->setDefaultSort('entity_id');
		$this->setDefaultDir('desc');
	}

	public $listId;

	protected function _prepareCollection()
	{
		$collection = Mage::getModel('catalog/product')->getCollection()
		->addAttributeToSelect('sku')
		->addAttributeToSelect('name')
		->addAttributeToSelect('attribute_set_id')
		->addAttributeToSelect('type_id');

		$this->setCollection($collection);

		parent::_prepareCollection();
		return $this;
	}

	protected function _prepareColumns()
	{
		$this->addColumn('entity_id',
		array(
                'header'=> Mage::helper('catalog')->__('ID'),
                'width' => '50px',
                'type'  => 'number',
                'index' => 'entity_id',
		));
		$this->addColumn('sku',
		array(
                'header'=> Mage::helper('catalog')->__('SKU'),
                'width' => '80px',
                'index' => 'sku',
		));
		$this->addColumn('name',
		array(
                'header'=> Mage::helper('catalog')->__('Name'),
                'index' => 'name',
		));

		$this->addColumn('type',
		array(
                'header'=> Mage::helper('catalog')->__('Type'),
                'width' => '60px',
                'index' => 'type_id',
                'type'  => 'options',
                'options' => Mage::getSingleton('catalog/product_type')->getOptionArray(),
		));

		$sets = Mage::getResourceModel('eav/entity_attribute_set_collection')
		->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
		->load()
		->toOptionHash();

		$this->addColumn('set_name',
		array(
                'header'=> Mage::helper('catalog')->__('Attrib. Set Name'),
                'width' => '100px',
                'index' => 'attribute_set_id',
                'type'  => 'options',
                'options' => $sets,
		));

		return parent::_prepareColumns();
	}

	protected function _prepareMassaction()
	{
		$this->setMassactionIdField('entity_id');
		$this->getMassactionBlock()->setFormFieldName('product');

		$this->getMassactionBlock()->addItem('add', array(
             'label'=> Mage::helper('purchase')->__('Add'),
             'url'  => $this->getUrl('*/*/addListProduct',array('list_id'=>$this->listId))
		));

		return $this;
	}

	public function getGridUrl()
	{
		return $this->getUrl('*/*/newListProduct', array('_current'=>true,'list_id'=>$this->listId));
	}
}